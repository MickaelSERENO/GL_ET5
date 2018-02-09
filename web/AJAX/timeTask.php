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

	if($_GET['requestID'] == 0) //Verify date
	{
		$startDate = new DateTime();
		$startDate->setTimestamp((int)($_GET['startDate'])/1000 + 1);
		$endDate   = new DateTime();
		$endDate->setTimestamp((int)($_GET['endDate'])/1000 + 1);

		if($taskRqst->isTaskDateValide($_GET['taskID'], $startDate, $endDate))
			echo '1';
		else
			echo '-1';
		return;
	}

	else if($_GET['requestID'] == 1) //Set date
	{
		$startDate = new DateTime();
		$startDate->setTimestamp((int)($_GET['startDate'])/1000 + 1);
		$endDate   = new DateTime();
		$endDate->setTimestamp((int)($_GET['endDate'])/1000 + 1);

		if(!$taskRqst->isTaskDateValide($_GET['taskID'], $startDate, $endDate))
		{
			echo '-1';
			return;
		}
		$taskRqst->setTaskDate($_GET['taskID'], $startDate, $endDate);
		echo '1';
		return;
	}

	echo '-1';
	return;
?>
