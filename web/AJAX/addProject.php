<?php
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	if($_SESSION['rank'] == 0)
	{
		echo '-1';
		return;
	}

	$projectRqst   = new ProjectRqst();
	$collaborators = json_decode($_GET['collaborators']);

	echo $projectRqst->createProject($_GET['name'], $_GET['description'], (int)$_GET['startTime'], (int)$_GET['endTime'], $_GET['managerEmail'], $_GET['clientEmail'], $_GET['contactClientEmail'], $collaborators);

	return;
?>

