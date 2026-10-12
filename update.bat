@echo off
rem Compatibility entrypoint. Select the source release before running this script.
rem Deployment preserves working files and applies only forward database migrations.
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\deploy.ps1" %*
exit /b %errorlevel%
