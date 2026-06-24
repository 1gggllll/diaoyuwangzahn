@echo off
setlocal enabledelayedexpansion
chcp 65001 >nul
set ROOT=%~dp0
set PHP=%ROOT%.tools\php\php.exe
set ROUTER=%ROOT%router.php
set PORT=8080

if not exist "%PHP%" (
  echo [错误] 未找到 PHP，请先运行 setup-local.ps1
  pause
  exit /b 1
)

echo 启动钓鱼课题演示站点...
echo.
echo 本机访问: http://localhost:%PORT%/phishguard/index.php
echo.

set "LAN_IP="
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do (
  for /f "tokens=1" %%b in ("%%a") do (
    set "IP=%%b"
    if not "!IP!"=="127.0.0.1" (
      if not "!IP:~0,8!"=="169.254." (
        echo 手机同 WiFi 访问: http://!IP!:%PORT%/phishguard/index.php
        if not defined LAN_IP set "LAN_IP=!IP!"
      )
    )
  )
)

if defined LAN_IP (
  echo http://!LAN_IP!:%PORT%/phishguard> "%ROOT%lan-url.txt"
  echo.
  echo 二维码页: http://localhost:%PORT%/phishguard/qrcode.php
) else (
  echo [提示] 未检测到局域网 IP，手机扫码请配置 config.local.php 中的 demo_base
)

echo 不在同一 WiFi 请用 start-ngrok-demo.ps1 做公网映射
echo 按 Ctrl+C 停止服务
echo.

"%PHP%" -S 0.0.0.0:%PORT% "%ROUTER%"
