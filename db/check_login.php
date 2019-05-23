<?php

session_start();

if( $_SESSION['nickname'] ) {
	echo 'Logged in as: '.$_SESSION['nickname'];
} else {
    echo 'Not nogged in';
}

?>
