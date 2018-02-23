<?php
	require_once __DIR__.'/PSQLDatabase.php';

	class Client
	{
		public $email;
		public $name;
		public $description;
		public $contactEmail;
		public $contactTelephone;
	}

	class ClientRqst extends PSQLDatabase
	{
		public function getClients()
		{
			$clients = array();

            //Fetch clients information
			$script = "SELECT email, name, description,
								contactEmail, telephone
						FROM Client
						INNER JOIN ClientContact
						ON email=clientEmail";

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
				$client					= new Client();
				$client->email			= $row[0];
				$client->name        	= $row[1];
				$client->description    = $row[2];
				$client->contactEmail   = $row[3];
				$client->contactTelephone= $row[4];
				
				array_push($clients,$client);

			}

            return $clients;
			
		}
	}
?>
