<?php
	require_once __DIR__."/../../PSQL/ContactsRqst.php";
	session_start();

	//Is connected as admin
	if(!isset($_SESSION['email']) || $_SESSION['rank'] != 2)
	{
		echo '-10';
		return;
	}

	$contactRqst = new ContactsRqst();
	echo $contactRqst->modifyClientContact($_GET['oldEmail'], $_GET['newEmail'], $_GET['name'], $_GET['surname'], $_GET['telephone'], $_GET['clientEmail']);
	return;
?>
