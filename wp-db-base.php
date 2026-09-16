<?php
// =============================================
// DATABASE MANAGER — CRUD + Pagination Nav
// Auto Detect db.php / config.php / .env / wp-config
// =============================================

ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '0');

// =============================================
// AUTO DETECT CREDENTIALS
// =============================================

function detect_from_dbphp() {
    $db_file = __DIR__ . '/db.php';
    if (!file_exists($db_file)) {
        $db_file = dirname(__DIR__) . '/db.php';
        if (!file_exists($db_file)) return null;
    }
    
    $content = file_get_contents($db_file);
    $creds = [];
    
    if (preg_match('/\$host\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) $creds['DB_HOST'] = $m[1];
    if (preg_match('/\$db\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) $creds['DB_NAME'] = $m[1];
    if (preg_match('/\$user\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) $creds['DB_USER'] = $m[1];
    if (preg_match('/\$pass\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) $creds['DB_PASSWORD'] = $m[1];
    
    return !empty($creds) ? $creds : null;
}

function get_env_value($key, $default = null) {
    $env_file = __DIR__ . '/.env';
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue;
            if (strpos($line, $key . '=') === 0) {
                $value = explode('=', $line, 2)[1] ?? '';
                $value = trim($value);
                if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value)-1) {
                    $value = substr($value, 1, -1);
                }
                return $value;
            }
        }
    }
    return $default;
}

function detect_from_wpconfig() {
    $wpconfig = __DIR__ . '/wp-config.php';
    if (!file_exists($wpconfig)) {
        $wpconfig = dirname(__DIR__) . '/wp-config.php';
        if (!file_exists($wpconfig)) return null;
    }
    
    $content = file_get_contents($wpconfig);
    $creds = [];
    
    if (preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) $creds['DB_NAME'] = $m[1];
    if (preg_match("/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) $creds['DB_USER'] = $m[1];
    if (preg_match("/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) $creds['DB_PASSWORD'] = $m[1];
    if (preg_match("/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) {
        $creds['DB_HOST'] = explode(':', $m[1])[0];
    }
    
    return !empty($creds) ? $creds : null;
}

function detect_from_config() {
    $config_file = __DIR__ . '/config.php';
    if (!file_exists($config_file)) {
        $config_file = dirname(__DIR__) . '/config.php';
        if (!file_exists($config_file)) return null;
    }
    
    $content = file_get_contents($config_file);
    $creds = [];
    
    if (preg_match("/define\s*\(\s*['\"]DB_SERVER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) $creds['DB_HOST'] = $m[1];
    if (preg_match("/define\s*\(\s*['\"]DB_USERNAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) $creds['DB_USER'] = $m[1];
    if (preg_match("/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) $creds['DB_PASSWORD'] = $m[1];
    if (preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $content, $m)) $creds['DB_NAME'] = $m[1];
    
    return !empty($creds) ? $creds : null;
}

$creds = detect_from_dbphp();
$source = 'db.php';

if (!$creds) {
    $creds = detect_from_config();
    $source = 'config.php';
}

if (!$creds) {
    $host = get_env_value('DB_HOST', 'localhost');
    $user = get_env_value('DB_USERNAME', '');
    $pass = get_env_value('DB_PASSWORD', '');
    $dbname = get_env_value('DB_DATABASE', '');
    if (!empty($user) && !empty($dbname)) {
        $creds = ['DB_HOST' => $host, 'DB_USER' => $user, 'DB_PASSWORD' => $pass, 'DB_NAME' => $dbname];
        $source = '.env';
    }
}

if (!$creds) {
    $wp_creds = detect_from_wpconfig();
    if ($wp_creds) {
        $creds = $wp_creds;
        $source = 'wp-config.php';
    }
}

if (!$creds || empty($creds['DB_NAME']) || empty($creds['DB_USER'])) {
    die("❌ Could not detect credentials.\n");
}

$host = $creds['DB_HOST'] ?? 'localhost';
$user = $creds['DB_USER'] ?? '';
$pass = $creds['DB_PASSWORD'] ?? '';
$dbname = $creds['DB_NAME'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ DB Connection failed: " . $e->getMessage());
}

// =============================================
// FUNCTIONS
// =============================================
function get_primary_key($pdo, $table) {
    $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    $row = $stmt->fetch();
    return $row ? $row['Column_name'] : null;
}

function get_columns($pdo, $table) {
    $stmt = $pdo->query("DESCRIBE `$table`");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_table_data($pdo, $table, $limit = 50, $offset = 0) {
    $stmt = $pdo->query("SELECT * FROM `$table` LIMIT $limit OFFSET $offset");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_total_rows($pdo, $table) {
    return $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

// =============================================
// PROCESS ACTIONS
// =============================================
$selected_db = isset($_GET['db']) ? $_GET['db'] : '';
$selected_table = isset($_GET['table']) ? $_GET['table'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

if ($selected_db) {
    $pdo->exec("USE `$selected_db`");
}

// === ADD ===
if ($action === 'add' && isset($_POST['add_submit'])) {
    $columns = get_columns($pdo, $selected_table);
    $fields = [];
    $values = [];
    foreach ($columns as $col) {
        if ($col['Field'] === 'id' && $col['Extra'] === 'auto_increment') continue;
        if (isset($_POST['field_' . $col['Field']])) {
            $fields[] = "`" . $col['Field'] . "`";
            $values[] = $_POST['field_' . $col['Field']];
        }
    }
    if (!empty($fields)) {
        $sql = "INSERT INTO `$selected_table` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', array_fill(0, count($values), '?')) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        header("Location: ?db=" . urlencode($selected_db) . "&table=" . urlencode($selected_table));
        exit;
    }
}

// === DELETE ===
if ($action === 'delete' && isset($_GET['id'])) {
    $pk = get_primary_key($pdo, $selected_table);
    $stmt = $pdo->prepare("DELETE FROM `$selected_table` WHERE `$pk` = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: ?db=" . urlencode($selected_db) . "&table=" . urlencode($selected_table));
    exit;
}

// === DUPLICATE ===
if ($action === 'duplicate' && isset($_GET['id'])) {
    $pk = get_primary_key($pdo, $selected_table);
    $stmt = $pdo->query("SELECT * FROM `$selected_table` WHERE `$pk` = " . (int)$_GET['id']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        unset($row[$pk]);
        $fields = [];
        $values = [];
        foreach ($row as $key => $val) {
            $fields[] = "`$key`";
            $values[] = $val;
        }
        $sql = "INSERT INTO `$selected_table` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', array_fill(0, count($values), '?')) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        header("Location: ?db=" . urlencode($selected_db) . "&table=" . urlencode($selected_table));
        exit;
    }
}

// === EDIT ===
if ($action === 'edit' && isset($_POST['edit_submit'])) {
    $pk = get_primary_key($pdo, $selected_table);
    $id = $_POST['edit_id'] ?? 0;
    $columns = get_columns($pdo, $selected_table);
    $sets = [];
    $values = [];
    foreach ($columns as $col) {
        if ($col['Field'] === $pk) continue;
        if (isset($_POST['field_' . $col['Field']])) {
            $sets[] = "`" . $col['Field'] . "` = ?";
            $values[] = $_POST['field_' . $col['Field']];
        }
    }
    if (!empty($sets)) {
        $values[] = $id;
        $sql = "UPDATE `$selected_table` SET " . implode(', ', $sets) . " WHERE `$pk` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        header("Location: ?db=" . urlencode($selected_db) . "&table=" . urlencode($selected_table));
        exit;
    }
}

// =============================================
// GUI
// =============================================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Database Manager — CRUD</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:#0a0e17;color:#e8e8e8;font-family:'Inter',sans-serif;padding:16px;min-height:100vh}
        .container{max-width:1200px;margin:0 auto;background:#111820;border:1px solid #1f2a3a;border-radius:16px;padding:20px}
        .header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #1f2a3a;padding-bottom:12px;margin-bottom:16px;flex-wrap:wrap;gap:8px}
        .header h1{color:#fbbf24;font-size:20px;font-weight:800}
        .header .info{font-size:12px;color:#94a3b8}
        .header .info span{color:#e8e8e8}
        .badge{background:#1a2332;border:1px solid #2a3a52;border-radius:40px;padding:2px 10px;font-size:10px;color:#94a3b8;display:inline-block}
        .db-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:6px;margin:8px 0 16px}
        .db-link{display:block;background:#0f1824;border:1px solid #1f2a3a;border-radius:6px;padding:8px 12px;text-decoration:none;color:#c8d0dc;font-size:12px;text-align:center;transition:0.15s}
        .db-link:hover,.db-link.active{background:#1a2332;border-color:#fbbf24;color:#fbbf24}
        .table-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:6px;margin:8px 0}
        .table-link{background:#0f1824;border:1px solid #1a2332;border-radius:6px;padding:6px 10px;text-decoration:none;color:#c8d0dc;font-size:12px;text-align:center;transition:0.15s;display:block}
        .table-link:hover,.table-link.active{background:#1a2332;border-color:#fbbf24;color:#fbbf24}
        .table-link .size{color:#475569;font-size:10px;display:block}
        .toolbar{display:flex;gap:8px;margin:12px 0;flex-wrap:wrap}
        .btn{background:#fbbf24;color:#0a0e17;padding:6px 14px;border:none;border-radius:4px;font-weight:600;font-size:12px;cursor:pointer;transition:0.15s;font-family:monospace;text-decoration:none;display:inline-block}
        .btn:hover{background:#f59e0b}
        .btn-secondary{background:#1a2332;color:#e8e8e8;border:1px solid #2a3a52}
        .btn-secondary:hover{background:#2a3a52}
        .btn-danger{background:#dc2626;color:#fff}
        .btn-danger:hover{background:#b91c1c}
        .btn-success{background:#16a34a;color:#fff}
        .btn-success:hover{background:#15803d}
        .btn-warning{background:#f59e0b;color:#0a0e17}
        .btn-warning:hover{background:#d97706}
        .table-responsive{overflow-x:auto;margin:8px 0}
        table{width:100%;border-collapse:collapse;font-size:12px;background:#0a0e17;border-radius:4px;overflow:hidden}
        th{background:#1a2332;color:#fbbf24;padding:6px 8px;text-align:left;border:1px solid #1f2a3a;white-space:nowrap;font-weight:600}
        td{color:#c8d0dc;padding:6px 8px;border:1px solid #1f2a3a;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        tr:nth-child(even){background:#0f1824}
        .actions{display:flex;gap:4px;flex-wrap:wrap}
        .actions a{text-decoration:none;font-size:11px;padding:2px 8px;border-radius:3px}
        .actions .edit{background:#2563eb;color:#fff}
        .actions .edit:hover{background:#1d4ed8}
        .actions .dup{background:#f59e0b;color:#0a0e17}
        .actions .dup:hover{background:#d97706}
        .actions .del{background:#dc2626;color:#fff}
        .actions .del:hover{background:#b91c1c}
        .form-container{background:#0f1824;border:1px solid #1f2a3a;border-radius:6px;padding:16px;margin:12px 0}
        .form-container input,.form-container textarea{background:#0a0e17;color:#e8e8e8;border:1px solid #1f2a3a;padding:6px 10px;border-radius:4px;font-family:monospace;font-size:12px;width:100%;margin:4px 0}
        .form-container input:focus{border-color:#fbbf24;outline:none}
        .form-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px}
        .form-grid label{font-size:11px;color:#94a3b8}
        .pagination{display:flex;gap:4px;margin:10px 0;flex-wrap:wrap;align-items:center}
        .pagination a{padding:4px 10px;background:#0f1824;border:1px solid #1f2a3a;border-radius:4px;text-decoration:none;color:#c8d0dc;font-size:12px;transition:0.15s}
        .pagination a:hover{background:#1a2332;border-color:#fbbf24;color:#fbbf24}
        .pagination .current{background:#fbbf24;color:#0a0e17;padding:4px 10px;border-radius:4px;font-weight:700;font-size:12px}
        .pagination .dots{color:#475569;padding:4px 6px;font-size:12px}
        .stats-bar{font-size:12px;color:#94a3b8;margin:4px 0}
        .stats-bar strong{color:#fbbf24}
        .empty{color:#475569;padding:20px;text-align:center}
        @media(max-width:600px){.form-grid{grid-template-columns:1fr}.table-grid{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📊 Database Manager — CRUD</h1>
        <div class="info">
            📁 <span><?php echo htmlspecialchars($dbname); ?></span>
            🔌 <span><?php echo htmlspecialchars($host); ?></span>
            📄 <span class="badge"><?php echo htmlspecialchars($source); ?></span>
        </div>
    </div>

    <?php
    $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    $selected_db = isset($_GET['db']) ? $_GET['db'] : '';
    ?>

    <div class="db-grid">
        <?php foreach ($dbs as $db): ?>
            <a href="?db=<?php echo urlencode($db); ?>" class="db-link <?php echo $selected_db == $db ? 'active' : ''; ?>">
                📁 <?php echo htmlspecialchars($db); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($selected_db): ?>
        <?php
        $pdo->exec("USE `$selected_db`");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        ?>
        <div style="display:flex;flex-wrap:wrap;gap:4px;margin:8px 0">
            <?php foreach ($tables as $t): ?>
                <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($t); ?>" class="table-link <?php echo $selected_table == $t ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($t); ?>
                    <span class="size"><?php echo number_format(get_total_rows($pdo, $t)); ?> rows</span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($selected_table): ?>
            <?php
            $pk = get_primary_key($pdo, $selected_table);
            $columns = get_columns($pdo, $selected_table);
            $total_rows = get_total_rows($pdo, $selected_table);
            $data = get_table_data($pdo, $selected_table, $limit, $offset);
            $total_pages = ceil($total_rows / $limit);
            ?>

            <div class="toolbar">
                <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>&action=add" class="btn btn-success">➕ Add New</a>
                <a href="?db=<?php echo urlencode($selected_db); ?>" class="btn btn-secondary">⬅ Back</a>
                <span class="stats-bar">📊 <strong><?php echo number_format($total_rows); ?></strong> rows | Page <strong><?php echo $page; ?></strong> of <strong><?php echo $total_pages; ?></strong></span>
            </div>

            <?php if ($action === 'add'): ?>
                <div class="form-container">
                    <h3 style="color:#fbbf24;font-size:14px;margin-bottom:8px;">➕ Add New Record — <?php echo htmlspecialchars($selected_table); ?></h3>
                    <form method="POST">
                        <div class="form-grid">
                            <?php foreach ($columns as $col): ?>
                                <?php if ($col['Field'] === 'id' && $col['Extra'] === 'auto_increment') continue; ?>
                                <div>
                                    <label><?php echo htmlspecialchars($col['Field']); ?></label>
                                    <input type="text" name="field_<?php echo $col['Field']; ?>" placeholder="<?php echo htmlspecialchars($col['Field']); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top:8px;">
                            <button type="submit" name="add_submit" class="btn btn-success">💾 Save</button>
                            <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($action === 'edit' && isset($_GET['id'])): ?>
                <?php
                $id = $_GET['id'];
                $stmt = $pdo->prepare("SELECT * FROM `$selected_table` WHERE `$pk` = ?");
                $stmt->execute([$id]);
                $edit_row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($edit_row):
                ?>
                <div class="form-container">
                    <h3 style="color:#fbbf24;font-size:14px;margin-bottom:8px;">✏️ Edit Record — <?php echo htmlspecialchars($selected_table); ?></h3>
                    <form method="POST">
                        <input type="hidden" name="edit_id" value="<?php echo $id; ?>">
                        <div class="form-grid">
                            <?php foreach ($columns as $col): ?>
                                <?php if ($col['Field'] === $pk) continue; ?>
                                <div>
                                    <label><?php echo htmlspecialchars($col['Field']); ?></label>
                                    <input type="text" name="field_<?php echo $col['Field']; ?>" value="<?php echo htmlspecialchars($edit_row[$col['Field']] ?? ''); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top:8px;">
                            <button type="submit" name="edit_submit" class="btn btn-warning">💾 Update</button>
                            <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- DATA TABLE -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <th><?php echo htmlspecialchars($col['Field']); ?></th>
                            <?php endforeach; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="<?php echo count($columns) + 1; ?>" class="empty">📭 No data found</td></tr>
                        <?php else: ?>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <?php foreach ($columns as $col): ?>
                                        <td><?php echo htmlspecialchars(substr($row[$col['Field']] ?? '', 0, 50)); ?></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <div class="actions">
                                            <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>&action=edit&id=<?php echo $row[$pk]; ?>" class="edit">Edit</a>
                                            <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>&action=duplicate&id=<?php echo $row[$pk]; ?>" class="dup" onclick="return confirm('Duplicate this record?')">Dup</a>
                                            <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>&action=delete&id=<?php echo $row[$pk]; ?>" class="del" onclick="return confirm('Delete this record?')">Del</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION NAVIGATION -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>&page=1">« First</a>
                        <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>&page=<?php echo $page - 1; ?>">‹ Prev</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1) {
                        echo '<a href="?db=' . urlencode($selected_db) . '&table=' . urlencode($selected_table) . '&page=1">1</a>';
                        if ($start > 2) echo '<span class="dots">…</span>';
                    }
                    
                    for ($i = $start; $i <= $end; $i++) {
                        if ($i == $page) {
                            echo '<span class="current">' . $i . '</span>';
                        } else {
                            echo '<a href="?db=' . urlencode($selected_db) . '&table=' . urlencode($selected_table) . '&page=' . $i . '">' . $i . '</a>';
                        }
                    }
                    
                    if ($end < $total_pages) {
                        if ($end < $total_pages - 1) echo '<span class="dots">…</span>';
                        echo '<a href="?db=' . urlencode($selected_db) . '&table=' . urlencode($selected_table) . '&page=' . $total_pages . '">' . $total_pages . '</a>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>&page=<?php echo $page + 1; ?>">Next ›</a>
                        <a href="?db=<?php echo urlencode($selected_db); ?>&table=<?php echo urlencode($selected_table); ?>&page=<?php echo $total_pages; ?>">Last »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty">📂 Select a table from above</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty">📂 Select a database from above</div>
    <?php endif; ?>
</div>
</body>
</html>