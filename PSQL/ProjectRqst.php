<?php
	require_once __DIR__.'/../PSQL/PSQLDatabase.php';
	require_once __DIR__.'/../PSQL/TaskRqst.php';

	class EndUser
	{
		public $name;
		public $surname;
		public $email;
	}
	
	class Project
	{
		public $id;
		public $startDate;
		public $endDate;
		public $name;
		public $description;
		public $managerEmail;
		public $contactEmail;
		public $status;
	}
	
	class ProjectInfo extends Project
	{
		public $clientName;
		public $managerLastName;
		public $managerFirstName;
		public $contactLastName;
		public $contactFirstName;
		public $listCollab;
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
			return $row[0] == '1';
		}

		public function isManager($email, $id)
		{
			$script = "SELECT COUNT(*) FROM Project WHERE id=$id AND managerEmail='$email';";

			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);

			return $row[0] == '1';
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

		
		public function getInfoProject($idProject)
		{
			$project = new ProjectInfo();
			
			$script = "SELECT id, managerEmail, contactEmail, name, description, startDate, endDate, status
						FROM Project WHERE Project.id = $idProject;";
			$scriptProject = "SELECT name, description, startDate, endDate, managerEmail, contactEmail
						FROM Project WHERE Project.id = $idProject;";
			$resultScriptProject = pg_query($this->_conn, $scriptProject);
			$rowProject = pg_fetch_row($resultScriptProject);
			
			if($rowProject != null)
			{
				$project->name = $rowProject[0];
				$project->description = $rowProject[1];
				$project->startDate = DateTime::createFromFormat("Y-m-d H:i:s", $rowProject[2] . " 00:00:00", new DateTimeZone("UTC"));
				$project->endDate = DateTime::createFromFormat("Y-m-d H:i:s", $rowProject[3] . " 00:00:00", new DateTimeZone("UTC"));
				$project->managerEmail = $rowProject[4];
				$project->contactEmail = $rowProject[5];
			}
			
			$scriptManager = "SELECT surname, name FROM Contact WHERE Contact.email = '$project->managerEmail'";
			$resultScriptManager = pg_query($this->_conn, $scriptManager);
			$rowManager = pg_fetch_row($resultScriptManager);
			if($rowManager != null)
			{
				$project->managerLastName = $rowManager[0];
				$project->managerFirstName = $rowManager[1];
			}
			
			$scriptContactClient = "SELECT surname, name FROM Contact WHERE Contact.email = '$project->contactEmail'";
			$resultScriptContactClient = pg_query($this->_conn, $scriptContactClient);
			$rowContactClient = pg_fetch_row($resultScriptContactClient);
			if($rowContactClient != null)
			{
				$project->contactLastName = $rowContactClient[0];
				$project->contactFirstName = $rowContactClient[1];
			}
			
			$scriptClient = "SELECT Client.name FROM ClientContact, Client 
						WHERE Client.email = ClientContact.clientEmail AND ClientContact.contactEmail = '$project->contactEmail'";
			$resultScriptClient = pg_query($this->_conn, $scriptClient);
			$rowClient = pg_fetch_row($resultScriptClient);
			if($rowClient != null)
			{
				$project->clientName = $rowClient[0];
			}
			
			
			$project->listCollab = array();
			
			$scriptCollab = "SELECT Contact.name, Contact.surname FROM Contact, ProjectCollaborator
						WHERE ProjectCollaborator.idProject = $idProject AND ProjectCollaborator.collaboratorEmail = Contact.email";
			$resultScriptCollab = pg_query($this->_conn, $scriptCollab);
			while($rowCollab = pg_fetch_row($resultScriptCollab))
			{
				$collab = new EndUser();
				$collab->name = $rowCollab[0];
				$collab->surname = $rowCollab[1];
				array_push($project->listCollab, $collab);
			}
			
			return $project;
		}

		public function getManagedProjects($email, $started)
		{
			$projects = array();
			$script = "SELECT id
						FROM project 
						WHERE managerEmail = '$email'";
			if($started)
			{
				$script = $script . " AND status != 'NOT_STARTED'";
			}
			$script = $script . " ORDER BY status, startDate";
			$resultScript = pg_query($this->_conn, $script);
			
			while($row = pg_fetch_row($resultScript))
			{
				$project = $this->getInfoProject((int)$row[0]);
				$project->id = (int) $row[0];
				$project->startDate = $project->startDate->format("Y-m-d");
				$project->endDate = $project->endDate->format("Y-m-d");
				$project->status = $this->getProjectStatus($project->id);

				array_push($projects, $project);
			}
			return $projects;
		}
	}
?>
