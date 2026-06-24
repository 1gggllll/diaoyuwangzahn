@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo === PhishGuard project complete setup ===
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup-local.ps1"
if errorlevel 1 (
  echo Setup failed.
  pause
  exit /b 1
)
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0run-tests.ps1"
if errorlevel 1 (
  echo Tests failed.
  pause
  exit /b 1
)
echo.
echo All done. Open http://localhost/phishguard/index.php
echo Or http://localhost:8080/phishguard/index.php
pause
