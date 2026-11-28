@echo off
setlocal EnableExtensions DisableDelayedExpansion
title MANXMARKET

pushd "%~dp0"
if errorlevel 1 (
    echo [GAGAL] Folder aplikasi tidak dapat dibuka.
    pause
    exit /b 1
)

if not exist "artisan" (
    echo [GAGAL] File artisan tidak ditemukan di folder aplikasi.
    goto :gagal
)

if not exist "vendor\autoload.php" (
    echo [GAGAL] Dependensi belum tersedia. Jalankan composer install terlebih dahulu.
    goto :gagal
)

set "PHP_EXE="
if exist "%~dp0..\..\php\php.exe" set "PHP_EXE=%~dp0..\..\php\php.exe"
for /d %%D in ("%~dp0..\..\bin\php\*") do (
    if not defined PHP_EXE if exist "%%~fD\php.exe" set "PHP_EXE=%%~fD\php.exe"
)

if not defined PHP_EXE (
    for /f "delims=" %%P in ('where php.exe 2^>nul') do (
        if not defined PHP_EXE set "PHP_EXE=%%P"
    )
)

if not defined PHP_EXE (
    echo [GAGAL] PHP tidak ditemukan. Aktifkan PHP Laragon atau tambahkan php.exe ke PATH.
    goto :gagal
)

set "PHP_CLI_SERVER_WORKERS=1"
echo MANXMARKET
echo Buka di browser: http://127.0.0.1:8000
echo Biarkan jendela ini terbuka selama aplikasi digunakan.
echo Tekan Ctrl+C untuk menghentikan server.
echo.

"%PHP_EXE%" artisan serve --host=127.0.0.1 --port=8000
if errorlevel 1 (
    echo [GAGAL] Server tidak dapat berjalan. Lihat pesan di atas dan periksa port 8000.
    goto :gagal
)

popd
exit /b 0

:gagal
popd
pause
exit /b 1
