<?php
	require_once __DIR__.'/PSQLDatabase.php';
	require_once __DIR__.'/ConnectionRqst.php';

	class Contact
	{
		public $name;
		public $surname;
		public $email;
		public $telephone;
		public $entreprise;
		public $status;
	}

	class ContactsRqst extends PSQLDatabase
	{
		public function getContact($email)
		{			
			$contact = new Contact;
			
			$script = "SELECT name, surname, email, telephone
						FROM Contact WHERE '$email'=email;";
			$resultScript = pg_query($this->_conn, $script);
			$row = pg_fetch_row($resultScript);
			
			if($row != null)
			{
				$contact->name = $row[0];
				$contact->surname = $row[1];
				$contact->email = $row[2];
				$contact->telephone = $row[3];
				$connect = new ConnectionRqst();
				$rank = $connect->getRank($email);
				if($rank == 0)
				{
					$contact->status = "Collaborateur";
				}
				else if($rank == 1)
				{
					$contact->status = "Responsable de projet";
				}
				else
				{
					$contact->status = "Contact Client";
				}
			}
			
			$scriptEntreprise = "SELECT Client.name
						FROM Client, ClientContact WHERE '$email'=ClientContact.contactEmail AND Client.email=ClientContact.clientEmail;";
			$resultScriptEntreprise = pg_query($this->_conn, $scriptEntreprise);
			$rowEntreprise = pg_fetch_row($resultScriptEntreprise);
			
			if($rowEntreprise != null)
			{
				$contact->entreprise = $rowEntreprise[0];
			}
			else
			{
				$contact->entreprise = "PoPS2017-2018";
			}

            return $contact;
			
		}
	}
?>
