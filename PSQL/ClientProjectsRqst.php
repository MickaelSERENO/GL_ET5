<?php
	require_once __DIR__.'/PSQLDatabase.php';

	class Project
	{
		public $managerEmail;
		public $contactEmail;
		public $name;
		public $startDate;
		public $endDate;
	}

	class ClientProjectsRqst extends PSQLDatabase
	{
		public function getClientProjects()
		{
			$projects = array();

            //Fetch clients information
			$script = "SELECT managerEmail, contactEmail, name, startDate, endDate
						FROM Project";

			if (!($this->_conn))			{
				echo "Erreur lors de la connexion.\n";
				exit;
			}
			$resultScript = pg_query($this->_conn, $script);
			if( ! $resultScript ){
				echo "Erreur lors de la requête.\n";
				exit;
			}
			
			while($row = pg_fetch_row($resultScript))
			{
				$project				= new Project();
				$project->managerEmail 	= $row[0];
				$project->contactEmail 	= $row[1];
				$project->name        	= $row[2];
				$project->startDate    	= $row[3];
				$project->endDate    	= $row[4];
				
				array_push($projects,$project);

			}

            return $projects;
			
		}
	}
?>
