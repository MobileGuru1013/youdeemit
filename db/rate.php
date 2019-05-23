<?php

require_once 'demetra.php';

/*

this page accepts following parameters in GET:
url = the URL of the rated page
motivation= the name of the motivation (must be in database)
value = the value of the rating (1=ok, 2=difficult, 3=inappropriate)
type = can be 'domain' or 'page'
note = optionally a comment
note_lang = optionally the language of the comment (else it will be guessed)

*/



try {
	$conn=demetra_connect();
	
	// get the descriptor id
	$motivation=$_GET['motivation'];
	if( $motivation ) {
		$r=demetra_query($conn,'select id from motivation where name='.demetra_quote($conn,$motivation));
		if( mysqli_num_rows($r) ) {
			$row=mysqli_fetch_assoc($r);
			$motivation_id=$row['id'];
			mysqli_free_result($r);
		} else {
			throw new Exception( 'motivation '.$motivation.' not found' );
		}
	} else {
		$motivation_id=0;
	}
	
	// get the value
	$value=(int)$_GET['value'];
	
	// get the source ip
	$source_ip=$_SERVER['REMOTE_ADDR'];
	
	// get the session id
	$sid=session_id();
	
	// get the note
	$note=$_GET['note'];
	if( $note ) {
		// if a note is there get the language
		$note_lang=$_GET['note_lang'];
		if( !$note_lang ) {
			$arr=split(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$guess=null;
			for( $i=0; $i<count($arr); ++$i ) {
				$l=trim($arr[$i]);
				$len=strlen($l);
				if( !$guess || $len>strlen($guess) ) {
					$guess=$l;
					if( $len==5 ) break;
				}
			}
			if( $guess ) $note_lang=$guess;
		}
		
	} else $note=null;
	
	// process the url
	$url=$_GET['url'];
	if( !$url ) throw Exception( 'No url in parameters' );
	$uurl=demetra_unpackURL($conn,$url);
	$domain=$uurl['domain'];
	$uri=$uurl['uri'];
	
	$domain_id=$uurl['domain_id'];
	if( !$domain_id ) {
		demetra_query($conn,'insert into domain (name) values ('.demetra_quote($conn,$domain).')');
		$r=demetra_query($conn,'select last_insert_id() as id');
		$row=mysqli_fetch_assoc($r);
		$domain_id=$row['id'];
		mysqli_free_result($r);
	}
	
	$type=$_GET['type'];
	if( $type!='domain' ) {
		$page_id=$uurl['page_id'];
		if( !$page_id ) {
			demetra_query($conn,'insert into page (domain_id, uri) values ('.$domain_id.','.demetra_quote($conn,$uri).')');
			$r=demetra_query($conn,'select last_insert_id() as id');
			$row=mysqli_fetch_assoc($r);
			$page_id=$row['id'];
			mysqli_free_result($r);
		}
		$table='p_rating';
		$target_id=$page_id;
	} else {
		$table='d_rating';
		$target_id=$domain_id;
	}
	
	// look if there is already a rating for this page coming from the same ip during the last 30 days
	$r=demetra_query($conn,'select id from '.$table.' where session_id='.demetra_quote($conn,$sid).' and target_id='.$target_id.' and timediff(date,current_timestamp())>-3600' );
	if( mysqli_num_rows($r) ) {
		echo json_encode(array('status'=>'duplicate'));
	} else {
		// insert the rating
		demetra_query( $conn, 'insert into '.$table.' (session_id, motivation_id, value, target_id, source_ip, note, note_lang, user_id) values ('.demetra_quote($conn,$sid).','.$motivation_id.','.$value.','.$target_id.','.demetra_quote($conn,$source_ip).','.demetra_quote($conn,$note).','.demetra_quote($conn,$note_lang).','.demetra_quote($conn,$_SESSION['user_id']).')' );
		echo json_encode(array('status'=>'ok'));
	}
} catch( Exception $e ) {
	echo json_encode( array('status'=>'error','message'=>$e->getMessage()) );
}
?>
