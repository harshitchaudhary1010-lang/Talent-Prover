@echo off
echo ========================================
echo   TalentProve - XAMPP Quick Start
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo WARNING: Not running as administrator
    echo Some features may not work properly.
    echo.
)

echo Starting XAMPP services...
echo.

REM Try to find XAMPP installation
set XAMPP_PATH=C:\xampp
if exist "C:\xampp2.0\xampp_control.exe" set XAMPP_PATH=C:\xampp2.0
if exist "C:\Program Files\XAMPP\xampp_control.exe" set XAMPP_PATH=C:\Program Files\XAMPP

echo XAMPP Path: %XAMPP_PATH%
echo.

REM Start Apache
echo [1/2] Starting Apache...
if exist "%XAMPP_PATH%\apache_start.bat" (
    call "%XAMPP_PATH%\apache_start.bat"
) else (
    echo Apache start script not found at %XAMPP_PATH%
    echo Please start Apache manually from XAMPP Control Panel
)
echo.

REM Start MySQL
echo [2/2] Starting MySQL...
if exist "%XAMPP_PATH%\mysql_start.bat" (
    call "%XAMPP_PATH%\mysql_start.bat"
) else (
    echo MySQL start script not found at %XAMPP_PATH%
    echo Please start MySQL manually from XAMPP Control Panel
)
echo.

echo ========================================
echo   Services Started!
echo ========================================
echo.
echo Your website should now be accessible at:
echo   http://localhost/
echo.
echo To check system status:
echo   http://localhost/check.php
echo.
echo To view setup instructions:
echo   Open: SETUP_INSTRUCTIONS.md
echo.
echo Press any key to open XAMPP Control Panel...
pause >nul

REM Open XAMPP Control Panel
if exist "%XAMPP_PATH%\xampp-control.exe" (
    start "" "%XAMPP_PATH%\xampp-control.exe"
) else if exist "%XAMPP_PATH%\xampp_control.exe" (
    start "" "%XAMPP_PATH%\xampp_control.exe"
) else (
    echo XAMPP Control Panel not found!
    echo Please open it manually.
)

REM Open browser
echo.
echo Opening browser...
timeout /t 3 /nobreak >nul
start http://localhost/check.php

echo.
echo Done! The system check page should open in your browser.
echo.
pause
