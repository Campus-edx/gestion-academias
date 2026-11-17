@echo off
REM Script de instalación: prepara el proyecto la primera vez (doble clic).
setlocal
cd /d "%~dp0"

echo ============================================
echo   ERP Formacion Base - Beta 0.2.1 ES
echo ============================================
echo.

REM Comprueba que PHP está instalado (en el PATH o en C:\xampp\php).
where php >nul 2>nul
if errorlevel 1 (
    if exist C:\xampp\php\php.exe (
        set "PHP_BIN=C:\xampp\php\php.exe"
    ) else (
        echo [ERROR] PHP no esta disponible en PATH ni en C:\xampp\php\php.exe
        pause
        exit /b 1
    )
) else (
    set "PHP_BIN=php"
)

REM Comprueba que Composer está instalado.
where composer >nul 2>nul
if errorlevel 1 (
    echo [ERROR] Composer no esta disponible en PATH.
    echo Instala Composer 2 y vuelve a ejecutar este archivo.
    pause
    exit /b 1
)

REM Si no existe .env, lo crea copiando .env.example.
if not exist .env (
    copy .env.example .env >nul
    echo [OK] .env creado desde .env.example
) else (
    echo [OK] .env ya incluido
)

echo.
echo Instalando/comprobando dependencias PHP...
REM Instala las librerías (carpeta vendor).
call composer install
if errorlevel 1 goto :error

echo.
echo Limpiando caches...
REM Limpia las cachés de Laravel.
%PHP_BIN% artisan optimize:clear
if errorlevel 1 goto :error

echo.
echo ============================================
echo Codigo preparado.
echo 1. Importa database\erp_formacion_beta.sql
echo 2. Ejecuta start_artisan.bat
echo 3. Abre http://127.0.0.1:8001
echo ============================================
pause
exit /b 0

:error
echo.
REM Si algún paso falla, se llega aquí y se muestra el error.
echo [ERROR] El setup se ha detenido. Revisa el mensaje anterior.
pause
exit /b 1
