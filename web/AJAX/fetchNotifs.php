<?php
	require_once __DIR__.'/../../PSQL/NotifRqst.php';
	session_start();
	
	if(!isset($_GET["unread"]))
	{
		http_response_code(403);
		die('Forbidden Access');
	}
	$notifsRqst = new NotifRqst();
	$notifs = $notifsRqst->getNotifs($_SESSION["email"], $_GET["unread"] == 'true');
	
	file_put_contents('php://stderr', $_SESSION["email"].'\n');
	file_put_contents('php://stderr', (bool)$_GET["unread"] . '\n');
	file_put_contents('php://stderr', serialize($notifs).'\n');

	echo (json_encode($notifs, JSON_HEX_APOS | 
								JSON_HEX_QUOT | 
								JSON_HEX_AMP |
								JSON_HEX_TAG));
?>
