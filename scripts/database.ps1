param([switch]$Fresh, [string]$ConfirmTarget, [switch]$Demo, [switch]$Seed)
$ErrorActionPreference = 'Stop'
$taskArguments = @('database')
if ($Fresh) { $taskArguments += '--fresh' }
if ($ConfirmTarget) { $taskArguments += "--confirm=$ConfirmTarget" }
if ($Demo) { $taskArguments += '--demo' }
if ($Seed) { $taskArguments += '--seed' }
& php (Join-Path $PSScriptRoot 'manage.php') @taskArguments
if ($LASTEXITCODE -ne 0) { throw 'Database operation failed.' }
