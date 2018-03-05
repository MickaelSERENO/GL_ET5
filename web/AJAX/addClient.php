<?php
require_once __DIR__."/../../PSQL/ClientsInfoRqst.php";

	session_start();
	if(!isset($_SESSION['email']) || $_SESSION['rank'] == 0)
	{
		echo "-1";
		return;
	}

	$clientRqst = new ClientRqst();
	$clientRqst->createClient($_GET['name'], $_GET['email'], $_GET['telephone'], $_GET['description']);
	echo "1";
	return;
?>
