# Deploy to Hostinger via FTP (reads credentials from .env.deploy)
param(
    [int]$Minutes = 30,
    [string[]]$Files = @(),
    [switch]$All,
    [switch]$UploadZip
)

$localRoot  = "C:\xampp\htdocs\southdev-home-depot"
$deployEnv  = Join-Path $localRoot ".env.deploy"
$remoteRoot = "/domains/southdevhomedepotdavao.com/public_html"

if (-not (Test-Path $deployEnv)) {
    Write-Host "Missing .env.deploy - copy .env.deploy.example to .env.deploy and add your FTP password." -ForegroundColor Red
    Write-Host "Get FTP details: hPanel -> Files -> FTP Accounts" -ForegroundColor Yellow
    exit 1
}

$cfg = @{}
Get-Content $deployEnv | ForEach-Object {
    if ($_ -match '^\s*([^#=]+)=(.*)$') {
        $cfg[$matches[1].Trim()] = $matches[2].Trim().Trim('"')
    }
}

$ftpHost = $cfg['FTP_HOST']
$ftpUser = $cfg['FTP_USER']
$ftpPass = $cfg['FTP_PASS']
if ($cfg['FTP_REMOTE_ROOT']) {
    $remoteRoot = $cfg['FTP_REMOTE_ROOT'].TrimEnd('/')
}

if (-not $ftpHost -or -not $ftpUser -or -not $ftpPass) {
    Write-Host "FTP_HOST, FTP_USER, and FTP_PASS required in .env.deploy" -ForegroundColor Red
    exit 1
}

$excludeDirs  = @('.git', 'repo.git', 'docs', 'database', '.venv', 'vendor', 'storage\mails', 'tools', 'node_modules')
$excludeFiles = @(
    'southdev-hostinger.zip', 'southdev-deploy.zip',
    'deploy.ps1', 'deploy-hostinger.ps1', 'deploy-git-status.ps1', 'deploy-full-ftp.py',
    'ftp_upload.ps1', 'ftp_curl_upload.ps1', 'pack-for-hostinger.ps1',
    '.env', '.env.deploy', '.env.production', '.env.hostinger.example', '.env.deploy.example', '.env.example', '.gitignore'
)

function Upload-File($localPath, $remotePath) {
    $remoteDir  = ($remotePath -replace '/[^/]+$','')
    $remoteFile = ($remotePath -split '/')[-1]
    $localEsc   = $localPath.Replace("\", "\\")

    $result = python -c @"
import ftplib, sys
try:
    ftp = ftplib.FTP('$ftpHost', timeout=120)
    ftp.login('$ftpUser', '$ftpPass')
    ftp.set_pasv(True)
    parts = '$remoteDir'.strip('/').split('/')
    ftp.cwd('/')
    for p in parts:
        if not p: continue
        try: ftp.cwd(p)
        except:
            try: ftp.mkd(p); ftp.cwd(p)
            except: pass
    with open(r'$localEsc', 'rb') as f:
        ftp.storbinary('STOR $remoteFile', f, 8192)
    ftp.quit()
    print('OK')
except Exception as e:
    print('ERR:' + str(e))
"@ 2>&1

    if ($result -match '^OK') {
        Write-Host "  [OK] $remotePath" -ForegroundColor Cyan
        return $true
    }
    Write-Host "  [FAIL] $remotePath - $result" -ForegroundColor Red
    return $false
}

Write-Host ""
Write-Host "Deploy to Hostinger ($ftpHost)" -ForegroundColor Yellow
Write-Host "Remote root: $remoteRoot" -ForegroundColor Yellow
Write-Host ""

if ($UploadZip) {
    $zip = Join-Path $localRoot "southdev-hostinger.zip"
    if (-not (Test-Path $zip)) {
        Write-Host "Run pack-for-hostinger.ps1 first." -ForegroundColor Red
        exit 1
    }
    Write-Host "Uploading southdev-hostinger.zip ..." -ForegroundColor White
    if (Upload-File $zip "$remoteRoot/southdev-hostinger.zip") {
        Write-Host ""
        Write-Host "Zip uploaded. In File Manager: public_html -> Extract southdev-hostinger.zip -> delete zip." -ForegroundColor Green
    }
    exit
}

# Always upload production .env
$envProd = Join-Path $localRoot ".env.production"
if (Test-Path $envProd) {
    Write-Host "Uploading .env ..." -ForegroundColor White
    Upload-File $envProd "$remoteRoot/.env" | Out-Null
}

if ($Files.Count -gt 0) {
    foreach ($rel in $Files) {
        $relNorm = $rel.Replace('\', '/')
        $baseName = [IO.Path]::GetFileName($relNorm)
        $skip = $false
        foreach ($f in $excludeFiles) {
            if ($baseName -eq $f -or $relNorm -eq $f) { $skip = $true; break }
        }
        if ($skip) {
            Write-Host "  [SKIP] $relNorm (excluded)" -ForegroundColor DarkYellow
            continue
        }
        $rel = $rel.Replace([char]47, [IO.Path]::DirectorySeparatorChar)
        $full = Join-Path $localRoot $rel
        if (Test-Path $full) {
            Upload-File $full "$remoteRoot/$($rel.Replace([IO.Path]::DirectorySeparatorChar, [char]47))"
        }
    }
} else {
    $cutoff = if ($All) { (Get-Date).AddYears(-10) } else { (Get-Date).AddMinutes(-$Minutes) }
    $changed = Get-ChildItem -Path $localRoot -Recurse -File |
        Where-Object { $_.LastWriteTime -gt $cutoff } |
        Where-Object {
            $rel = $_.FullName.Substring($localRoot.Length + 1)
            $top = $rel.Split([IO.Path]::DirectorySeparatorChar)[0]
            $skip = $false
            foreach ($d in $excludeDirs) { if ($rel -like "$d*") { $skip = $true; break } }
            if (-not $skip) { foreach ($f in $excludeFiles) { if ($_.Name -eq $f) { $skip = $true; break } } }
            -not $skip
        }
    foreach ($f in $changed) {
        $rel = $f.FullName.Substring($localRoot.Length + 1).Replace([IO.Path]::DirectorySeparatorChar, [char]47)
        Upload-File $f.FullName "$remoteRoot/$rel" | Out-Null
    }
}

Write-Host ""
Write-Host "Live: https://southdevhomedepotdavao.com" -ForegroundColor Cyan
Write-Host ""
