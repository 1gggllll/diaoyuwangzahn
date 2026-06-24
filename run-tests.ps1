# PhishGuard automated test runner (T01-T13, C01) - ASCII-only for encoding safety
$ErrorActionPreference = 'Stop'
$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$PhpExe = Join-Path $ProjectRoot '.tools\php\php.exe'
$Router = Join-Path $ProjectRoot 'router.php'
$Port = 8080
$Base = "http://localhost:$Port/phishguard"
$results = @()

function Add-Result($id, $pass, $detail) {
    $script:results += [PSCustomObject]@{ Id = $id; Pass = $pass; Detail = $detail }
}

function New-WebSessionFor {
    $s = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $s.Cookies = New-Object System.Net.CookieContainer
    return $s
}

function Follow-Redirect($response, $session) {
    if ($response.StatusCode -ge 300 -and $response.StatusCode -lt 400) {
        $loc = $response.Headers['Location']
        if ($loc -notmatch '^https?://') {
            $base = [Uri]$response.BaseResponse.ResponseUri
            $loc = (New-Object Uri($base, $loc)).AbsoluteUri
        }
        return Invoke-WebRequest -Uri $loc -WebSession $session -UseBasicParsing
    }
    return $response
}

function Submit-And-Check($session, $from, $account, $password) {
    Invoke-WebRequest -Uri "$Base/index.php?from=$from" -WebSession $session -UseBasicParsing | Out-Null
    Invoke-WebRequest -Uri "$Base/login.php?from=$from" -WebSession $session -UseBasicParsing | Out-Null
    $post = Invoke-WebRequest -Uri "$Base/submit.php" -WebSession $session -Method POST -Body @{
        account  = $account
        password = $password
        channel  = 'direct'
    } -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
    return Follow-Redirect $post $session
}

if (-not (Test-Path $PhpExe)) { throw "Missing PHP: $PhpExe" }

if (-not (Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue)) {
    Start-Process -FilePath $PhpExe -ArgumentList @('-S', "localhost:$Port", $Router) -WorkingDirectory $ProjectRoot -WindowStyle Hidden
    Start-Sleep -Seconds 2
}

try {
    Invoke-WebRequest -Uri "$Base/index.php" -UseBasicParsing -TimeoutSec 8 | Out-Null
} catch {
    throw "Web not reachable: $($_.Exception.Message)"
}

$s1 = New-WebSessionFor
$index = Invoke-WebRequest -Uri "$Base/index.php" -WebSession $s1 -UseBasicParsing
$loginHref = ($index.Links | Where-Object { $_.href -match 'login\.php' } | Select-Object -First 1).href
$loginUrl = if ($loginHref) {
    if ($loginHref -match '^https?://') { $loginHref } else { "$Base/$($loginHref.TrimStart('./'))" }
} else { "$Base/login.php?from=direct" }
$login = Invoke-WebRequest -Uri $loginUrl -WebSession $s1 -UseBasicParsing
Add-Result 'T01' ($login.Content -match 'action="submit.php"' -and $login.Content -match 'auth-form') 'index to login form ok'

$s2 = New-WebSessionFor
$success = Submit-And-Check $s2 'direct' 'test' '123456'
Add-Result 'T02' (($success.Content -match '<title>') -and ($success.BaseResponse.ResponseUri.AbsolutePath -match 'success\.php')) 'submit redirects to success'

$admin = New-WebSessionFor
$adminLogin = Invoke-WebRequest -Uri "$Base/admin/login.php" -WebSession $admin -Method POST -Body @{ user = 'admin'; pass = 'admin123' } -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
$adminHome = Follow-Redirect $adminLogin $admin
Add-Result 'T03' ($adminHome.BaseResponse.ResponseUri.AbsolutePath -match 'admin/index\.php') 'admin session ok'
Add-Result 'T04' ($adminHome.Content -match 'test' -and $adminHome.Content -match '123456') 'credentials visible in admin'

$s5 = New-WebSessionFor
Submit-And-Check $s5 'qrcode' 'qr_user' 'qr_pass' | Out-Null
$admin2 = New-WebSessionFor
Invoke-WebRequest -Uri "$Base/admin/login.php" -WebSession $admin2 -Method POST -Body @{ user = 'admin'; pass = 'admin123' } -UseBasicParsing | Out-Null
$list5 = Invoke-WebRequest -Uri "$Base/admin/index.php" -WebSession $admin2 -UseBasicParsing
Add-Result 'T05' ($list5.Content -match 'qr_user' -and $list5.Content -match '>qrcode<') 'channel qrcode'

