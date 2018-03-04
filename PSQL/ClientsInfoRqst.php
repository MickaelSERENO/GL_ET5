<?php
	require_once __DIR__.'/PSQLDatabase.php';

	class Client
	{
		public $email;
		public $name;
		public $description;
		public $telephone;
	}

	class ClientRqst extends PSQLDatabase
	{
		public function getClients()
		{
			$clients = array();

            //Fetch clients information
			$script = "SELECT Client.email, Client.name, description, Client.telephone
						FROM Client;";

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
				$client->telephone      = $row[3];
				
				array_push($clients,$client);

			}

            return $clients;
			
		}

		public function createClient($name, $email, $telephone, $description)
		{
			$name        = pg_escape_string($name);
			$email       = pg_escape_string($email);
			$telephone   = pg_escape_string($telephone);
			$description = pg_escape_string($description);

            $script = "INSERT INTO Client(email, name, telephone, description) VALUES ('$email', '$name', '$telephone', '$description');";
            $resultScript = pg_query($this->_conn, $script);
		}
	}
?>
