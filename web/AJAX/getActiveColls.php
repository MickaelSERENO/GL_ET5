<?php
	require_once __DIR__.'/../../PSQL/ContactsRqst.php';

	$contactsRqst = new ContactsRqst();
	echo json_encode($contactsRqst->getActiveCollaborators());
	return;
?>
