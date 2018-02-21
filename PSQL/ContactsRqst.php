<?php
	require_once __DIR__.'/PSQLDatabase.php';

	class Contact
	{
		public $name;
		public $surname;
		public $email;
		public $clientContactTelephone;
	}

	class ContactsRqst extends PSQLDatabase
	{
		public function getContacts()
		{
			$contacts = array();

            //Fetch contacts information
			$script = "SELECT name, surname, email, telephone
						FROM Contact
						INNER JOIN ClientContact
						ON email=contactEmail";

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
				$contact						= new Contact();
				$contact->name        			= $row[0];
				$contact->surname        		= $row[1];
				$contact->email					= $row[2];
				$contact->clientContactTelephone= $row[3];
				
				array_push($contacts,$contact);

			}

            return $contacts;
			
		}
	}
?>
