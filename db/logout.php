<?php
session_start();

unset( $_SESSION['user_id'] );
unset( $_SESSION['nickname'] );

header( 'Location: '.$_SERVER['HTTP_REFERER'] );


?>
