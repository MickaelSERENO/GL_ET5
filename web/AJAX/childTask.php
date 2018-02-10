<?php
	require_once __DIR__.'/../../PSQL/TaskRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if(!isset($_GET['projectID']) || !canAccessProjet($_GET['projectID']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}

	if($_GET['requestID'] == 0)
	{
		$taskRqst = new TaskRqst();
		if($taskRqst->checkChild($_GET['idMother'], $_GET['idChild']))
		{
			$taskRqst->addChild($_GET['idMother'], $_GET['idChild'], $_SESSION["rank"] == 2);
			echo '1';
		}
		else
			echo '-1';
		return;
	}

	echo '-1';
	return;

?>
