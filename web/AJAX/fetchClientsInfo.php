<?php
	require_once __DIR__.'/../../PSQL/ClientsInfoRqst.php';
	session_start();
	
	$clientRqst = new ClientRqst();
	$allClients = $clientRqst->getClients();
	
	echo (json_encode($allClients));

?>
