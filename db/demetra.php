<?php
ERROR_REPORTING(E_ALL);
ini_set('display_errors', 1);

mb_language('uni');
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
//date_default_timezone_set(date_default_timezone_get());

if (get_magic_quotes_gpc()) {
    function stripslashes_deep($value) {
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
        return $value;
    }

    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

session_start();


function cmg_exceptionErrorHandler($errno, $errstr, $errfile, $errline ) {
	if( $errno==8 || $errno==8192 ) return;
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('cmg_exceptionErrorHandler');

function demetra_connect() {
	// set here your db credentials
	$conn=mysqli_connect('HOST', 'USER', 'PASS', 'DBNAME');
	return $conn;
	if (!$conn) {
		throw new Exception( 'Unable to connect to database.');
	}
	mysqli_query($conn,'set names utf8');
}

function demetra_close($conn) {
	mysqli_close($conn);
}

function demetra_query($conn,$sql,$log=0) {
	if( $log ) demetra_log($sql);
	$r=mysqli_query($conn,$sql);
	if( mysqli_error($conn) ) {
		throw new Exception(mysqli_error($conn));
	}
	return $r;
}

function demetra_log($s) {
	echo '<pre>',$s,'</pre>';
}

function demetra_quote( $conn, $s ) {
	if( is_null($s) ) return 'NULL';
	else return '\'' . mysqli_real_escape_string( $conn, $s ) . '\'';
}


function demetra_unpackURL($conn,$url) {
	if( !strncmp('http://',$url,7) ) {
		$url=substr($url,7);
	} else if( !strncmp('https://',$url,8) ) {
		$url=substr($url,8);
	}
	
	$fsp=strpos($url,'/');
	if( !$fsp ) {
		$domain=$url;
		$uri='/';
	} else {
		$domain=substr($url,0,$fsp);
		$uri=substr($url,$fsp);
	}
	
	// get the domain id
	$r=demetra_query($conn,'select id from domain where name='.demetra_quote($conn,$domain));
	if( mysqli_num_rows($r) ) {
		$row=mysqli_fetch_assoc($r);
		$domain_id=$row['id'];
		mysqli_free_result($r);
		// get the page id
		$r=demetra_query($conn,'select id from page where domain_id='.$domain_id.' and uri='.demetra_quote($conn,$uri));
		if( mysqli_num_rows($r) ) {
			$row=mysqli_fetch_assoc($r);
			$page_id=$row['id'];
			mysqli_free_result($r);
		} 
	} 
	
	return array(
		'domain'=>$domain,
		'uri'=>$uri,
		'domain_id'=>$domain_id,
		'page_id'=>$page_id
	);
	
}


?>

