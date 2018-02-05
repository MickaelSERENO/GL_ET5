<?php
	require_once __DIR__.'/../../PSQL/TaskRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if(!isset($_GET['projectID']) || !canAccessProjet($_GET['projectID']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}

	$taskRqst = new TaskRqst();
	$result = $taskRqst->getTasks((int)($_GET["projectID"]));

	echo(json_encode($result));
?>
