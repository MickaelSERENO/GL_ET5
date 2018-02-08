<?php
	require_once __DIR__.'/../PSQL/PSQLDatabase.php';

	class EndUser
	{
		public $name;
		public $surname;
		public $email;
	}

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

		public function getCollaborators($idProject)
		{
			$result = array();

			$script       = "SELECT name, surname, email 
							 FROM Contact, EndUser, ProjectCollaborator 
							 WHERE Contact.email = EndUser.contactEmail AND Contact.email = ProjectCollaborator.collaboratorEmail AND ProjectCollaborator.idProject = $idProject;"; 
			$resultScript = pg_query($this->_conn, $script);

			while($row = pg_fetch_row($resultScript))
			{
				$endUser          = new EndUser();
				$endUser->name    = $row[0];
				$endUser->surname = $row[1];
				$endUser->email   = $row[2];
				array_push($result, $endUser);
			}

			return $result;
		}

		public function setCollaborator($idTask, $collEmail)
		{
			$script       = "UPDATE Task SET collaboratorEmail = '$collEmail' WHERE Task.id = $idTask";
			$resultScript = pg_query($this->_conn, $script);

			return true;
		}

		public function isTaskDateValide($idTask, $startDate, $endDate)
		{
			$startDate->setTime(0,0,0);
			$endDate->setTime(0,0,0);

			//Get the project date
			$start = null;
			$end   = null;

			$script       = "SELECT Project.startDate, Project.endDate FROM Project, AbstractTask WHERE AbstractTask.id = $idTask AND AbstractTask.idProject = Project.id";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			if($row == null)
				return false;

			$start = DateTime::createFromFormat("Y-m-d H:i:s", $row[0] . " 00:00:00", new DateTimeZone("UTC"));
			$end   = DateTime::createFromFormat("Y-m-d H:i:s", $row[1] . " 00:00:00", new DateTimeZone("UTC"));

			//Get the minimum successor date
			$script       = "SELECT MIN(startDate) FROM AbstractTask, TaskOrder WHERE TaskOrder.predecessorID = $idTask AND AbstractTask.id = TaskOrder.successorID;";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			if($row[0] != "")
				$end   = DateTime::createFromFormat("Y-m-d H:i:s", $row[0] . " 00:00:00", new DateTimeZone("UTC"));

			//Get the maximum predecessor date
			$script = "SELECT MAX(endDate) FROM Task, TaskOrder WHERE TaskOrder.successorID = $idTask AND Task.id = TaskOrder.predecessorID;";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			if($row[0] != "")
				$start = DateTime::createFromFormat("Y-m-d H:i:s", $row[0] + " 00:00:00", new DateTimeZone("UTC"));

			//TODO notif
			return $startDate->getTimestamp() >= $start->getTimestamp() && $endDate->getTimestamp() <= $end->getTimestamp();
		}

		public function setTaskDate($idTask, $startDate, $endDate)
		{
			$startFormat  = $startDate->format("Y-m-d");
			$endFormat    = $endDate->format("Y-m-d");

			$script       = "UPDATE AbstractTask SET startDate = '$startFormat' WHERE id = $idTask;";
			$resultScript = pg_query($this->_conn, $script);

			$script       = "UPDATE Task SET endDate = '$endFormat' WHERE id = $idTask;";
			$resultScript = pg_query($this->_conn, $script);
		}
	}
?>
