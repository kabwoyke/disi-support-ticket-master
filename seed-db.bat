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
echo [1/10] Wiping solves database (mysql_solves)...
call php artisan db:wipe --database=mysql_solves --force
if errorlevel 1 goto :fail

echo.
echo [2/10] Running migrations from scratch...
call php artisan migrate:fresh --force
if errorlevel 1 goto :fail

:: Seeders run in dependency order (matches migration order):
::   departments -> categories -> equipments (needs categories)
::   -> desks -> support_teams (needs categories) -> solve_users
echo.
echo [3/10] DepartmentSeeder...
call php artisan db:seed --class=DepartmentSeeder --force
if errorlevel 1 goto :fail

echo.
echo [4/10] CategorySeeder...
call php artisan db:seed --class=CategorySeeder --force
if errorlevel 1 goto :fail

echo.
echo [5/10] EquipmentSeeder...
call php artisan db:seed --class=EquipmentSeeder --force
if errorlevel 1 goto :fail

echo.
echo [6/10] DeskSeeder...
call php artisan db:seed --class=DeskSeeder --force
if errorlevel 1 goto :fail

echo.
echo [7/10] SupportTeamSeeder...
call php artisan db:seed --class=SupportTeamSeeder --force
if errorlevel 1 goto :fail

echo.
echo [8/10] SolveUserSeeder...
call php artisan db:seed --class=SolveUserSeeder --force
if errorlevel 1 goto :fail

echo.
echo [9/10] UserSeeder (ticket requester accounts, e.g. test@example.com)...
call php artisan db:seed --class=UserSeeder --force
if errorlevel 1 goto :fail

echo.
echo [10/10] DatabaseSeeder (calls UserSeeder - idempotent)...
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
