@echo off
REM Chay website bang PHP built-in server (khong can Apache). Can bat MySQL trong XAMPP truoc.
chcp 65001 >nul
set PHP=php
where php >nul 2>nul || set PHP=C:\xampp\php\php.exe
echo Mo trinh duyet tai http://localhost:8000
%PHP% -S localhost:8000 -t public
