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

	//Close
	if($_GET['requestID'] == 0)
	{
		//Pay the distinction between administrator and project manager
		//Close the project 
		if($_SESSION['rank'] == 2) //Admin
		{
			if($projectRqst->closeProject($_GET['projectID'], true))
				echo '1';
		}
		else if($projectRqst->isManager($_GET['projectID']))
		{
			if($projectRqst->closeProject($_GET['projectID'], false))
				echo '1';
		}
		else
			echo '0';
	}

	//Open
	else if($_GET['requestID'] == 1)
	{
		//Pay the distinction between administrator and project manager
		//Open the project 
		if($_SESSION['rank'] == 2) //Admin
		{
			if($projectRqst->openProject($_GET['projectID'], true))
				echo '1';
		}
		else if($projectRqst->isManager($_GET['projectID']))
		{
			if($projectRqst->openProject($_GET['projectID'], false))
				echo '1';
		}
		else
			echo '0';
	}

	return;
?>
