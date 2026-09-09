param(
    [string] $PhpPath = 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe',
    [string] $ComposerPhar = 'C:\laragon\bin\composer\composer.phar',
    [string] $OutputDirectory = ''
)

$ErrorActionPreference = 'Stop'

$backendRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$repositoryRoot = Split-Path $backendRoot -Parent
if ([string]::IsNullOrWhiteSpace($OutputDirectory)) {
    $OutputDirectory = Join-Path $repositoryRoot 'artifacts'
}

$outputRoot = [IO.Path]::GetFullPath($OutputDirectory)
$temporaryRoot = [IO.Path]::GetFullPath([IO.Path]::GetTempPath())
$stagingRoot = Join-Path $temporaryRoot 'buildmaster-hostatom-release'
$applicationRoot = Join-Path $stagingRoot 'buildmaster'
$publicRoot = Join-Path $stagingRoot 'public_html'
$archivePath = Join-Path $outputRoot ("hostatom-release-{0}.zip" -f (Get-Date -Format 'yyyyMMdd-HHmmss'))

New-Item -ItemType Directory -Force -Path $outputRoot | Out-Null
if (Test-Path $stagingRoot) {
    $resolvedStaging = [IO.Path]::GetFullPath($stagingRoot)
    if (-not $resolvedStaging.StartsWith($temporaryRoot, [StringComparison]::OrdinalIgnoreCase) -or
        (Split-Path $resolvedStaging -Leaf) -ne 'buildmaster-hostatom-release') {
        throw "Refusing to clean an unexpected staging path: $resolvedStaging"
    }
    Remove-Item -LiteralPath ("\\?\" + $resolvedStaging) -Recurse -Force
}

New-Item -ItemType Directory -Force -Path $applicationRoot, $publicRoot | Out-Null
$excluded = @('.env', '.env.backup', '.env.production', '.git', '.phpunit.result.cache', 'node_modules', 'public', 'storage', 'tests', 'vendor')
Get-ChildItem -LiteralPath $backendRoot -Force |
    Where-Object { $_.Name -notin $excluded } |
    ForEach-Object { Copy-Item -LiteralPath $_.FullName -Destination $applicationRoot -Recurse -Force }

$storageDirectories = @(
    'app/private/quarantine',
    'app/public',
    'framework/cache/data',
    'framework/sessions',
    'framework/testing',
    'framework/views',
    'logs'
)
foreach ($directory in $storageDirectories) {
    New-Item -ItemType Directory -Force -Path (Join-Path $applicationRoot "storage/$directory") | Out-Null
}
New-Item -ItemType Directory -Force -Path (Join-Path $applicationRoot 'bootstrap/cache') | Out-Null
Get-ChildItem -LiteralPath (Join-Path $applicationRoot 'bootstrap/cache') -Force |
    Remove-Item -Force
$localDatabase = Join-Path $applicationRoot 'database/database.sqlite'
if (Test-Path $localDatabase) {
    Remove-Item -LiteralPath $localDatabase -Force
}

$composerArguments = @(
    '-d', 'extension=php_zip.dll',
    $ComposerPhar,
    'install',
    "--working-dir=$applicationRoot",
    '--no-dev',
    '--prefer-dist',
    '--optimize-autoloader',
    '--no-interaction',
    '--no-scripts'
)
& $PhpPath @composerArguments
if ($LASTEXITCODE -ne 0) {
    throw 'Composer could not build the production dependency directory.'
}

& $PhpPath -d extension=php_zip.dll (Join-Path $applicationRoot 'artisan') package:discover --ansi
if ($LASTEXITCODE -ne 0) {
    throw 'Laravel package discovery failed in the release directory.'
}

Get-ChildItem -LiteralPath (Join-Path $backendRoot 'public') -Force |
    ForEach-Object { Copy-Item -LiteralPath $_.FullName -Destination $publicRoot -Recurse -Force }

$frontController = Join-Path $publicRoot 'index.php'
$index = [IO.File]::ReadAllText($frontController)
$index = $index.Replace("__DIR__.'/../storage", "__DIR__.'/../buildmaster/storage")
$index = $index.Replace("__DIR__.'/../vendor", "__DIR__.'/../buildmaster/vendor")
$index = $index.Replace("__DIR__.'/../bootstrap", "__DIR__.'/../buildmaster/bootstrap")
$index = $index.Replace(
    "`$app = require_once __DIR__.'/../buildmaster/bootstrap/app.php';",
    "`$app = require_once __DIR__.'/../buildmaster/bootstrap/app.php';`r`n`r`n`$app->usePublicPath(__DIR__);"
)
[IO.File]::WriteAllText($frontController, $index, (New-Object Text.UTF8Encoding($false)))

$notes = @'
HOSTATOM GO RELEASE

1. Extract both buildmaster and public_html into the hosting account home directory.
2. Create buildmaster/.env from buildmaster/.env.example and enter production values.
3. Make buildmaster/storage and buildmaster/bootstrap/cache writable by PHP.
4. Run the migration and optimization commands from DirectAdmin Terminal or one-time Cron Jobs.
5. Remove the one-time Cron Jobs immediately after they finish.

Never place .env or Google credentials inside public_html.
'@
[IO.File]::WriteAllText((Join-Path $stagingRoot 'DEPLOY-NOTES.txt'), $notes, (New-Object Text.UTF8Encoding($false)))

if (Test-Path $archivePath) {
    Remove-Item -LiteralPath $archivePath -Force
}
& tar.exe -a -c -f $archivePath -C $stagingRoot .
if ($LASTEXITCODE -ne 0) {
    throw 'The release ZIP could not be created.'
}
Remove-Item -LiteralPath ("\\?\" + [IO.Path]::GetFullPath($stagingRoot)) -Recurse -Force
Write-Host "Release package created: $archivePath"
