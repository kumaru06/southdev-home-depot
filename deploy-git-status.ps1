# Upload all git modified + untracked files to Hostinger
param(
    [switch]$IncludeUploads
)

$localRoot = "C:\xampp\htdocs\southdev-home-depot"
Set-Location $localRoot

$files = @()
git status --short | ForEach-Object {
    $path = $_.Substring(3).Trim()
    if ($path.EndsWith('/')) {
        $dir = $path.TrimEnd('/').Replace('/', '\')
        if (Test-Path $dir) {
            Get-ChildItem -Path $dir -Recurse -File | ForEach-Object {
                $files += $_.FullName.Substring($localRoot.Length + 1).Replace('\', '/')
            }
        }
        return
    }
    if (-not $IncludeUploads -and $path -like 'assets/uploads/*') {
        return
    }
    $files += $path.Replace('\', '/')
}

if ($files.Count -eq 0) {
    Write-Host "No changed files to deploy." -ForegroundColor Yellow
    exit 0
}

Write-Host "Deploying $($files.Count) file(s)..." -ForegroundColor Yellow
& "$localRoot\deploy-hostinger.ps1" -Files $files
