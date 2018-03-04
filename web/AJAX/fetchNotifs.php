<?php
	require_once __DIR__.'/../../PSQL/NotifRqst.php';
	session_start();
	
	if(!isset($_GET['unread']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}
	$notifsRqst = new NotifRqst();
	$notifs = $notifsRqst->getNotifs($_SESSION["email"], $_GET["unread"]);
	
	echo (json_encode($notifs, JSON_HEX_APOS | 
								JSON_HEX_QUOT | 
								JSON_HEX_AMP |
								JSON_HEX_TAG));
?>
