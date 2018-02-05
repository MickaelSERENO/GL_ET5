<?php
	require_once __DIR__.'/../PSQL/PSQLDatabase.php';

	class ProjectRqst extends PSQLDatabase
	{
		public function closeProject($idProject, $isAdmin)
		{
			//Check if the project is already closed (need this because of notification)
			$status = $this->getProjectStatus($idProject);
			if($status == 'CLOSED_INVISIBLED' || $status == 'CLOSED_VISIBLE')
				return false;

			$script = "UPDATE Project SET status = 'CLOSED_INVISIBLE' WHERE id = $idProject;"; 
			$resultScript = pg_query($this->_conn, $script);

			//TODO send notifications
			return true;
		}

		public function openProject($idProject, $isAdmin)
		{
			//Check if the project is already closed (need this because of notification)
			$status = $this->getProjectStatus($idProject);
			if($status == 'STARTED')
				return false;

			$script = "UPDATE Project SET status = 'STARTED' WHERE id = $idProject;"; 
			$resultScript = pg_query($this->_conn, $script);

			//TODO send notifications
			return true;
		}

		public function isCollaborator($email, $projectID)
		{
			$script = "SELECT COUNT(*) FROM ProjectCollaborator WHERE idProject='$projectID' AND collaboratorEmail='$email';";

			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);

			if($row == null)
				return false;
			return true;
		}

		public function isManager($email, $id)
		{
			$script = "SELECT COUNT(*) FROM Project WHERE id=$id AND managerEmail='$email';";

			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);

			if($row == null)
				return false;
			return true;
		}

		public function getProjectStatus($id)
		{
			$script = "SELECT status FROM Project WHERE id = $id";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);

			if($row == null)
				return null;
			return $row[0];
		}
	}
?>
