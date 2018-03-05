<?php
	require_once __DIR__.'/../../PSQL/NotifRqst.php';
	session_start();
	
	$notifsRqst = new NotifRqst();
	$count = $notifsRqst->countUnreadNotifs($_SESSION["email"]);
	
	echo $count;
?>
