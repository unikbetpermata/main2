<?php
// Tiny File Manager - single file
$root = realpath('.');
$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$path = realpath($root . '/' . $dir);
if (!$path || strpos($path, $root) !== 0) { $path = $root; $dir = ''; }

function h($s){ return htmlspecialchars($s, ENT_QUOTES); }

// Download single file
if (isset($_GET['download'])) {
    $file = realpath($path . '/' . $_GET['download']);
    if ($file && strpos($file, $root) === 0 && is_file($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

// Delete file or folder
if (isset($_GET['delete'])) {
    $target = realpath($path . '/' . $_GET['delete']);
    if ($target && strpos($target, $root) === 0 && $target !== $root) {
        function fm_rrmdir($d) {
            foreach (scandir($d) as $f) {
                if ($f === '.' || $f === '..') continue;
                $fp = "$d/$f";
                is_dir($fp) ? fm_rrmdir($fp) : unlink($fp);
            }
            rmdir($d);
        }
        if (is_dir($target)) {
            fm_rrmdir($target);
            $msg = 'Folder deleted.';
        } elseif (is_file($target)) {
            unlink($target);
            $msg = 'File deleted.';
        }
    } else {
        $msg = 'Cannot delete that.';
    }
    header('Location: ?dir=' . urlencode($dir) . '&msg=' . urlencode($msg));
    exit;
}
// Build zip in background, write status, keep file for fetch step
if (isset($_GET['zipbuild'])) {
    @set_time_limit(0);
    @ini_set('max_execution_time', 0);
    ignore_user_abort(true);

    $folder = $_GET['zipbuild'] === '.' ? $path : realpath($path . '/' . $_GET['zipbuild']);
    $token = isset($_GET['token']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['token']) : uniqid('t');
    $statusFile = sys_get_temp_dir() . '/fm_status_' . $token . '.json';
    $tmpZip = sys_get_temp_dir() . '/fm_zip_' . $token . '.zip';

    header('Content-Type: application/json');

    if ($folder && strpos($folder, $root) === 0 && is_dir($folder)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        $allFiles = [];
        foreach ($iterator as $f) { if (!$f->isDir()) $allFiles[] = $f->getPathname(); }
        $total = count($allFiles);
        file_put_contents($statusFile, json_encode(['done'=>0,'total'=>$total,'status'=>'zipping']));

        $zip = new ZipArchive();
        if ($zip->open($tmpZip, ZipArchive::CREATE) === TRUE) {
            $done = 0;
            foreach ($allFiles as $fp) {
                $localPath = substr($fp, strlen($folder) + 1);
                $zip->addFile($fp, $localPath);
                $done++;
                if ($done % 5 === 0 || $done === $total) {
                    file_put_contents($statusFile, json_encode(['done'=>$done,'total'=>$total,'status'=>'zipping']));
                }
            }
            $zip->close();
            file_put_contents($statusFile, json_encode(['done'=>$total,'total'=>$total,'status'=>'ready','zipname'=>basename($folder).'.zip']));
            echo json_encode(['ok'=>true]);
        } else {
            file_put_contents($statusFile, json_encode(['done'=>0,'total'=>$total,'status'=>'error']));
            echo json_encode(['ok'=>false]);
        }
    } else {
        file_put_contents($statusFile, json_encode(['done'=>0,'total'=>0,'status'=>'error']));
        echo json_encode(['ok'=>false]);
    }
    exit;
}

// Serve the finished zip (real download step, run after status=ready)
if (isset($_GET['zipfetch'])) {
    $token = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['zipfetch']);
    $statusFile = sys_get_temp_dir() . '/fm_status_' . $token . '.json';
    $tmpZip = sys_get_temp_dir() . '/fm_zip_' . $token . '.zip';
    if (file_exists($tmpZip)) {
        $info = json_decode(@file_get_contents($statusFile), true);
        $zipName = $info['zipname'] ?? 'download.zip';
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($tmpZip));
        readfile($tmpZip);
        unlink($tmpZip);
        @unlink($statusFile);
    }
    exit;
}

// Progress polling endpoint
if (isset($_GET['zipstatus'])) {
    $token = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['zipstatus']);
    $statusFile = sys_get_temp_dir() . '/fm_status_' . $token . '.json';
    header('Content-Type: application/json');
    echo file_exists($statusFile) ? file_get_contents($statusFile) : json_encode(['done'=>0,'total'=>0,'status'=>'starting']);
    exit;
}

