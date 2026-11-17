@echo off
REM Script para arrancar la aplicación (doble clic).
setlocal
cd /d "%~dp0"

REM Si falta la carpeta vendor, avisa de que hay que instalar primero.
if not exist vendor\autoload.php (
    echo [ERROR] No existe vendor\autoload.php
    echo Ejecuta primero setup_xampp.bat o composer install.
    pause
    exit /b 1
)

echo Iniciando ERP en http://127.0.0.1:8001
REM Arranca el servidor en http://127.0.0.1:8001.
php artisan serve --host=127.0.0.1 --port=8001
