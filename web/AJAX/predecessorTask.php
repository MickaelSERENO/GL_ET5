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
		if(!$taskRqst->checkSuccessor($_GET['idPred'], $_GET['idSucc']))
		{
			echo '-1';
		}
		else
		{
			$taskRqst->addSuccessor($_GET['idPred'], $_GET['idSucc'], $_SESSION['rank'] == 2);
			echo '1';
		}
		return;
	}
	return;
?>
