@echo off
REM Deploy membership plan changes: commit, push, then run the pull command on live server
setlocal
cd /d "%~dp0\.."

echo.
echo === Git status ===
git status

echo.
echo === Adding and committing ===
git add -A
git diff --cached --quiet
if %ERRORLEVEL% equ 0 (
    echo No changes to commit.
) else (
    git commit -m "feat: membership plan enhancements - admin terms, capabilities, PayPal demo"
)

echo.
echo === Pushing to remote ===
git push

echo.
echo ============================================================
echo On live server (via PuTTY/SSH), run:
echo.
echo   cd /path/to/ToyHavenPlatform ^&^& git pull ^&^& php artisan migrate --force ^&^& php artisan route:clear ^&^& php artisan config:cache
echo.
echo Replace /path/to/ToyHavenPlatform with your actual project path.
echo ============================================================
echo.
pause
