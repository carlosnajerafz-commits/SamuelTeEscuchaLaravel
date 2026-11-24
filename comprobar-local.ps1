$ErrorActionPreference = 'Stop'
$phpExecutable = Join-Path $PSScriptRoot '.runtime\php\php.exe'
if (-not (Test-Path -LiteralPath $phpExecutable)) {
    $phpExecutable = (Get-Command php -ErrorAction Stop).Source
}
Push-Location $PSScriptRoot
try {
    & $phpExecutable artisan test --compact
    if ($LASTEXITCODE -ne 0) { throw 'Fallaron las pruebas.' }
} finally {
    Pop-Location
}