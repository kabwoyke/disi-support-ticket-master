@echo off
setlocal
title Seeding disi-support database...

:: Run from the folder this script lives in
cd /d "%~dp0"

echo ============================================================
echo  This will WIPE and rebuild the main and solves databases.
echo ============================================================
set /p CONFIRM=Type YES to continue:
if /i not "%CONFIRM%"=="YES" (
    echo Cancelled.
    exit /b 1
)

:: migrate:fresh only drops tables on the default connection, so the
:: separate solves database must be wiped too or its migrations fail
:: with "table already exists".
echo.
echo [1/9] Wiping solves database (mysql_solves)...
call php artisan db:wipe --database=mysql_solves --force
if errorlevel 1 goto :fail

echo.
echo [2/9] Running migrations from scratch...
call php artisan migrate:fresh --force
if errorlevel 1 goto :fail

:: Seeders run in dependency order (matches migration order):
::   departments -> categories -> equipments (needs categories)
::   -> desks -> support_teams (needs categories) -> solve_users
echo.
echo [3/9] DepartmentSeeder...
call php artisan db:seed --class=DepartmentSeeder --force
if errorlevel 1 goto :fail

echo.
echo [4/9] CategorySeeder...
call php artisan db:seed --class=CategorySeeder --force
if errorlevel 1 goto :fail

echo.
echo [5/9] EquipmentSeeder...
call php artisan db:seed --class=EquipmentSeeder --force
if errorlevel 1 goto :fail

echo.
echo [6/9] DeskSeeder...
call php artisan db:seed --class=DeskSeeder --force
if errorlevel 1 goto :fail

echo.
echo [7/9] SupportTeamSeeder...
call php artisan db:seed --class=SupportTeamSeeder --force
if errorlevel 1 goto :fail

echo.
echo [8/9] SolveUserSeeder...
call php artisan db:seed --class=SolveUserSeeder --force
if errorlevel 1 goto :fail

echo.
echo [9/9] DatabaseSeeder (test@example.com admin login user)...
call php artisan db:seed --class=DatabaseSeeder --force
if errorlevel 1 goto :fail

echo.
echo ============================================================
echo  Database seeded successfully.
echo ============================================================
pause
exit /b 0

:fail
echo.
echo ============================================================
echo  SEEDING FAILED - see the error above. Nothing after the
echo  failed step was run.
echo ============================================================
pause
exit /b 1
