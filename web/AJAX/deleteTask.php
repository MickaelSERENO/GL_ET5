<?php
	require_once __DIR__.'/../../Libraries/check.php';
	require_once __DIR__.'/../../PSQL/TaskRqst.php';
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';

	session_start();

	//Get task project id
	$projectRqst = new ProjectRqst();
	$idProject   = $projectRqst->getProjectIDFromTask($_GET['taskID']);

	if(!canModifyProject($idProject))
	{
		echo "-1";
		return;
	}

	$taskRqst = new TaskRqst();
	$taskRqst->deleteTask($_GET['taskID']);
	echo "1";
	return;
?>
