$ErrorActionPreference = 'Stop'
$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$PhpExe = Join-Path $ProjectRoot '.tools\php\php.exe'
$MysqlExe = 'D:\MYSQL\MySQL Server 8.0\bin\mysql.exe'
if (-not (Test-Path $MysqlExe)) {
    $MysqlExe = 'C:\xampp\mysql\bin\mysql.exe'
}
$SqlFile = Join-Path $ProjectRoot 'init.sql'
$ApacheConf = 'C:\xampp\apache\conf\httpd.conf'
$SnippetFile = Join-Path $ProjectRoot 'apache-alias-snippet.conf'
$Port = 8080
$BaseUrl = "http://localhost:$Port/phishguard/index.php"

function Write-Step($msg) { Write-Host "`n==> $msg" -ForegroundColor Cyan }

Write-Step 'Step 1: Web server'
if (Test-Path $ApacheConf) {
    $snippet = Get-Content $SnippetFile -Raw
    $conf = Get-Content $ApacheConf -Raw
    if ($conf -notmatch 'Alias /phishguard') {
        Add-Content -Path $ApacheConf -Value "`n$snippet" -Encoding UTF8
        Write-Host 'Apache Alias appended to httpd.conf'
    } else {
        Write-Host 'Apache Alias already configured'
    }
    $apacheBin = 'C:\xampp\apache\bin\httpd.exe'
    if (Test-Path $apacheBin) {
        & $apacheBin -k restart 2>&1 | Out-Null
        $BaseUrl = 'http://localhost/phishguard/index.php'
        Write-Host "Apache restarted: $BaseUrl"
    }
} else {
    if (-not (Test-Path $PhpExe)) {
        throw "PHP not found at $PhpExe. Install XAMPP or run from a machine with .tools/php."
    }
    if (-not (Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue)) {
        $router = Join-Path $ProjectRoot 'router.php'
        Start-Process -FilePath $PhpExe -ArgumentList @('-S', "localhost:$Port", $router) -WorkingDirectory $ProjectRoot -WindowStyle Hidden
        Start-Sleep -Seconds 2
        Write-Host "PHP built-in server started on port $Port"
    } else {
        Write-Host "Port $Port already listening"
    }
    Write-Host "URL: $BaseUrl"
}

Write-Step 'Step 2: MySQL init.sql'
if (-not (Test-Path $MysqlExe)) {
    throw "MySQL client not found: $MysqlExe"
}

$mysqlArgs = @('-u', 'root', '--default-character-set=utf8mb4')
$imported = $false
$workingPass = ''
foreach ($tryPass in @('', '123456', 'root', 'mysql')) {
    $argList = $mysqlArgs.Clone()
    if ($tryPass -ne '') { $argList += @("-p$tryPass") }
    $prevEap = $ErrorActionPreference
    $ErrorActionPreference = 'SilentlyContinue'
    & $MysqlExe @argList -e "SELECT 1" 2>$null | Out-Null
    $mysqlOk = ($LASTEXITCODE -eq 0)
    $ErrorActionPreference = $prevEap
    if ($mysqlOk) {
        $null = Get-Content $SqlFile -Raw | & $MysqlExe @argList 2>&1
        if ($LASTEXITCODE -eq 0) {
            $imported = $true
            $workingPass = $tryPass
            Write-Host "MySQL import OK (password length: $($tryPass.Length))"
        }
        break
    }
}

if (-not $imported) {
    Write-Host 'Auto import failed. Run manually:' -ForegroundColor Yellow
    Write-Host "  mysql -u root -p < `"$SqlFile`""
} elseif ($workingPass -ne '') {
    $localCfg = Join-Path $ProjectRoot 'config.local.php'
    @"
<?php
`$host = '127.0.0.1';
`$db   = 'phishguard';
`$user = 'root';
`$pass = '$workingPass';
"@ | Set-Content -Path $localCfg -Encoding UTF8
    Write-Host "Wrote config.local.php"
}

Write-Step 'Step 3: Offline QR (optional)'
$imgDir = Join-Path $ProjectRoot 'img'
$qrFile = Join-Path $imgDir 'qrcode-static.png'
New-Item -ItemType Directory -Force -Path $imgDir | Out-Null
if (-not (Test-Path $qrFile)) {
    try {
        $qrLink = ($BaseUrl -replace 'index\.php', 'index.php?from=qrcode')
        $api = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + [Uri]::EscapeDataString($qrLink)
        Invoke-WebRequest -Uri $api -OutFile $qrFile -UseBasicParsing -TimeoutSec 15
        Write-Host "Saved img/qrcode-static.png"
    } catch {
        Write-Host 'QR download skipped (no network). Use online qrcode.php or save PNG manually.' -ForegroundColor Yellow
    }
} else {
    Write-Host 'img/qrcode-static.png already exists'
}

Write-Step 'Step 4: Smoke test'
try {
    $resp = Invoke-WebRequest -Uri $BaseUrl -UseBasicParsing -TimeoutSec 10
    if ($resp.StatusCode -eq 200 -and $resp.Content -match 'id="timer"') {
        Write-Host "Smoke test OK: $BaseUrl" -ForegroundColor Green
    } else {
        Write-Host "Page reachable but unexpected content ($($resp.StatusCode))" -ForegroundColor Yellow
    }
} catch {
    Write-Host "Smoke test failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`nNext: .\run-tests.ps1"
Write-Host "After defense: .\truncate-demo-data.ps1"
