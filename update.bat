@echo off
cd /d C:\xampp\htdocs\LioLoto
git reset --hard origin/main
git pull origin main
npm install
npm run build
