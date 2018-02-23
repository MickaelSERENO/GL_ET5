<?php
	require_once __DIR__.'/../../PSQL/ClientProjectsRqst.php';
	session_start();
	
	$clientProjectRqst = new ClientProjectsRqst();
	$allClientProjects = $clientProjectRqst->getClientProjects();
	
	echo (json_encode($allClientProjects));

?>
