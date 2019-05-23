<?php

/*

accepts a get parameter url and returns on success a json in the form:
{
	"status":"ok",
	"rate":"1",
	"ratings":"45"
}

*/

require_once 'demetra.php';

try {
	$conn=demetra_connect();
	$resp=array();
	$url=$_GET['url'];
	if( !$url ) throw new Exception( 'No url in parameters' );
	$uurl=demetra_unpackURL($conn,$url);
	$page_id=$uurl['page_id'];
	$domain_id=$uurl['domain_id'];
	$sum=0;
	$ratings=0;
	$pageVoted=0;
	$domainVoted=0;
	$sid=session_id();
	if( $page_id ) {
		$r=demetra_query($conn,'select count(*) as num,sum(value) as sum, session_id as sid from p_rating where datediff(date,current_timestamp())>-180 and target_id='.$page_id.' group by session_id='.demetra_quote($conn,$sid).' desc' );
		switch( mysqli_num_rows($r) ) {
			case 1:
				$arr=mysqli_fetch_assoc($r);
				$ratings+=$arr['num'];
				$sum+=$arr['sum'];
				if( $arr['sid']==$sid ) $pageVoted=1;
				break;
			case 2:
				$arr=mysqli_fetch_assoc($r);
				$ratings+=$arr['num'];
				$sum+=$arr['sum'];
				$arr=mysqli_fetch_assoc($r);
				$ratings+=$arr['num'];
				$sum+=$arr['sum'];
				$pageVoted=1;
				break;
			default:
				break;
		}
		mysqli_free_result($r);
	}
	if( $domain_id ) {
		$r=demetra_query($conn,'select count(*) as num,sum(value) as sum, session_id as sid from d_rating where datediff(date,current_timestamp())>-180 and target_id='.$domain_id.' group by session_id='.demetra_quote($conn,$sid).' desc');
		switch( mysqli_num_rows($r) ) {
			case 1:
				$arr=mysqli_fetch_assoc($r);
				$ratings+=$arr['num'];
				$sum+=$arr['sum'];
				if( $arr['sid']==$sid ) $domainVoted=1;
				break;
			case 2:
				$arr=mysqli_fetch_assoc($r);
				$ratings+=$arr['num'];
				$sum+=$arr['sum'];
				$arr=mysqli_fetch_assoc($r);
				$ratings+=$arr['num'];
				$sum+=$arr['sum'];
				$domainVoted=1;
				break;
			default:
				break;
		}
		mysqli_free_result($r);
	}
	
	if( $ratings ) {
		$rate=round($sum*100/$ratings)/100;
	} else {
		$rate=0;
	}
	
	echo json_encode(array('status'=>'ok','rate'=>$rate,'ratings'=>$ratings, 'pageAlreadyRated'=>$pageVoted, 'domainAlreadyRated'=>$domainVoted,'userNickname'=>$_SESSION['nickname'] ));
	
} catch( Exception $e ) {
	echo json_encode(array('status'=>'error','message'=>$e->getMessage()));
}


?>
