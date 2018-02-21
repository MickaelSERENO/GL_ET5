<?php
	require_once __DIR__.'/../../PSQL/ContactsRqst.php';
	session_start();
	
	$contactsRqst = new ContactsRqst();
	$allContacts = $contactsRqst->getContacts();
	
	echo (json_encode($allContacts));

?>
