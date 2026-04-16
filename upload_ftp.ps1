# FTP一括アップロードスクリプト
$ftpHost   = "ftp://v2009.coreserver.jp"
$ftpUser   = "kohetsuwatanabe@laissezfairetc.v2009.coreserver.jp"
$ftpPass   = "Mihama02#kw#!"
$localDir  = "C:\Users\kohet\OneDrive\レッセ\会計アプリ\deploy"
$remoteDir = "/public_html"

# SSL証明書の検証を無効化（共有サーバー対応）
[System.Net.ServicePointManager]::ServerCertificateValidationCallback = { $true }

$credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)

# FTPディレクトリ作成（存在していてもエラーを無視）
function New-FtpDirectory($url) {
    try {
        $req = [System.Net.FtpWebRequest]::Create($url)
        $req.Credentials = $credentials
        $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $req.EnableSsl = $true
        $req.UseBinary = $true
        $req.UsePassive = $true
        $req.KeepAlive = $false
        $res = $req.GetResponse()
        $res.Close()
    } catch { }
}

# ディレクトリパスを階層ごとに作成
function Ensure-FtpPath($remotePath) {
    $parts = $remotePath.Split("/") | Where-Object { $_ -ne "" }
    $current = $ftpHost
    foreach ($part in $parts) {
        $current = "$current/$part"
        New-FtpDirectory $current
    }
}

# ファイルアップロード
function Send-FtpFile($localFile, $remoteUrl) {
    $req = [System.Net.FtpWebRequest]::Create($remoteUrl)
    $req.Credentials = $credentials
    $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $req.EnableSsl = $true
    $req.UseBinary = $true
    $req.UsePassive = $true
    $req.KeepAlive = $false

    $bytes = [System.IO.File]::ReadAllBytes($localFile)
    $req.ContentLength = $bytes.Length
    $stream = $req.GetRequestStream()
    $stream.Write($bytes, 0, $bytes.Length)
    $stream.Close()

    $res = $req.GetResponse()
    $res.Close()
}

# アップロード実行
$files = Get-ChildItem -Path $localDir -Recurse -File
$total = $files.Count
$i = 0

foreach ($file in $files) {
    $i++
    $rel = $file.FullName.Substring($localDir.Length).Replace("\", "/")
    $remoteFilePath = "$remoteDir$rel"
    $remoteFileUrl  = "$ftpHost$remoteFilePath"

    # 親ディレクトリを作成
    $parentPath = $remoteFilePath.Substring(0, $remoteFilePath.LastIndexOf("/"))
    Ensure-FtpPath $parentPath

    Write-Host "[$i/$total] $rel"
    try {
        Send-FtpFile $file.FullName $remoteFileUrl
    } catch {
        Write-Warning "失敗: $rel - $($_.Exception.Message)"
    }
}

Write-Host ""
Write-Host "完了！ $total ファイルをアップロードしました。"
