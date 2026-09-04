@echo off
title Deploy MentorSphere to XAMPP
echo ===================================================
echo   Deploying MentorSphere to XAMPP htdocs
echo ===================================================
echo.

set "XAMPP_DIR=C:\xampp"

if not exist "%XAMPP_DIR%" (
    echo [!] Default XAMPP path "C:\xampp" was not found.
    set /p XAMPP_DIR="Please enter your XAMPP installation directory (e.g. D:\xampp): "
)

if not exist "%XAMPP_DIR%" (
    echo [ERROR] The directory "%XAMPP_DIR%" does not exist.
    echo Please make sure XAMPP is installed.
    pause
    exit /b 1
)

set "DEST_DIR=%XAMPP_DIR%\htdocs\Mid-Term"

echo [*] Creating destination directory: %DEST_DIR%
if not exist "%DEST_DIR%" mkdir "%DEST_DIR%"
if not exist "%DEST_DIR%\uploads" mkdir "%DEST_DIR%\uploads"

echo [*] Copying project files...
copy /Y "%~dp0index.html" "%DEST_DIR%\"
copy /Y "%~dp0index.php" "%DEST_DIR%\"
copy /Y "%~dp0api.php" "%DEST_DIR%\"
copy /Y "%~dp0db.php" "%DEST_DIR%\"
copy /Y "%~dp0style.css" "%DEST_DIR%\"
copy /Y "%~dp0schema.sql" "%DEST_DIR%\"

echo.
echo ===================================================
echo   [SUCCESS] Files successfully copied to:
echo   %DEST_DIR%
echo ===================================================
echo.
echo Next steps:
echo 1. Ensure Apache and MySQL are running in XAMPP Control Panel.
echo 2. Import schema.sql in phpMyAdmin (http://localhost/phpmyadmin/).
echo 3. Open http://localhost/Mid-Term/ in your browser.
echo.
set /p OPEN_BROWSER="Do you want to open http://localhost/Mid-Term/ in browser now? (Y/N): "
if /i "%OPEN_BROWSER%"=="Y" (
    start http://localhost/Mid-Term/
)

pause
