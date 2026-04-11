param(
    [switch]$SkipServe
)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
$env:PHP_INI_SCAN_DIR = $PSScriptRoot

function Invoke-Step {
    param(
        [string]$Title,
        [scriptblock]$Action
    )

    Write-Host "==> $Title"
    & $Action
    if ($LASTEXITCODE -ne 0) {
        throw "Gagal pada langkah: $Title (exit code $LASTEXITCODE)"
    }
}

if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
}

if (-not (Test-Path 'database\database.sqlite')) {
    New-Item -ItemType File -Path 'database\database.sqlite' | Out-Null
}

$composerPhar = 'C:\ProgramData\ComposerSetup\bin\composer.phar'
$composerArgs = @('install', '--no-interaction', '--prefer-dist', '--no-progress')

if (Test-Path $composerPhar) {
    Invoke-Step -Title 'Composer install' -Action { php -d extension=zip -d extension=fileinfo $composerPhar @composerArgs }
} else {
    Invoke-Step -Title 'Composer install' -Action { composer @composerArgs }
}

Invoke-Step -Title 'Generate app key' -Action { php artisan key:generate --force }
Invoke-Step -Title 'Migrate dan seed' -Action { php -d extension=pdo_sqlite -d extension=sqlite3 artisan migrate --seed --force }
Invoke-Step -Title 'Lint (Pint)' -Action { php vendor\bin\pint --test }
Invoke-Step -Title 'Test (Artisan)' -Action { php -d extension=pdo_sqlite -d extension=sqlite3 artisan test }

if (-not $SkipServe) {
    Write-Host '==> Menjalankan server di http://127.0.0.1:8000'
    php -d extension=pdo_sqlite -d extension=sqlite3 -S 127.0.0.1:8000 -t public public/index.php
}
