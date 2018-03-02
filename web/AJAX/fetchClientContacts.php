<?php
	require_once __DIR__.'/../../PSQL/ClientsContactsRqst.php';
	session_start();
	
	$clientContactRqst = new ClientContactsRqst();
	$allClientContacts = $clientContactRqst->getClientContacts($_GET['clientEmail']);
	
	echo (json_encode($allClientContacts));

?>