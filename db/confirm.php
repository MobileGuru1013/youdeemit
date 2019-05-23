<?php
require_once 'demetra.php';

/* Confirms the regostration

GET params:
	email
	code (which must match the one recorded on the database for this email)

*/


$email=$_GET['email'];
$code=$_GET['code'];
$lang='en';



try {
	if( !$email || !$code ) throw new Exception();
	
	$conn=demetra_connect();
	$sql='select id, nickname, password, lang from pending_user where email='.demetra_quote($conn,$email).' and code='.demetra_quote($conn,$code);
	$r=demetra_query($conn,$sql);
	if( mysqli_num_rows($r) ) {
		$row=mysqli_fetch_assoc($r);
		mysqli_free_result($r);
		$lang=$row['lang'];
		$nickname=$row['nickname'];
		$password=$row['password'];
		$id=$row['id'];
		demetra_query($conn,'insert into user ( id, nickname, email, password, lang ) values ( '.$id.', '.demetra_quote($conn,$nickname).', '.demetra_quote($conn,$email).', '.demetra_quote($conn,$password).', '.demetra_quote($conn,$lang).')');
		demetra_query($conn,'delete from pending_user where id='.$id);
	} else {
		throw new Exception();
	}
	
	$_SESSION['user_id']=$id;
	$_SESSION['nickname']=$nickname;

	header( 'Location: http://www.deemeter.com/' );
	
} catch( Exception $e ) {
	header( 'Location: http://www.deemeter.com/'.$lang.'/confirmError.php' );
}

	
?>
