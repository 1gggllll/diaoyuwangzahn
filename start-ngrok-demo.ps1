# 课题一公网演示（方案 B）：ngrok 临时映射本机站点
# 使用前：1) 已运行 setup-local.ps1 或 XAMPP；2) 已安装 ngrok 并配置 authtoken
$ErrorActionPreference = 'Stop'
$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$PhpExe = Join-Path $ProjectRoot '.tools\php\php.exe'
$Router = Join-Path $ProjectRoot 'router.php'
$ApachePort = 80
$BuiltinPort = 8080

function Test-PortListen($port) {
    return [bool](Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue)
}

# 优先使用 XAMPP Apache（大纲「公网 Web 服务器」语义更接近）
if (Test-PortListen $ApachePort) {
    $targetPort = $ApachePort
    $demoUrl = 'http://localhost/phishguard/index.php'
    Write-Host "检测到 Apache 监听 80，将映射 $demoUrl" -ForegroundColor Green
} elseif (Test-Path $PhpExe) {
    if (-not (Test-PortListen $BuiltinPort)) {
        Start-Process -FilePath $PhpExe -ArgumentList @('-S', "localhost:$BuiltinPort", $Router) -WorkingDirectory $ProjectRoot -WindowStyle Hidden
        Start-Sleep -Seconds 2
    }
    $targetPort = $BuiltinPort
    $demoUrl = "http://localhost:$BuiltinPort/phishguard/index.php"
    Write-Host "使用 PHP 内置服务器：$demoUrl" -ForegroundColor Yellow
} else {
    throw '请先启动 XAMPP Apache 或运行 setup-local.ps1'
}

$ngrok = Get-Command ngrok -ErrorAction SilentlyContinue
if (-not $ngrok) {
    Write-Host @"

未找到 ngrok 命令。安装步骤：
  1. 访问 https://ngrok.com/download 下载 Windows 版
  2. 注册账号后执行：ngrok config add-authtoken <你的token>
  3. 重新运行本脚本

答辩合规提醒：
  - 仅向指导教师演示公网链接
  - 演示结束立即 Ctrl+C 停止 ngrok
  - 执行：TRUNCATE TABLE phishguard.phishing_records;

"@ -ForegroundColor Yellow
    exit 1
}

Write-Host "`n即将启动 ngrok，公网 URL 将显示在下方。" -ForegroundColor Cyan
Write-Host "演示入口：/phishguard/index.php?from=url" -ForegroundColor Cyan
Write-Host "防护演示：/phishguard/login.php?test_block=1" -ForegroundColor Cyan
Write-Host "按 Ctrl+C 结束公网映射`n" -ForegroundColor Cyan

& ngrok http $targetPort
