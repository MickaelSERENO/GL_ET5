<?php
	require_once __DIR__."/../../PSQL/ClientsInfoRqst.php";
	session_start();

	//Is connected as admin
	if(!isset($_SESSION['email']) || $_SESSION['rank'] != 2)
	{
		echo '-10';
		return;
	}

	$clientRqst = new ClientRqst();
	echo $clientRqst->modifyClient($_GET['oldEmail'], $_GET['newEmail'], $_GET['name'], $_GET['telephone'], $_GET['description']);
	return;
?>

