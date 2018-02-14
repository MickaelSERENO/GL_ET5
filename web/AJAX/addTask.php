<?php
	require_once __DIR__.'/../../PSQL/TaskRqst.php';
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if(!isset($_POST['projectID']) || !canModifyProject($_POST['projectID']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}

	//Change advancement, chargeConsumed, remaining
	if($_GET['requestID'] == 0)
	{
		$taskRqst     = new TaskRqst();
		$predecessors = json_decode($_POST['predecessors']);
		$children     = json_decode($_POST['children']);

		if($taskRqst->canAddTask($_POST['idProject'], $_POST['collEmail'], $_POST['initCharge'], $_POST['mother'], $_POST['startDate'], $_POST['endDate'], $predecessors, $children))
		{
			$taskRqst->addTask($_POST['idProject'], $_POST['name'], $_POST['collEmail'], $_POST['initCharge'], $_POST['mother'], $_POST['startDate'], $_POST['endDate'], $_POST['description'], $predecessors, $children);
		}
	}
?>