// View / Edit
$editFile = null; $editContent = ''; $msg = isset($_GET['msg']) ? $_GET['msg'] : '';
if (isset($_GET['edit'])) {
    $editFile = realpath($path . '/' . $_GET['edit']);
    if ($editFile && strpos($editFile, $root) === 0 && is_file($editFile)) {
        $editContent = file_get_contents($editFile);
    } else { $editFile = null; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']) && isset($_POST['filename'])) {
    $target = realpath($path . '/' . $_POST['filename']);
    if ($target && strpos($target, $root) === 0 && is_file($target) && is_writable($target)) {
        file_put_contents($target, $_POST['content']);
        $msg = 'Saved.';
        $editFile = $target;
        $editContent = $_POST['content'];
    } else { $msg = 'Cannot save (not writable).'; }
}

$relDir = trim(str_replace($root, '', $path), '/\\');
$parent = dirname($relDir);
if ($parent === '.' || $parent === '\\' ) $parent = '';

$items = @scandir($path) ?: [];
$items = array_filter($items, fn($i) => $i !== '.' && $i !== '..');
usort($items, function($a,$b) use ($path){
    $ad = is_dir("$path/$a"); $bd = is_dir("$path/$b");
    if ($ad != $bd) return $bd - $ad;
    return strcasecmp($a,$b);
});
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>File Manager</title>
<style>
body{font-family:monospace;background:#1e1e1e;color:#ddd;margin:0;padding:20px}
a{color:#6cf;text-decoration:none} a:hover{text-decoration:underline}
table{width:100%;border-collapse:collapse} td,th{padding:4px 8px;text-align:left;border-bottom:1px solid #333}
.dir{color:#f5c542;font-weight:bold}
textarea{width:100%;height:400px;background:#111;color:#0f0;font-family:monospace;font-size:13px}
.bar{margin-bottom:10px} .perm{color:#888;font-size:12px} .btn{background:#333;color:#ddd;border:1px solid #555;padding:2px 6px;cursor:pointer}
input[type=text]{background:#111;color:#ddd;border:1px solid #555;width:100%}
</style></head><body>
<h2>📁 /<?= h($relDir) ?></h2>
<?php if ($msg && !$editFile): ?><p style="color:#8f8"><?= h($msg) ?></p><?php endif; ?>

<?php if ($editFile): ?>
  <div class="bar"><a href="?dir=<?= urlencode($relDir) ?>">&laquo; back to folder</a></div>
  <p><?= h($msg) ?></p>
  <form method="post">
    <input type="hidden" name="filename" value="<?= h(basename($editFile)) ?>">
    <p>Editing: <b><?= h(basename($editFile)) ?></b> (<?= h(substr(sprintf('%o', fileperms($editFile)), -4)) ?>)</p>
    <textarea name="content"><?= h($editContent) ?></textarea><br>
    <button class="btn" type="submit" name="save" value="1">Save</button>
  </form>

<?php else: ?>
  <div class="bar">
    <?php if ($relDir !== ''): ?><a href="?dir=<?= urlencode($parent) ?>">⬅ Back</a> | <?php endif; ?>
    <a href="#" onclick="startZip('.', '<?= h($relDir) ?>'); return false;">⬇ Download this folder as ZIP</a>
  </div>
  <div id="zipProgressBox" style="display:none;margin-bottom:10px;padding:10px;background:#111;border:1px solid #444;">
    <div id="zipLabel">Preparing...</div>
    <div style="background:#333;height:16px;width:100%;margin-top:6px;">
      <div id="zipBar" style="background:#4caf50;height:16px;width:0%;"></div>
    </div>
  </div>
  <table>
  <tr><th>Name</th><th>Size</th><th>Perms</th><th>Modified</th><th>Actions</th></tr>
  <?php foreach ($items as $item):
      $full = "$path/$item";
      $isDir = is_dir($full);
      $relItem = ($relDir === '' ? $item : "$relDir/$item");
      $perm = substr(sprintf('%o', fileperms($full)), -4);
  ?>
    <tr>
      <td>
        <?php if ($isDir): ?>
          📂 <a class="dir" href="?dir=<?= urlencode($relItem) ?>"><?= h($item) ?>/</a>
        <?php else: ?>
          📄 <?= h($item) ?>
        <?php endif; ?>
      </td>
      <td><?= $isDir ? '-' : number_format(filesize($full)) . ' B' ?></td>
      <td class="perm"><?= $perm ?></td>
      <td class="perm"><?= date('Y-m-d H:i', filemtime($full)) ?></td>
      <td>
        <?php if (!$isDir): ?>
          <a href="?dir=<?= urlencode($relDir) ?>&edit=<?= urlencode($item) ?>">Edit</a> |
          <a href="?dir=<?= urlencode($relDir) ?>&download=<?= urlencode($item) ?>">Download</a> |
          <a href="#" onclick="return confirmDelete('<?= h(addslashes($item)) ?>', '<?= h($relDir) ?>');">Delete</a>
        <?php else: ?>
          <a href="#" onclick="startZip('<?= h($item) ?>', '<?= h($relDir) ?>'); return false;">Download ZIP</a> |
          <a href="#" onclick="return confirmDelete('<?= h(addslashes($item)) ?>', '<?= h($relDir) ?>');">Delete</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </table>
<?php endif; ?>

<script>
function confirmDelete(item, dir) {
  if (confirm('Delete "' + item + '"? This cannot be undone.')) {
    window.location = '?dir=' + encodeURIComponent(dir) + '&delete=' + encodeURIComponent(item);
  }
  return false;
}
function startZip(item, dir) {
  var token = 'z' + Date.now();
  var box = document.getElementById('zipProgressBox');
  var bar = document.getElementById('zipBar');
  var label = document.getElementById('zipLabel');
  box.style.display = 'block';
  label.textContent = 'Scanning files...';
  bar.style.width = '0%';

  // Kick off background build (this request itself may take a while, that's fine, we just don't wait on it to update UI)
  fetch('?dir=' + encodeURIComponent(dir) + '&zipbuild=' + encodeURIComponent(item) + '&token=' + token)
    .catch(function(){});

  var poll = setInterval(function() {
    fetch('?zipstatus=' + token)
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (d.total > 0) {
          var pct = Math.round((d.done / d.total) * 100);
          bar.style.width = pct + '%';
          label.textContent = 'Zipping: ' + d.done + ' / ' + d.total + ' files (' + pct + '%)';
        } else {
          label.textContent = 'Scanning files...';
        }
        if (d.status === 'ready') {
          clearInterval(poll);
          bar.style.width = '100%';
          label.innerHTML = 'Done! <a href="?zipfetch=' + token + '" style="color:#8f8;text-decoration:underline;">Click here to download</a> (starting automatically...)';
          window.location = '?zipfetch=' + token;
        } else if (d.status === 'error') {
          label.textContent = 'Error creating zip.';
          clearInterval(poll);
        }
      })
      .catch(function(){});
  }, 800);
}
</script>
</body></html>
