<?php
	require_once __DIR__.'/../../PSQL/TaskRqst.php';
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if(!isset($_GET['projectID']) || !canModifyProject($_GET['projectID']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}

	$taskRqst    = new TaskRqst();
	$projectRqst = new ProjectRqst();

	if($_GET['requestID'] == 0) //Fetch all collaborators
	{
		echo(json_encode($projectRqst->getCollaborators((int)($_GET['projectID']))));
		return;
	}

	else if($_GET['requestID'] == 1) //Set collaborator
	{
		if($taskRqst->setCollaborator((int)($_GET['taskID']), $_GET['collEmail'], -1))
			echo '1';
		else
			echo '-1';
		return;
	}

	else
	{
		echo -1;
		return;
	}
	return;
?>
