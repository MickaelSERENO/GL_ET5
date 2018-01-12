<?php
	session_start();
	require_once __DIR__.'/../../PSQL/TaskRqst.php';

	//TODO check if we can access to this project

	$taskRqst = new TaskRqst();
	$result = $taskRqst->getTasks((int)($_GET["projectID"]));

	echo(json_encode($result));
?>
