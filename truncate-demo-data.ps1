# Truncate demo phishing records after defense (C02)
$ErrorActionPreference = 'Stop'
$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$MysqlExe = 'C:\xampp\mysql\bin\mysql.exe'
if (-not (Test-Path $MysqlExe)) {
    $MysqlExe = 'D:\MYSQL\MySQL Server 8.0\bin\mysql.exe'
}
if (-not (Test-Path $MysqlExe)) {
    throw "MySQL client not found"
}

$sql = "TRUNCATE TABLE phishguard.phishing_records;"
$imported = $false
foreach ($tryPass in @('', '123456', 'root', 'mysql')) {
    $args = @('-u', 'root', '--default-character-set=utf8mb4', '-e', $sql)
    if ($tryPass -ne '') { $args = @('-u', 'root', "-p$tryPass", '--default-character-set=utf8mb4', '-e', $sql) }
    & $MysqlExe @args 2>$null | Out-Null
    if ($LASTEXITCODE -eq 0) {
        $imported = $true
        Write-Host 'C02 OK: phishing_records truncated' -ForegroundColor Green
        break
    }
}
if (-not $imported) { throw 'Truncate failed. Check MySQL password.' }
