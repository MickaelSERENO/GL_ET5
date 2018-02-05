<?php
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if(!isset($_GET['projectID']) || !canAccessProjet($_GET['projectID']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}

	$projectRqst = new ProjectRqst();

	//Pay the distinction between administrator and project manager
	//Close the project 
	if($_SESSION['rank'] == 2) //Admin
		$projectRqst->closeProject($_GET['projectID'], true);
	else if($projectRqst->isManager($_GET['projectID'])
		$projectRqst->closeProject($_GET['projectID'], false);
	else
	{
		http_response_code(403);
		die('Forbidden Access');
	}
?>
