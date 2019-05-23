<?php

/*

accepts a get parameter lang and returns on success a json in the form:
{
	"status":"ok",
	"motivations":[
	    {"name":"NAME1","value":(2=yellow, 3=red),"label":"LABEL1","descr":"DESCR1"},
	    {"name":"NAME2","value":(2=yellow, 3=red),"label":"LABEL2","descr":"DESCR2"},
	    ...
	]
}

*/

require_once 'demetra.php';

try {
	$lang=$_GET['lang'];
	if( !$lang || $lang!='it' && $lang!='en' ) $lang='it';
	$conn=demetra_connect();
	$r=demetra_query($conn,'select name, value, label_'.$lang.' as label, descr_'.$lang.' as descr from motivation');
	$result=array();
	while( $row=mysqli_fetch_assoc($r) ) {
		$result[]=array('name'=>$row['name'],'value'=>$row['value'],'label'=>$row['label'],descr=>$row['descr']);
	}
	mysqli_free_result($r);
	
	echo json_encode(array('status'=>'ok','motivations'=>$result));
	
} catch( Exception $e ) {
	echo json_encode(array('status'=>'error','message'=>$e->getMessage()));
}


?>
