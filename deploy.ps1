# ============================================================
#  DEPLOY SCRIPT — Push local changes to InfinityFree
# ============================================================
#
#  USAGE:
#    .\deploy.ps1                     Upload files changed in last 30 min
#    .\deploy.ps1 -Minutes 60         Upload files changed in last 60 min
#    .\deploy.ps1 -Files "views/customer/products.php","assets/css/customer.css"
#                                      Upload specific files only
#    .\deploy.ps1 -All                 Upload ALL files (full re-deploy)
#
# ============================================================

param(
    [int]$Minutes = 30,
    [string[]]$Files = @(),
    [switch]$All
)

$ftpHost   = "ftp://ftpupload.net"
$ftpUser   = "if0_41705046"
$ftpPass   = "markperez201"
$localRoot = "C:\xampp\htdocs\southdev-home-depot"
$remoteRoot = "/htdocs"

# Directories/files to always skip
$excludeDirs  = @('.git', 'repo.git', 'docs', 'database')
$excludeFiles = @('southdev-deploy.zip', 'ftp_upload.ps1', 'deploy.ps1',
                   'test.html', 'test.php', 'test_deploy.php',
                   'tmp_index_html.html', 'tmp_products_html.html', 'tmp_products_html_after.css.html',
                   'flowcharts.html', '.env', '.gitignore')

$uploaded = 0; $failed = @(); $created = @()

# ---- FTP helpers (via Python to handle server's PASV/EPSV quirks) ----
function Ensure-FtpDir($remotePath) {
    if ($script:created -contains $remotePath) { return }
    python -c "
import ftplib, re
def mkpasv(ftp):
    resp = ftp.sendcmd('PASV')
    m = re.search(r'\|\|\|(\d+)\|', resp)
    if m: return ftp.sock.getpeername()[0], int(m.group(1))
    return ftplib.parse227(resp)
ftp = ftplib.FTP(); ftp.connect('$ftpHost'.replace('ftp://',''), 21, timeout=30)
ftp.login('$ftpUser', '$ftpPass'); ftp.set_pasv(True)
ftp.makepasv = lambda: mkpasv(ftp)
try: ftp.mkd('$remotePath')
except: pass
ftp.quit()
" 2>$null
    $script:created += $remotePath
}

function Upload-File($localPath, $remotePath) {
    # Ensure parent directories exist
    $parts = $remotePath.TrimStart('/').Split('/')
    for ($i = 1; $i -lt $parts.Count; $i++) {
        $dir = "/" + ($parts[0..($i-1)] -join "/")
        Ensure-FtpDir $dir
    }

    $remoteDir  = "/" + ($parts[0..($parts.Count-2)] -join "/")
    $remoteFile = $parts[-1]
    $localPathEsc = $localPath.Replace('\','\\')

    $result = python -c "
import ftplib, re, sys
def mkpasv(ftp):
    resp = ftp.sendcmd('PASV')
    m = re.search(r'\|\|\|(\d+)\|', resp)
    if m: return ftp.sock.getpeername()[0], int(m.group(1))
    return ftplib.parse227(resp)
try:
    ftp = ftplib.FTP(); ftp.connect('$ftpHost'.replace('ftp://',''), 21, timeout=60)
    ftp.login('$ftpUser', '$ftpPass'); ftp.set_pasv(True)
    ftp.makepasv = lambda: mkpasv(ftp)
    ftp.cwd('$remoteDir')
    with open('$localPathEsc', 'rb') as f:
        ftp.storbinary('STOR $remoteFile', f)
    ftp.quit()
    print('OK')
except Exception as e:
    print('ERR:' + str(e))
" 2>&1

    if ($result -match '^OK') {
        $kb = [math]::Round((Get-Item $localPath).Length/1024, 1)
        Write-Host "  [OK] $remotePath ($kb KB)" -ForegroundColor Cyan
        $script:uploaded++
    } else {
        Write-Host "  [FAIL] $remotePath - $result" -ForegroundColor Red
        $script:failed += $remotePath
    }
}

# ---- Determine which files to upload ----
Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "  Deploy to InfinityFree" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow

if ($Files.Count -gt 0) {
    # Specific files mode
    Write-Host "Mode: Specific files ($($Files.Count))" -ForegroundColor White
    foreach ($relFile in $Files) {
        $relFile = $relFile.Replace('/', '\')
        $fullPath = Join-Path $localRoot $relFile
        if (Test-Path $fullPath) {
            $remotePath = "$remoteRoot/$($relFile.Replace('\','/'))"
            Upload-File $fullPath $remotePath
        } else {
            Write-Host "  [SKIP] Not found: $relFile" -ForegroundColor Yellow
        }
    }
} else {
    # Auto-detect changed files
    if ($All) {
        Write-Host "Mode: FULL re-deploy" -ForegroundColor White
        $cutoff = (Get-Date).AddYears(-10)
    } else {
        Write-Host "Mode: Files changed in last $Minutes minutes" -ForegroundColor White
        $cutoff = (Get-Date).AddMinutes(-$Minutes)
    }
    
    $changedFiles = Get-ChildItem -Path $localRoot -Recurse -File |
        Where-Object { $_.LastWriteTime -gt $cutoff } |
        Where-Object {
            $rel = $_.FullName.Substring($localRoot.Length + 1)
            $topDir = $rel.Split('\')[0]
            $skip = $false
            foreach ($ed in $excludeDirs) { if ($topDir -eq $ed) { $skip = $true; break } }
            if (-not $skip) { foreach ($ef in $excludeFiles) { if ($_.Name -eq $ef) { $skip = $true; break } } }
            -not $skip
        }
    
    $count = ($changedFiles | Measure-Object).Count
    if ($count -eq 0) {
        Write-Host ""
        Write-Host "No changed files found in the last $Minutes minutes." -ForegroundColor Yellow
        Write-Host "Try: .\deploy.ps1 -Minutes 60" -ForegroundColor Gray
        Write-Host "Or:  .\deploy.ps1 -Files 'views/customer/products.php'" -ForegroundColor Gray
        exit
    }
    
    Write-Host "Found $count changed file(s):" -ForegroundColor White
    Write-Host ""
    
    foreach ($f in $changedFiles) {
        $rel = $f.FullName.Substring($localRoot.Length + 1).Replace('\', '/')
        $remotePath = "$remoteRoot/$rel"
        Upload-File $f.FullName $remotePath
    }
}

# ---- Summary ----
Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "  Uploaded: $uploaded file(s)" -ForegroundColor Green
if ($failed.Count -gt 0) {
    Write-Host "  Failed:   $($failed.Count) file(s)" -ForegroundColor Red
    $failed | ForEach-Object { Write-Host "    - $_" -ForegroundColor Red }
}
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""
Write-Host 'Live: https://southdev-home-depot.infinityfreeapp.com' -ForegroundColor Cyan
Write-Host ""
