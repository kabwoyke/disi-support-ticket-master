@echo off
title Starting Laravel Project...

:: Change directory to the project folder
cd /d "C:\Users\User\Desktop\disi-support\disi-support-ticket-master (1)\disi-support-ticket-master"

echo Starting Laravel Artisan Serve...
start "Laravel Server" cmd /k "php artisan serve"

echo Starting Vite (npm run dev)...
start "Vite Dev Server" cmd /k "npm run dev"

echo Starting Reverb WebSocket Server...
start "Laravel Reverb" cmd /k "php artisan reverb:start --debug"

echo All services launched!