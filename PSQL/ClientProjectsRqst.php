<?php
	require_once __DIR__.'/PSQLDatabase.php';

	class Project
	{
		public $managerEmail;
		public $clientEmail;
		public $name;
		public $startDate;
		public $endDate;
		public $statut;
	}

	class ClientProjectsRqst extends PSQLDatabase
	{
		public function getClientProjects($email)
		{
			$projects = array();

            //Fetch clients information
			$script = "SELECT Project.managerEmail, Client.name, Project.name, Project.startDate, Project.endDate
						FROM Project, ClientContact, Client WHERE Client.email = '$email' AND Project.contactEmail = ClientContact.contactEmail AND ClientContact.clientEmail = Client.email;";

			$resultScript = pg_query($this->_conn, $script);
			
			while($row = pg_fetch_row($resultScript))
			{
				$project				= new Project();
				$project->managerEmail 	= $row[0];
				$project->client 	    = $row[1];
				$project->name        	= $row[2];
				$project->startDate    	= $row[3];
				$project->endDate    	= $row[4];
				
				array_push($projects,$project);
			}

            return $projects;
		}
	}
?>
