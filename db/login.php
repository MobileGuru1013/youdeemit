<?php
require_once 'demetra.php';

/*

Params:
	email (can be nickname as well)
	password
	ajax (if you want an ajax response)
Errors;
	no-email
	no-password
	wrong-password
	wrong-email
	
*/
$params=$_POST;

try {
	
	$conn=demetra_connect();
	
	$email=trim($params['email']);
	$password=trim($params['password']);
	
	if( !$email ) throw new Exception( 'no-email' );
	if( !$password ) throw new Exception( 'no-password' );
	
	$r=demetra_query($conn,'select id, password, nickname from user where email='.demetra_quote($conn,$email).' or nickname='.demetra_quote($conn,$email));
	if( mysqli_num_rows($r) ) {
		$row=mysqli_fetch_assoc($r);
		mysqli_free_result($r);
		if( $row['password']!=$password ) throw new Exception( 'wrong-password' );
		$_SESSION['user_id']=$row['id'];
		$_SESSION['nickname']=$row['nickname'];
	} else {
		throw new Exception( 'wrong-email' );
	}
	
	if( $params['ajax'] ) {
		echo json_encode( array( 'status'=>'ok' ) );
	} else {
		header( 'Location: '.$_SERVER['HTTP_REFERER'] );
	}
	
} catch( Exception $e ) {
	echo json_encode( array( 'status'=>'error', 'message'=>$e->getMessage() ) );
}



?>
