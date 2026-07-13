# Pack project files for first-time Hostinger upload (File Manager zip extract)
$localRoot = "C:\xampp\htdocs\southdev-home-depot"
$outZip    = Join-Path $localRoot "southdev-hostinger.zip"

$excludeDirs  = @('.git', 'repo.git', 'docs', 'database', '.venv', 'storage\mails', 'tools')
$excludeFiles = @(
    'southdev-hostinger.zip', 'southdev-deploy.zip',
    'deploy.ps1', 'deploy-hostinger.ps1', 'deploy-git-status.ps1', 'deploy-full-ftp.py',
    'ftp_upload.ps1', 'ftp_curl_upload.ps1', 'pack-for-hostinger.ps1',
    'test.html', 'test.php', 'test_deploy.php',
    'tmp_index_html.html', 'tmp_products_html.html', 'tmp_products_html_after.css.html',
    'flowcharts.html', '.env', '.env.deploy', '.env.production', '.env.deploy.example',
    '.env.hostinger.example', '.env.example', '.gitignore',
    'CONFIGURATION_GUIDE.md', 'DEMO_ACCOUNTS.md', 'DEPLOYMENT_CHECKLIST.md',
    'DEPLOYMENT_GUIDE.md', 'EWALLET_API_SETUP.md', 'PAYMONGO_SETUP.md', 'README.md'
)

if (Test-Path $outZip) { Remove-Item $outZip -Force }

Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [System.IO.Compression.ZipFile]::Open($outZip, 'Create')

function Add-ToZip($zip, $localPath, $entryName) {
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $localPath, $entryName.Replace('\','/')) | Out-Null
}

Get-ChildItem -Path $localRoot -Recurse -File | ForEach-Object {
    $rel = $_.FullName.Substring($localRoot.Length + 1)
    $top = $rel.Split('\')[0]

    $skip = $false
    foreach ($d in $excludeDirs) {
        if ($rel -like "$d*") { $skip = $true; break }
    }
    if (-not $skip) {
        foreach ($f in $excludeFiles) {
            if ($_.Name -eq $f) { $skip = $true; break }
        }
    }
    if (-not $skip) {
        Add-ToZip $zip $_.FullName $rel
    }
}

# Copy .env.production into the zip as .env for Hostinger
$envProd = Join-Path $localRoot ".env.production"
if (Test-Path $envProd) {
    Add-ToZip $zip $envProd ".env"
    Write-Host "Included .env (from .env.production)" -ForegroundColor Gray
}

# Include SQL dump for phpMyAdmin import
$sqlDump = Join-Path $localRoot "database\hostinger_import.sql"
if (Test-Path $sqlDump) {
    Add-ToZip $zip $sqlDump "hostinger_import.sql"
    Write-Host "Included hostinger_import.sql for database import" -ForegroundColor Gray
}

$zip.Dispose()
$mb = [math]::Round((Get-Item $outZip).Length / 1MB, 1)
Write-Host ""
Write-Host "Created: $outZip ($mb MB)" -ForegroundColor Green
Write-Host "Upload this zip to Hostinger public_html and Extract." -ForegroundColor Cyan
Write-Host ""
