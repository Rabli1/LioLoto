param([switch]$Demo, [switch]$SkipInstall, [switch]$SkipBuild)
$ErrorActionPreference = 'Stop'
$taskArguments = @('setup')
if ($Demo) { $taskArguments += '--demo' }
if ($SkipInstall) { $taskArguments += '--skip-install' }
if ($SkipBuild) { $taskArguments += '--skip-build' }
& php (Join-Path $PSScriptRoot 'manage.php') @taskArguments
if ($LASTEXITCODE -ne 0) { throw 'Setup failed.' }
