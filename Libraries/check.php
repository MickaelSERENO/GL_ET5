<?php
	require_once __DIR__.'/../PSQL/ProjectRqst.php';

	function canAccessProject($id)
	{
		//Check if the user is connected
		if(!isset($_SESSION["email"]))
			return false;

		//Check the rank of this user
		$rank = $_SESSION["rank"];
		if($rank != 2 && $rank != 1) //If not admin
		{
			$projectRqst = new ProjectRqst();

			//Get the status of the project
			$status = $projectRqst->getProjectStatus($id);

			//Is it a collaborator of this project ?
			if($status != "CLOSED_INVISIBLED")
			{
				if(!$projectRqst->isCollaborator($_SESSION["email"], $id))
					return false;
			}
		}

		return true;
	}

	function canModifyProject($id)
	{
		//Check if the user is connected
		if(!isset($_SESSION["email"]))
			return false;

		//Check the rank of this user
		$rank = $_SESSION["rank"];
		if($rank == 2)
			return true;

		else if($rank == 1) //If not admin
		{
			$projectRqst = new ProjectRqst();
			if($projectRqst->isManager($_SESSION["email"], $id))
				return true;
		}

		return false;
	}

	// Check if the notification is one of the user's
	function canAccessNotification($id)
	{
		//Check if the user is connected
		if(!isset($_SESSION["email"]))
			return false;
		$notifR = new NotifRqst();
		$n = $notifR->getNotifByID($id);
		if($n->receiver == $_SESSION["email"])
		{
			return true;
		}
		return false;
	}
	
?>
