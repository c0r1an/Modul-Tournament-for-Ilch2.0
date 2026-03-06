param(
    [Parameter(Mandatory = $true)]
    [ValidatePattern('^\d+\.\d+\.\d+(?:-[0-9A-Za-z\.-]+)?$')]
    [string]$Version,
    [string]$Repo = 'c0r1an/Modul-Tournament-for-Ilch2.0',
    [string]$Title = '',
    [string]$Notes = '',
    [switch]$SkipCleanCheck
)

$ErrorActionPreference = 'Stop'
$oldNativePref = $null
if (Get-Variable PSNativeCommandUseErrorActionPreference -ErrorAction SilentlyContinue) {
    $oldNativePref = $PSNativeCommandUseErrorActionPreference
    $PSNativeCommandUseErrorActionPreference = $false
}

function Invoke-Step {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Command
    )

    Write-Host ">> $Command" -ForegroundColor Cyan
    Invoke-Expression $Command
    if ($LASTEXITCODE -ne 0) {
        throw "Command failed with exit code ${LASTEXITCODE}: $Command"
    }
}

function Get-NativeExitCode {
    param(
        [Parameter(Mandatory = $true)]
        [string]$FilePath,
        [string[]]$ArgumentList = @()
    )

    $stdoutFile = [System.IO.Path]::GetTempFileName()
    $stderrFile = [System.IO.Path]::GetTempFileName()

    try {
        $proc = Start-Process -FilePath $FilePath -ArgumentList $ArgumentList -NoNewWindow -Wait -PassThru -RedirectStandardOutput $stdoutFile -RedirectStandardError $stderrFile
        return [int]$proc.ExitCode
    } finally {
        Remove-Item $stdoutFile -Force -ErrorAction SilentlyContinue
        Remove-Item $stderrFile -Force -ErrorAction SilentlyContinue
    }
}

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$repoDir = (Resolve-Path (Join-Path $scriptDir '..')).Path
Set-Location $repoDir

Invoke-Step 'git rev-parse --is-inside-work-tree'

if (-not $SkipCleanCheck) {
    $dirty = git status --porcelain
    if (-not [string]::IsNullOrWhiteSpace(($dirty | Out-String))) {
        throw "Working tree is not clean. Commit/stash changes or use -SkipCleanCheck."
    }
}

$ghExe = $null
if (Get-Command gh -ErrorAction SilentlyContinue) {
    $ghExe = 'gh'
} elseif (Test-Path 'C:\Tools\gh\bin\gh.exe') {
    $ghExe = 'C:\Tools\gh\bin\gh.exe'
} else {
    throw "GitHub CLI (gh) not found. Install gh first."
}

$tag = "v$Version"
$tagExists = $false
git rev-parse --verify --quiet $tag | Out-Null
if ($LASTEXITCODE -eq 0) {
    $tagExists = $true
}

if (-not $tagExists) {
    Invoke-Step "git tag -a $tag -m `"Tournament Module $tag`""
} else {
    Write-Host "Tag $tag already exists locally." -ForegroundColor Yellow
}

Invoke-Step 'git push origin main'
Invoke-Step "git push origin $tag"

$hasToken = -not [string]::IsNullOrWhiteSpace($env:GH_TOKEN) -or -not [string]::IsNullOrWhiteSpace($env:GITHUB_TOKEN)
$authOk = $true
$authExitCode = Get-NativeExitCode -FilePath $ghExe -ArgumentList @('auth', 'status')
if ($authExitCode -ne 0 -and -not $hasToken) {
    $authOk = $false
}

if (-not $authOk) {
    throw "GitHub auth missing. Run 'gh auth login' or set GH_TOKEN."
}

if ([string]::IsNullOrWhiteSpace($Title)) {
    $Title = $tag
}

if ([string]::IsNullOrWhiteSpace($Notes)) {
    $Notes = @"
Tournament Module $tag

- Tag created and pushed
- Main branch pushed
- Release created by automation script
"@
}

$notesFile = [System.IO.Path]::GetTempFileName()
[System.IO.File]::WriteAllText($notesFile, $Notes, (New-Object System.Text.UTF8Encoding($false)))

try {
    $releaseViewExitCode = Get-NativeExitCode -FilePath $ghExe -ArgumentList @('release', 'view', $tag, '--repo', $Repo)
    $releaseExists = ($releaseViewExitCode -eq 0)
    if ($releaseExists) {
        Invoke-Step "& `"$ghExe`" release edit $tag --repo $Repo --title `"$Title`" --notes-file `"$notesFile`""
    } else {
        Invoke-Step "& `"$ghExe`" release create $tag --repo $Repo --title `"$Title`" --notes-file `"$notesFile`""
    }
} finally {
    Remove-Item $notesFile -Force -ErrorAction SilentlyContinue
    if ($null -ne $oldNativePref) {
        $PSNativeCommandUseErrorActionPreference = $oldNativePref
    }
}

Write-Host "Release workflow completed for $tag." -ForegroundColor Green
