<?php
	require_once __DIR__.'/../PSQL/TaskRqst.php';

	function canAccessProjet($id)
	{
		//Check if the user is connected
		if(!isset($_SESSION["email"]))
			return false;

		//Check the rank of this user
		$rank = $_SESSION["rank"];
		if($rank != 2) //If not admin
		{
			$taskRqst = new TaskRqst();

			//Is it a collaborator of this project ?
			if(!$taskRqst->isCollaborator($_SESSION["email"], $id))
				return false;
		}

		return true;
	}

?>
