param([int]$Port = 8013)
$ErrorActionPreference = 'Stop'
$phpExecutable = Join-Path $PSScriptRoot '.runtime\php\php.exe'
if (-not (Test-Path -LiteralPath $phpExecutable)) {
    $phpExecutable = (Get-Command php -ErrorAction Stop).Source
}
$env:Path = [IO.Path]::GetDirectoryName($phpExecutable) + ';' + $env:Path
Push-Location $PSScriptRoot
try {
    & $phpExecutable artisan serve --host=127.0.0.1 --port=$Port --no-reload
} finally {
    Pop-Location
}