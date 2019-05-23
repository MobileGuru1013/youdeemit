<?php
require_once 'demetra.php';

/*

POST params:
	nickname
	email
	password 
	lang
	ajax (if=1 response will be in json)
	
Error messages:
	no-password
	no-email
	no-nickname
	nickname-exists
	invalid-email
	email-exists
	could-not-send
*/


// got from: http://www.linuxjournal.com/article/9585
function valid_email($email) {
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex) {
      $isValid = false;
   }
   else {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64) {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255) {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.') {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local)) {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain)) {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',str_replace("\\\\","",$local))) {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local))) {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
         // domain not found in DNS                        
         $isValid = false;
      }
   }
   return $isValid;
}


function send_mail($email,$nickname,$code,$lang) {
	try {
		$sub=array(
			'it'=>'Conferma registrazione su YouDeemIt',
			'en'=>'Confirm registration on YouDeemIt'
		);
		$msg=array(
			'it'=>'Clicca su questo link per confermare la registrazione: http://www.deemeter.com/db/confirm.php?email='.$email.'&code='.$code.' .',
			'en'=>'Click on the following link to confirm your registration: http://www.deemeter.com/db/confirm.php?email='.$email.'&code='.$code.' .'
		);
		$headers = 	'From: register@deemeter.com' . "\r\n" .'Reply-To: no-reply@deemeter.com' . "\r\n";
		
		if( !mail( $email, $sub[$lang], $msg[$lang], $headers ) ) throw new Exception('could-not-send');

		return array('status'=>'ok' );
	} catch( Exception $e ) {
		throw new Exception( 'could-not-send' );
	}
}


// honey pot
$params=$_POST;
if( $params['user'] ) return;


$nickname=trim($params['nickname']);
$email=trim($params['email']);
$password=trim($params['password']);
//$pwsr=trim($params['pswr']);
$lang=$params['lang'];
$privacy=$params['privacy'];


try {
	
	if( !$email ) throw new Exception( 'no-email' );
	if( !$password ) throw new Exception( 'no-password' );
	//if( !$pswr ) throw new Exception( 'no-password-repeat' );
	if( !$nickname ) throw new Exception( 'no-nickname' );
	if( !$privacy ) throw new Exception( 'no-privacy' );
	if( !valid_email($email) ) throw new Exception( 'invalid-email' );
	
	if( !$lang ) $lang='en';

	$conn=demetra_connect();
	
	// check email is unique
	$r=demetra_query($conn,'select id from user where email='.demetra_quote($conn,$email));
	if( mysqli_num_rows($r) ) throw new Exception( 'email-exists' );
	mysqli_free_result($r);
	$r=demetra_query($conn,'select id, nickname, code, lang from pending_user where email='.demetra_quote($conn,$email));
	if( mysqli_num_rows($r) ) {
		// user already pending
		$row=mysqli_fetch_assoc($r);
		mysqli_free_result($r);
		if( $row['password']!=$password || $row['nickname']!=$nickname || $row['lang']!=$lang ) {
			// check the nickname is unique
			$r=demetra_query($conn,'select id from user where nickname='.demetra_quote($conn,$nickname));
			if( mysqli_num_rows($r) ) throw new Exception( 'nickname-exists' );
			// update the password and/or nickname and/or lang if necessary
			demetra_query($conn,'update pending_user set password='.demetra_quote($conn,$password).', nickname='.demetra_quote($conn,$nickname).', lang='.demetra_quote($conn,$lang).' where id='.$row['id']);
		}
		// send email
		if( $params['ajax'] ) echo json_encode( send_mail($email,$nickname,$row['code'],$lang ) );
		else header( 'Location: '.$_SERVER['HTTP_REFERER'] );
		return;
	}
	
	// check the nickname is unique
	$r=demetra_query($conn,'select id from user where nickname='.demetra_quote($conn,$nickname));
	if( mysqli_num_rows($r) ) throw new Exception( 'nickname-exists' );
	$r=demetra_query($conn,'select id from pending_user where nickname='.demetra_quote($conn,$nickname));
	if( mysqli_num_rows($r) ) throw new Exception( 'nickname-exists' );	
	
	// create a new unique code
	$r=null;
	do {
		if( $r ) mysqli_free_result($r);
		$chars='ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
		$len=strlen( $chars );
		$i=10;
		$code='';
		while( $i-- ) {
			$r=rand(0,$len-1);
			$code.=substr($chars,$r,1);
		}
		$r=demetra_query($conn,'select id from pending_user where code='.demetra_quote($conn,$code));
	} while( mysqli_num_rows($r) );
	
	// create the new pending user entry
	$r=send_mail( $email,$nickname,$code,$lang);
	demetra_query($conn,'insert into pending_user ( nickname, email, password, code, lang ) values ( '.demetra_quote($conn,$nickname).', '.demetra_quote($conn,$email).', '.demetra_quote($conn,$password).', '.demetra_quote($conn,$code).', '.demetra_quote($conn,$lang).')');
	if( $params['ajax'] ) echo json_encode($r);
	else header( 'Location: '.$_SERVER['HTTP_REFERER'] );
	
} catch( Exception $e ) {
	echo json_encode(array('status'=>'error','message'=>$e->getMessage()));	
}


?>
