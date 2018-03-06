<?php
	require_once __DIR__."/../../PSQL/ContactsRqst.php";

	session_start();
	if(!isset($_SESSION['email']) || $_SESSION['rank'] != 2)
	{
		echo '-1';
		return;
	}

	$contactRqst = new ContactsRqst();
	if($_GET['active'] == 'false')
	{
		if(!$contactRqst->setInactive($_GET['email']))
		{
			echo '-1';
			return;
		}
	}
	else
		$contactRqst->setActive($_GET['email']);

	echo '1';
	return;
?>
