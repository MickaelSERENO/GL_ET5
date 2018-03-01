<?php
	require_once __DIR__.'/../../PSQL/TaskRqst.php';
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if(!isset($_GET['projectID']) || !canModifyProject($_GET['projectID']))
	{
		echo '-1';
		return;
	}

	$taskRqst = new TaskRqst();
	$predecessors = json_decode($_GET['predecessors']);

	if($_GET['isMarker']==1)
	{
		$taskRqst->updateMarker($_GET['taskID'], $_GET['name'], (int)$_GET['startDate'], $_GET['description'], $predecessors);
		echo '1';
	}
	else
	{
		$children = json_decode($_GET['children']);


		$taskRqst->updateTask($_GET['taskID'], $_GET['name'], (int)$_GET['startDate'], (int)$_GET['endDate'], $_GET['collEmail'], $_GET['description'], $_GET['mother'], $predecessors, $children);

		if(count($children) == 0)
		{
			error_log("ok");
			$taskRqst->setCollaborator((int)($_GET['taskID']), $_GET['collEmail'], time());
		}
		echo '1';
	}

	return;
?>
