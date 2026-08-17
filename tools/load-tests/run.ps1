# SouthDev Home Depot — k6 load test runner (Windows)
# Usage:
#   .\tools\load-tests\run.ps1 smoke
#   .\tools\load-tests\run.ps1 browse
#   .\tools\load-tests\run.ps1 stress
#   .\tools\load-tests\run.ps1 local-cart
#   .\tools\load-tests\run.ps1 all

param(
    [ValidateSet('smoke', 'browse', 'stress', 'local-cart', 'all')]
    [string]$Test = 'smoke',

    [string]$BaseUrl = 'https://southdevhomedepotdavao.com',

    [string]$ProductIds = '1,2,3,4,5'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
Set-Location $root

function Resolve-K6 {
    $cmd = Get-Command k6 -ErrorAction SilentlyContinue
    if ($cmd) {
        return $cmd.Source
    }

    $candidates = @(
        'C:\Program Files\k6\k6.exe',
        "$env:LOCALAPPDATA\Programs\k6\k6.exe",
        "$env:ProgramFiles\k6\k6.exe"
    )

    foreach ($path in $candidates) {
        if (Test-Path $path) {
            # Make k6 available for this session without restarting the terminal
            $k6Dir = Split-Path $path -Parent
            if ($env:Path -notlike "*$k6Dir*") {
                $env:Path = "$k6Dir;$env:Path"
            }
            return $path
        }
    }

    Write-Host ''
    Write-Host 'k6 is not installed.' -ForegroundColor Red
    Write-Host 'Install: winget install k6 --source winget' -ForegroundColor Yellow
    Write-Host 'Then close and reopen the terminal, or run this script again.' -ForegroundColor Yellow
    Write-Host 'Or: https://k6.io/docs/get-started/installation/' -ForegroundColor Yellow
    Write-Host ''
    exit 1
}

function Invoke-K6Test {
    param(
        [string]$Script,
        [string]$Url,
        [string]$Ids,
        [string]$K6Exe
    )

    Write-Host ''
    Write-Host "Running $Script against $Url" -ForegroundColor Cyan
    Write-Host ''

    $env:BASE_URL = $Url
    $env:PRODUCT_IDS = $Ids

    & $K6Exe run $Script
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Test failed or thresholds exceeded: $Script" -ForegroundColor Red
        exit $LASTEXITCODE
    }
}

$k6Exe = Resolve-K6

$testsDir = Join-Path $root 'tools\load-tests'

switch ($Test) {
    'smoke' {
        Invoke-K6Test -Script (Join-Path $testsDir '01-smoke.js') -Url $BaseUrl -Ids $ProductIds -K6Exe $k6Exe
    }
    'browse' {
        Write-Host 'WARNING: browse test ramps to 50 concurrent users.' -ForegroundColor Yellow
        Write-Host 'Run during low-traffic hours. Ctrl+C to abort.' -ForegroundColor Yellow
        Start-Sleep -Seconds 3
        Invoke-K6Test -Script (Join-Path $testsDir '02-browse-50.js') -Url $BaseUrl -Ids $ProductIds -K6Exe $k6Exe
    }
    'stress' {
        Write-Host 'WARNING: stress test ramps up to 100 VUs. Use with caution on production.' -ForegroundColor Red
        Start-Sleep -Seconds 5
        Invoke-K6Test -Script (Join-Path $testsDir '03-stress-ramp.js') -Url $BaseUrl -Ids $ProductIds -K6Exe $k6Exe
    }
    'local-cart' {
        $local = 'http://localhost/southdev-home-depot'
        Invoke-K6Test -Script (Join-Path $testsDir '04-local-cart.js') -Url $local -Ids $ProductIds -K6Exe $k6Exe
    }
    'all' {
        Invoke-K6Test -Script (Join-Path $testsDir '01-smoke.js') -Url $BaseUrl -Ids $ProductIds -K6Exe $k6Exe
        Write-Host 'Smoke passed. Starting browse test in 5s...' -ForegroundColor Green
        Start-Sleep -Seconds 5
        Invoke-K6Test -Script (Join-Path $testsDir '02-browse-50.js') -Url $BaseUrl -Ids $ProductIds -K6Exe $k6Exe
    }
}

Write-Host ''
Write-Host 'Done.' -ForegroundColor Green
Write-Host ''
