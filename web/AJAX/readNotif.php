<?php
	require_once __DIR__.'/../../PSQL/NotifRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if(!isset($_GET['notifID']) || !canAccessNotification($_GET['notifID']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}

	$notifR = new NotifRqst();
	$notifR->readNotif($_GET['notifID']);
	echo '1';
?>
