#!/usr/bin
php artisan migrate:fresh --database=mysql_solves
php artisan db:seed --class=SolveUserSeeder
