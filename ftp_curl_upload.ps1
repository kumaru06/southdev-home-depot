# FTP Upload Script for InfinityFree - uses curl.exe (avoids .NET UTF8 bug)
# Run: .\ftp_curl_upload.ps1

$ftpHost = "ftpupload.net"
$ftpUser = "if0_41705046"
$ftpPass = "markperez201"
$localRoot = "C:\xampp\htdocs\southdev-home-depot"
$remoteRoot = "htdocs"

$excludeDirs  = @('.git', 'repo.git', 'docs', 'database', '.venv', 'vendor')
$excludeFiles = @(
    'southdev-deploy.zip', 'ftp_upload.ps1', 'ftp_curl_upload.ps1',
    'test.html', 'test.php', 'test_deploy.php',
    'tmp_index_html.html', 'tmp_products_html.html', 'tmp_products_html_after.css.html',
    'flowcharts.html', '.env', '.gitignore', 'composer.lock', 'deploy.ps1',
    'CONFIGURATION_GUIDE.md', 'DEMO_ACCOUNTS.md', 'DEPLOYMENT_CHECKLIST.md',
    'DEPLOYMENT_GUIDE.md', 'EWALLET_API_SETUP.md', 'PAYMONGO_SETUP.md', 'README.md',
    'fix_encoding.php'
)

$uploaded = 0
$failed   = 0

function Ftp-Upload {
    param($localFile, $remotePath)

    $url = "ftp://$ftpHost/$remotePath"
    $result = & curl.exe --silent --show-error `
        --user "${ftpUser}:${ftpPass}" `
        --ftp-create-dirs `
        --upload-file $localFile `
        $url 2>&1

    if ($LASTEXITCODE -eq 0) {
        $script:uploaded++
        Write-Host "  [OK] $remotePath" -ForegroundColor Green
    } else {
        $script:failed++
        Write-Host "  [FAIL] $remotePath — $result" -ForegroundColor Red
    }
}

# Walk all files recursively
$allFiles = Get-ChildItem -Path $localRoot -Recurse -File

foreach ($file in $allFiles) {
    # Check excluded dirs
    $relativePath = $file.FullName.Substring($localRoot.Length + 1).Replace('\', '/')
    $parts = $relativePath -split '/'
    $skip = $false

    foreach ($part in $parts[0..($parts.Count - 2)]) {
        if ($excludeDirs -contains $part) { $skip = $true; break }
    }
    if ($excludeFiles -contains $file.Name) { $skip = $true }
    if ($skip) {
        Write-Host "  [SKIP] $relativePath" -ForegroundColor DarkGray
        continue
    }

    $remotePath = "$remoteRoot/$relativePath"
    Ftp-Upload -localFile $file.FullName -remotePath $remotePath
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host " Done! Uploaded: $uploaded  |  Failed: $failed" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow

# Upload .env.production as .env separately
Write-Host ""
Write-Host "Uploading .env.production as .env ..." -ForegroundColor Cyan
Ftp-Upload -localFile "$localRoot\.env.production" -remotePath "$remoteRoot/.env"
Write-Host "Done." -ForegroundColor Green
