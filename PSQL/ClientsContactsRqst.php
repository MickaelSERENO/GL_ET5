<?php
	require_once __DIR__.'/PSQLDatabase.php';

	class Contact
	{
		public $name;
		public $surname;
		public $email;
		public $telephone;
	}

	class ClientContactsRqst extends PSQLDatabase
	{
		public function getClientContacts($email)
		{
			$contacts = array();

            //Fetch clients information
			$script = "SELECT Contact.name, Contact.surname, Contact.email, Contact.telephone
						FROM Contact, ClientContact WHERE ClientContact.clientEmail = '$email' AND ClientContact.contactEmail = Contact.email;";

			$resultScript = pg_query($this->_conn, $script);
			
			while($row = pg_fetch_row($resultScript))
			{
				$contact = new Contact();
				$contact->name = $row[0];
				$contact->surname = $row[1];
				$contact->email = $row[2];
				$contact->telephone = $row[3];
				
				array_push($contacts,$contact);
			}

            return $contacts;			
		}
	}
?>
