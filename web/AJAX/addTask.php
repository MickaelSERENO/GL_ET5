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

	//Change advancement, chargeConsumed, remaining
	if($_GET['requestID'] == 0)
	{
		$taskRqst     = new TaskRqst();
		$predecessors = json_decode($_GET['predecessors']);

		if($_GET['isMarker'] == 0)
		{
			$children     = json_decode($_GET['children']);

			if($taskRqst->canAddTask($_GET['projectID'], $_GET['collEmail'], $_GET['initCharge'], $_GET['mother'], (int)($_GET['startDate']), (int)($_GET['endDate']), $predecessors, $children))
			{
				$taskRqst->addTask($_GET['projectID'], $_GET['name'], $_GET['collEmail'], $_GET['initCharge'], $_GET['mother'], (int)($_GET['startDate']), (int)($_GET['endDate']), $_GET['description'], $predecessors, $children, $_SESSION['rank'] == 2);
				echo '1';
			}
			else
				echo '-1';
			return;
		}
		else
		{
			if($taskRqst->canAddMarker($_GET['projectID'], (int)($_GET['startDate']), $predecessors))
			{
				$taskRqst->addMarker($_GET['projectID'], $_GET['name'], (int)($_GET['startDate']), $_GET['description'], $predecessors, $_SESSION['rank'] == 2);
				echo '1';
			}
			echo '-1';
			return;
		}
	}
	 echo '-1';
	return;
?>
