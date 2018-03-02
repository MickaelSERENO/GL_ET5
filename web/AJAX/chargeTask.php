<?php
	require_once __DIR__.'/../../PSQL/TaskRqst.php';
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	$projectRqst = new ProjectRqst();
	$idProject   = $projectRqst->getProjectIDFromTask($_GET['taskID']);

	if(!canModifyProject($idProject))
	{
		echo "-1";
		return;
	}

	$taskRqst = new TaskRqst();
	if($_GET['request'] == 0)
	{
		if($projectRqst->isManager($_SESSION['email'], $idProject))
		{
			$taskRqst->setTaskInitCharge($_GET['taskID'], $_GET['initCharge']);
		}
		$taskRqst->setTaskCharge((int)$_GET['taskID'], (int)$_GET['chargeConsumed'], (int)$_GET['remaining']);
		echo '1';
		return;
	}

	else
	{
		echo '-1';
		return;
	}
?>
