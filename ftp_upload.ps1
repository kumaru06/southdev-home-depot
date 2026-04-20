# FTP Upload Script for InfinityFree Deployment
# Uploads all project files recursively via FTP

$ftpHost = "ftp://ftpupload.net"
$ftpUser = "if0_41705046"
$ftpPass = "markperez201"
$localRoot = "C:\xampp\htdocs\southdev-home-depot"
$remoteRoot = "/htdocs"

# Folders and files to upload (skip .git, repo.git, southdev-deploy.zip, ftp_upload.ps1, tmp files)
$excludeDirs = @('.git', 'repo.git', 'docs', 'database')
$excludeFiles = @('southdev-deploy.zip', 'ftp_upload.ps1', 'test.html', 'test.php', 'test_deploy.php', 
                   'tmp_index_html.html', 'tmp_products_html.html', 'tmp_products_html_after.css.html',
                   'flowcharts.html', '.env', '.gitignore', 'composer.lock',
                   'CONFIGURATION_GUIDE.md', 'DEMO_ACCOUNTS.md', 'DEPLOYMENT_CHECKLIST.md', 
                   'DEPLOYMENT_GUIDE.md', 'EWALLET_API_SETUP.md', 'PAYMONGO_SETUP.md', 'README.md')

$totalFiles = 0
$uploadedFiles = 0
$failedFiles = @()

function Create-FtpDirectory($uri) {
    try {
        $req = [System.Net.FtpWebRequest]::Create($uri)
        $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $req.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $req.UseBinary = $true
        $req.UsePassive = $true
        $resp = $req.GetResponse()
        $resp.Close()
        Write-Host "  [DIR] Created: $uri" -ForegroundColor Green
    } catch {
        # Directory may already exist, that's OK
    }
}

function Upload-FtpFile($localPath, $remotePath) {
    $script:totalFiles++
    try {
        $uri = "$ftpHost$remotePath"
        $req = [System.Net.FtpWebRequest]::Create($uri)
        $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $req.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $req.UseBinary = $true
        $req.UsePassive = $true
        $req.Timeout = 60000
        
        $fileContent = [System.IO.File]::ReadAllBytes($localPath)
        $req.ContentLength = $fileContent.Length
        
        $stream = $req.GetRequestStream()
        $stream.Write($fileContent, 0, $fileContent.Length)
        $stream.Close()
        
        $resp = $req.GetResponse()
        $resp.Close()
        
        $script:uploadedFiles++
        $sizeKB = [math]::Round($fileContent.Length / 1024, 1)
        Write-Host "  [OK] $remotePath ($sizeKB KB)" -ForegroundColor Cyan
    } catch {
        $script:failedFiles += $remotePath
        Write-Host "  [FAIL] $remotePath - $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "========================================" -ForegroundColor Yellow
Write-Host " FTP Upload to InfinityFree" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "Host: $ftpHost" 
Write-Host "User: $ftpUser"
Write-Host "Remote: $remoteRoot"
Write-Host ""

# First, count files to upload
$allItems = Get-ChildItem -Path $localRoot -Recurse -File
$filteredItems = $allItems | Where-Object {
    $relPath = $_.FullName.Substring($localRoot.Length + 1)
    $topDir = $relPath.Split('\')[0]
    $skip = $false
    foreach ($ed in $excludeDirs) {
        if ($topDir -eq $ed -or $relPath.StartsWith("$ed\")) { $skip = $true; break }
    }
    if (-not $skip -and $_.Directory.FullName -eq $localRoot) {
        foreach ($ef in $excludeFiles) {
            if ($_.Name -eq $ef) { $skip = $true; break }
        }
    }
    -not $skip
}
$fileCount = ($filteredItems | Measure-Object).Count
Write-Host "Files to upload: $fileCount" -ForegroundColor White
Write-Host ""

# Upload root-level files first
Write-Host "--- Root Files ---" -ForegroundColor Yellow
$rootFiles = Get-ChildItem -Path $localRoot -File | Where-Object {
    $excludeFiles -notcontains $_.Name
}
foreach ($f in $rootFiles) {
    $remotePath = "$remoteRoot/$($f.Name)"
    Upload-FtpFile $f.FullName $remotePath
}

# Get all directories to process (excluding excluded ones)
$dirs = @('assets', 'config', 'controllers', 'includes', 'middleware', 'models', 
          'payment', 'routes', 'storage', 'templates', 'tools', 'vendor', 'views')

foreach ($dir in $dirs) {
    $localDir = Join-Path $localRoot $dir
    if (-not (Test-Path $localDir)) { continue }
    
    Write-Host ""
    Write-Host "--- $dir/ ---" -ForegroundColor Yellow
    
    # Create the top-level remote directory
    Create-FtpDirectory "$ftpHost$remoteRoot/$dir"
    
    # Get all subdirectories and create them
    $subDirs = Get-ChildItem -Path $localDir -Directory -Recurse | Sort-Object FullName
    foreach ($sd in $subDirs) {
        $relPath = $sd.FullName.Substring($localRoot.Length + 1).Replace('\', '/')
        Create-FtpDirectory "$ftpHost$remoteRoot/$relPath"
    }
    
    # Upload all files in this directory tree
    $files = Get-ChildItem -Path $localDir -File -Recurse
    foreach ($f in $files) {
        $relPath = $f.FullName.Substring($localRoot.Length + 1).Replace('\', '/')
        Upload-FtpFile $f.FullName "$remoteRoot/$relPath"
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host " Upload Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "Uploaded: $uploadedFiles / $totalFiles files"
if ($failedFiles.Count -gt 0) {
    Write-Host "Failed: $($failedFiles.Count) files" -ForegroundColor Red
    foreach ($ff in $failedFiles) {
        Write-Host "  - $ff" -ForegroundColor Red
    }
}
Write-Host ""
Write-Host "Visit: https://southdev-home-depot.infinityfreeapp.com" -ForegroundColor Cyan
