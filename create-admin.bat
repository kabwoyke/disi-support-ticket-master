@echo off
setlocal
title Create Filament admin user

cd /d "%~dp0"

:: make:filament-user enforces an 8-character minimum password, so the user
:: is created directly instead. Safe to re-run: it updates the existing user.
echo Creating Filament admin user (admin@gmail.com / 123456)...
call php artisan tinker --execute="App\Models\User::updateOrCreate(['email' => 'admin@gmail.com'], ['name' => 'Admin', 'password' => '123456', 'email_verified_at' => now()]); echo 'OK';"
if errorlevel 1 (
    echo.
    echo Failed. Make sure migrations have been run ^(see seed-db.bat^).
    pause
    exit /b 1
)

echo.
echo.
echo Done. Log in at /admin with admin@gmail.com / 123456
pause