$s6 = New-WebSessionFor
Submit-And-Check $s6 'url' 'url_user' 'url_pass' | Out-Null
$admin3 = New-WebSessionFor
Invoke-WebRequest -Uri "$Base/admin/login.php" -WebSession $admin3 -Method POST -Body @{ user = 'admin'; pass = 'admin123' } -UseBasicParsing | Out-Null
$list6 = Invoke-WebRequest -Uri "$Base/admin/index.php" -WebSession $admin3 -UseBasicParsing
Add-Result 'T06' ($list6.Content -match 'url_user' -and $list6.Content -match '>url<') 'channel url'

$def = Invoke-WebRequest -Uri "$Base/defense.php" -UseBasicParsing
Add-Result 'T07' ($def.Content -match 'class="tips"') 'defense tips present'

$block = Invoke-WebRequest -Uri "$Base/login.php?test_block=1" -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
$blockFinal = Follow-Redirect $block (New-WebSessionFor)
Add-Result 'T08' ($blockFinal.BaseResponse.ResponseUri.Query -match 'blocked=1') 'test_block to defense'

$s9 = New-WebSessionFor
Invoke-WebRequest -Uri "$Base/login.php" -WebSession $s9 -UseBasicParsing | Out-Null
$empty = Invoke-WebRequest -Uri "$Base/submit.php" -WebSession $s9 -Method POST -Body @{ account = ''; password = ''; channel = 'direct' } -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
$emptyFinal = Follow-Redirect $empty $s9
Add-Result 'T09' ($emptyFinal.BaseResponse.ResponseUri.Query -match 'err=empty') 'empty submit err=empty'

$xss = '<script>alert(1)</script>'
$s10 = New-WebSessionFor
Submit-And-Check $s10 'direct' $xss 'xss_pass' | Out-Null
$admin10 = New-WebSessionFor
Invoke-WebRequest -Uri "$Base/admin/login.php" -WebSession $admin10 -Method POST -Body @{ user = 'admin'; pass = 'admin123' } -UseBasicParsing | Out-Null
$list10 = Invoke-WebRequest -Uri "$Base/admin/index.php" -WebSession $admin10 -UseBasicParsing
Add-Result 'T10' ($list10.Content -match '&lt;script&gt;' -and $list10.Content -notmatch '<script>alert') 'xss escaped in admin'

$sql = "admin' OR '1'='1"
$s11 = New-WebSessionFor
Submit-And-Check $s11 'direct' $sql 'sqlpass' | Out-Null
$admin11 = New-WebSessionFor
Invoke-WebRequest -Uri "$Base/admin/login.php" -WebSession $admin11 -Method POST -Body @{ user = 'admin'; pass = 'admin123' } -UseBasicParsing | Out-Null
$list11 = Invoke-WebRequest -Uri "$Base/admin/index.php" -WebSession $admin11 -UseBasicParsing
Add-Result 'T11' ($list11.Content -match "admin' OR '1'='1" -or $list11.Content -match 'admin&#039; OR') 'sql stored as literal'

$anon = New-WebSessionFor
$unauth = Invoke-WebRequest -Uri "$Base/admin/index.php" -WebSession $anon -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
$unauthFinal = Follow-Redirect $unauth $anon
Add-Result 'T12' ($unauthFinal.BaseResponse.ResponseUri.AbsolutePath -match 'login\.php') 'unauth redirect login'

Add-Result 'T13' ($adminHome.Content -match 'http-equiv="refresh" content="3"') 'meta refresh 3s'
Add-Result 'C01' ($login.Content -match 'auth-trust-warning' -and $index.Content -match '限定福利礼包') 'high-fidelity login and index theme'

Write-Host ''
Write-Host '=== Test Results ===' -ForegroundColor Cyan
$results | Format-Table -AutoSize
$failed = @($results | Where-Object { -not $_.Pass })
if ($failed.Count -gt 0) {
    Write-Host "FAILED: $($failed.Count)" -ForegroundColor Red
    $failed | Format-Table -AutoSize
    exit 1
}
Write-Host "PASSED: $($results.Count) items (C02 truncate after defense)" -ForegroundColor Green
exit 0
