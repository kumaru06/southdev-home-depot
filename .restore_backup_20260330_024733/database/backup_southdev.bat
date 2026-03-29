@echo off
REM Backup `southdev` database to a timestamped SQL dump (Windows)
REM Update MYSQL_USER and MYSQL_PWD if needed or run from a secured environment.
set TIMESTAMP=%DATE:~10,4%%DATE:~4,2%%DATE:~7,2%_%TIME:~0,2%%TIME:~3,2%%TIME:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set OUTFILE=backup_southdev_%TIMESTAMP%.sql
REM Adjust user, password, and path to mysqldump if needed
"C:\xampp\mysql\bin\mysqldump.exe" -u root -p southdev > "%OUTFILE%"
if %ERRORLEVEL% EQU 0 (
  echo Backup created: %OUTFILE%
) else (
  echo Backup failed with code %ERRORLEVEL%
)
pause
