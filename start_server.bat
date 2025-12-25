@echo off
echo Starting Survey System...
echo Note: This script attempts to use PHP from your system or XAMPP.

set PHP_BIN=php

:: Check if php is in PATH
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo PHP not found in PATH. Checking common XAMPP locations...
    if exist "C:\xampp\php\php.exe" (
        set PHP_BIN="C:\xampp\php\php.exe"
    ) else (
        echo PHP not found. Please ensure XAMPP is installed or PHP is in your PATH.
        pause
        exit /b
    )
)

echo Using PHP at: %PHP_BIN%
echo Opening browser...
start http://localhost:8000

echo Starting Server at http://localhost:8000
echo Press Ctrl+C to stop.
%PHP_BIN% -S localhost:8000 -t public
pause
