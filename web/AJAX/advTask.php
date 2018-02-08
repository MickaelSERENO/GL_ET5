<?php
	require_once __DIR__.'/../../PSQL/TaskRqst.php';
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if(!isset($_GET['projectID']) || !canAccessProjet($_GET['projectID']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}

	//Change advancement, chargeConsumed, remaining
	if($_GET['requestID'] == 0)
	{
		$taskRqst = new TaskRqst();
		if($taskRqst->setTaskAdvancement($_GET['taskID'], $_SESSION['email'], $_SESSION['rank'], $_GET['advancement'], $_GET['chargeConsumed'], $_GET['remaining']))
			echo "1";
		else
			echo "-1";
		return;
	}

	echo "-1";
	return;
?>
