<?php
	require_once __DIR__.'/../../PSQL/ClientProjectsRqst.php';
	session_start();
	
	$clientProjectRqst = new ClientProjectsRqst();
	$allClientProjects = $clientProjectRqst->getClientProjects($_GET['clientEmail']);
	
	echo (json_encode($allClientProjects));
	return;

?>
