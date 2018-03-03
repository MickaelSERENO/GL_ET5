<?php
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if(!isset($_GET['projectID']) || !canModifyProject($_GET['projectID']))
	{
		echo '-1';
		return;
	}

	$projectRqst   = new ProjectRqst();
	$projectRqst->deleteProject($_GET['projectID']);

	echo '1';
	return;
?>
