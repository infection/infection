$client = New-Object System.Net.WebClient
$xdebugUrl = "https://xdebug.org/files/php_xdebug-$env:xdebug_ver-nts-x86_64.dll"
$phpDir = (Get-Item (Get-Command php).Source).Directory.FullName
$xdebugPath = Join-Path $phpDir ext\xdebug.dll
$client.DownloadFile($xdebugUrl, $xdebugPath)
$phpIniPath = Join-Path $phpDir php.ini
Add-Content $phpIniPath @"
zend_extension=$xdebugPath
"@
