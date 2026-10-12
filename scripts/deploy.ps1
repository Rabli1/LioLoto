param([switch]$SkipInstall, [switch]$SkipBuild)
$ErrorActionPreference = 'Stop'
$taskArguments = @('deploy')
if ($SkipInstall) { $taskArguments += '--skip-install' }
if ($SkipBuild) { $taskArguments += '--skip-build' }
& php (Join-Path $PSScriptRoot 'manage.php') @taskArguments
if ($LASTEXITCODE -ne 0) { throw 'Deployment failed; inspect the error before reopening maintenance mode.' }
