<?php
	require_once __DIR__.'/../PSQL/ProjectRqst.php';

	function canAccessProjet($id)
	{
		//Check if the user is connected
		if(!isset($_SESSION["email"]))
			return false;

		//Check the rank of this user
		$rank = $_SESSION["rank"];
		if($rank != 2) //If not admin
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

			//Check the special case of invisible project
			else
			{
				if(!$projectRqst->isManager($_SESSION["email"], $id))
					return false;
			}
		}

		return true;
	}

?>
