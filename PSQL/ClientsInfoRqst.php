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

		public function modifyClient($oldEmail, $newEmail, $name, $telephone, $description)
		{
			$pgName        = pg_escape_string($name);
			$pgOldEmail    = pg_escape_string($oldEmail);
			$pgNewEmail    = pg_escape_string($newEmail);
			$pgTelephone   = pg_escape_string($telephone);
			$pgDescription = pg_escape_string($description);

			if($oldEmail != $newEmail)
			{
				$script  = "SELECT COUNT(*) FROM Contact, Client WHERE Contact.email = '$pgNewEmail' OR Client.email = '$pgNewEmail';";
                $resultScript = pg_query($this->_conn, $script);
                $row = pg_fetch_row($resultScript);
                if($row[0] != 0)
                {
                    return 1;
                }
            }

            $script = "UPDATE Client SET name='$pgName', description='$pgDescription', email='$pgNewEmail', telephone='$pgTelephone' WHERE email = '$pgOldEmail';";
            $resultScript = pg_query($this->_conn, $script);

            return 0;
		}

	}
?>
