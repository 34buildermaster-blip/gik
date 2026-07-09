$ErrorActionPreference = "Stop"

$Root = Resolve-Path (Join-Path $PSScriptRoot "..")
$Backend = Join-Path $Root "backend"
$Frontend = Join-Path $Root "frontend"
$Php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
$Npm = "C:\Program Files\nodejs\npm.cmd"

if (-not (Test-Path -LiteralPath $Php)) {
    $Php = "php"
}

if (-not (Test-Path -LiteralPath $Npm)) {
    $Npm = "npm"
}

Write-Host "Starting 34 Build Master integrated dev..."
Write-Host "Frontend + gateway : http://127.0.0.1:3000"
Write-Host "Admin via gateway   : http://127.0.0.1:3000/admin"
Write-Host "Laravel backend     : http://127.0.0.1:8000"
Write-Host ""
Write-Host "Press Ctrl+C to stop both servers."

$BackendJob = Start-Job -Name "34bm-backend" -ScriptBlock {
    param($Backend, $Php)

    Set-Location $Backend
    $env:APP_URL = "http://127.0.0.1:3000"
    & $Php artisan serve --host=127.0.0.1 --port=8000
} -ArgumentList $Backend, $Php

$FrontendJob = Start-Job -Name "34bm-frontend" -ScriptBlock {
    param($Frontend, $Npm)

    Set-Location $Frontend
    $env:BACKEND_URL = "http://127.0.0.1:8000"
    $env:NEXT_PUBLIC_API_URL = "http://127.0.0.1:3000/backend-api"
    & $Npm run dev -- --hostname 127.0.0.1 --port 3000
} -ArgumentList $Frontend, $Npm

try {
    while ($true) {
        Receive-Job -Job $BackendJob, $FrontendJob
        Start-Sleep -Seconds 1
    }
} finally {
    Stop-Job -Job $BackendJob, $FrontendJob -ErrorAction SilentlyContinue
    Remove-Job -Job $BackendJob, $FrontendJob -Force -ErrorAction SilentlyContinue
}
