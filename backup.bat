@echo off
setlocal EnableDelayedExpansion

:: ============================================================================
:: MANXMARKET POS & GROSIR - Otomasi Cadangan Database MySQL (ACID)
:: Rotasi 7 Hari + Salinan Cloud OneDrive & Drive Eksternal
:: ============================================================================

set "MYSQLDUMP=mysqldump.exe"
set "DB_NAME=db_manxmarket"
if not defined DB_USER set "DB_USER=root"
set "LOCAL_DIR=%USERPROFILE%\manxmarket_backups"
set "OFFSITE_DIR="
if defined OneDrive set "OFFSITE_DIR=%OneDrive%\manxmarket_backups"
set "LOG_FILE=%LOCAL_DIR%\backup_history.log"
set "RETENTION_DAYS=7"

if not exist "%LOCAL_DIR%" mkdir "%LOCAL_DIR%"
if defined OFFSITE_DIR if not exist "%OFFSITE_DIR%" mkdir "%OFFSITE_DIR%"

:: Format Waktu ISO YYYY-MM-DD_HHMMSS
set "CURR_DATE=%date%"
set "CURR_TIME=%time%"
set "YYYY=%CURR_DATE:~6,4%"
set "MM=%CURR_DATE:~3,2%"
set "DD=%CURR_DATE:~0,2%"
set "HH=%CURR_TIME:~0,2%"
if "%HH:~0,1%"==" " set "HH=0%HH:~1,1%"
set "MIN=%CURR_TIME:~3,2%"
set "SEC=%CURR_TIME:~6,2%"

set "TS=%YYYY%-%MM%-%DD%_%HH%%MIN%%SEC%"
set "BACKUP_FILE=%LOCAL_DIR%\db_manxmarket_%TS%.sql"
set "BACKUP_TEMP=%BACKUP_FILE%.tmp"

echo [%date% %time%] [INFO] Memulai pencadangan database %DB_NAME%... >> "%LOG_FILE%"

:: Eksekusi mysqldump
"%MYSQLDUMP%" --host=127.0.0.1 -u %DB_USER% --single-transaction --quick --result-file="%BACKUP_TEMP%" %DB_NAME%

if %ERRORLEVEL% neq 0 (
    echo [%date% %time%] [ERROR] mysqldump gagal dengan kode keluar %ERRORLEVEL% >> "%LOG_FILE%"
    if exist "%BACKUP_TEMP%" del "%BACKUP_TEMP%"
    exit /b %ERRORLEVEL%
)

:: Verifikasi integritas ukuran file
for %%F in ("%BACKUP_TEMP%") do set "FILESIZE=%%~zF"
if "%FILESIZE%"=="" set "FILESIZE=0"
if %FILESIZE% leq 100 (
    echo [%date% %time%] [ERROR] File backup tidak valid atau kosong: %FILESIZE% bytes >> "%LOG_FILE%"
    if exist "%BACKUP_TEMP%" del "%BACKUP_TEMP%"
    exit /b 1
)

move /y "%BACKUP_TEMP%" "%BACKUP_FILE%" >nul
if errorlevel 1 (
    echo [%date% %time%] [ERROR] Gagal menyimpan file backup akhir. >> "%LOG_FILE%"
    exit /b 1
)

echo [%date% %time%] [SUCCESS] Backup lokal tersimpan: %BACKUP_FILE% - %FILESIZE% bytes >> "%LOG_FILE%"

:: Salinan cadangan ke cloud offsite (OneDrive)
if defined OFFSITE_DIR if exist "%OFFSITE_DIR%" (
    copy /y "%BACKUP_FILE%" "%OFFSITE_DIR%\" >nul
    if !ERRORLEVEL! equ 0 (
        echo [%date% %time%] [SUCCESS] Salinan offsite cloud tersinkronkan ke %OFFSITE_DIR% >> "%LOG_FILE%"
    ) else (
        echo [%date% %time%] [WARN] Gagal menyalin ke cloud offsite >> "%LOG_FILE%"
    )
)

:: Salinan cadangan ke flashdisk / external storage jika ada (E: sampai H:)
for %%D in (E F G H) do (
    if exist "%%D:\manxmarket_backups" (
        copy /y "%BACKUP_FILE%" "%%D:\manxmarket_backups\" >nul
        if !ERRORLEVEL! equ 0 (
            echo [%date% %time%] [SUCCESS] Salinan offsite drive eksternal %%D: tersimpan >> "%LOG_FILE%"
        )
    )
)

:: Rotasi 7 hari lokal menggunakan forfiles
forfiles /p "%LOCAL_DIR%" /m "*.sql" /d -%RETENTION_DAYS% /c "cmd /c del /f /q @path" 2>nul

:: Rotasi 7 hari offsite cloud jika folder ada
if defined OFFSITE_DIR if exist "%OFFSITE_DIR%" (
    forfiles /p "%OFFSITE_DIR%" /m "*.sql" /d -%RETENTION_DAYS% /c "cmd /c del /f /q @path" 2>nul
)

echo [%date% %time%] [INFO] Siklus pencadangan dan rotasi selesai sempurna. >> "%LOG_FILE%"
exit /b 0
