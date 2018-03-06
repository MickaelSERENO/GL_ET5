<?php
	require_once __DIR__.'/PSQLDatabase.php';
	require_once __DIR__.'/ConnectionRqst.php';

	class BasicContact
	{
		public $name;
		public $surname;
		public $email;
	}

	class Contact extends BasicContact
	{
		public $clientEmail;
		public $telephone;
		public $entreprise;
		public $status;
		public $rank;
		public $active;
	}

	class ContactsRqst extends PSQLDatabase
	{
		public function getContact($email)
		{			
			$pgEmail = pg_escape_string($email);
			$contact = new Contact;
			
			$script = "SELECT name, surname, email, telephone
						FROM Contact WHERE '$pgEmail'=email;";
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
				if($rank == -1)
					$rank = 2;
				else
				{
					$activeScript       = "SELECT isActive FROM EndUser WHERE contactEmail='$pgEmail';";
					$resultActiveScript = pg_query($this->_conn, $activeScript);
					$activeRow          = pg_fetch_row($resultActiveScript);
					$contact->active    = $activeRow[0] == 't';
				}
				$contact->rank       = $rank;
			}
			
			$scriptEntreprise = "SELECT Client.name, Client.email
						FROM Client, ClientContact WHERE '$email'=ClientContact.contactEmail AND Client.email=ClientContact.clientEmail;";
			$resultScriptEntreprise = pg_query($this->_conn, $scriptEntreprise);
			$rowEntreprise = pg_fetch_row($resultScriptEntreprise);
			
			if($rowEntreprise != null)
			{
				$contact->entreprise  = $rowEntreprise[0];
				$contact->clientEmail = $rowEntreprise[1];
			}
			else
			{
				$contact->entreprise = "PoPS2017-2018";
			}

            return $contact;
			
		}

		public function getActiveCollaborators()
		{
			$script = "SELECT name, surname, email FROM Contact INNER JOIN EndUser ON email = contactEmail WHERE isActive = TRUE;";
			$resultScript = pg_query($this->_conn, $script);

			$contacts  = array();
			while($row = pg_fetch_object($resultScript))
				array_push($contacts, $row);
			return $contacts;
		}

		public function getActivePM()
		{
			$script = "SELECT name, surname, email FROM Contact INNER JOIN ProjectManager ON email = userEmail INNER JOIN EndUser ON userEmail = contactEmail WHERE isActive = TRUE;";
			$resultScript = pg_query($this->_conn, $script);

			$contacts  = array();
			while($row = pg_fetch_object($resultScript))
				array_push($contacts, $row);
			return $contacts;
		}

		public function setInactive($email)
		{
			$email        = pg_escape_string($email);
			$script       = "UPDATE EndUser SET isActive='false' WHERE contactEmail='$email';";
			$resultScript = pg_query($this->_conn, $script);

			return true;
		}

		public function setActive($email)
		{
			$email        = pg_escape_string($email);
			$script       = "UPDATE EndUser SET isActive='true' WHERE contactEmail='$email';";
			$resultScript = pg_query($this->_conn, $script);

			return true;
		}

		public function modifyClientContact($oldEmail, $newEmail, $name, $surname, $telephone, $clientEmail)
		{
			$pgName        = pg_escape_string($name);
			$pgSurname     = pg_escape_string($surname);
			$pgOldEmail    = pg_escape_string($oldEmail);
			$pgNewEmail    = pg_escape_string($newEmail);
			$pgTelephone   = pg_escape_string($telephone);
			$pgClientEmail = pg_escape_string($clientEmail);

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

            $script = "UPDATE Contact SET name='$pgName', surname='$pgSurname', email='$pgNewEmail', telephone='$pgTelephone' WHERE email = '$pgOldEmail';
                       UPDATE ClientContact SET clientEmail='$pgClientEmail' WHERE contactEmail = '$pgNewEmail';";
            $resultScript = pg_query($this->_conn, $script);

            return 0;
		}

		public function modifyEndUser($oldEmail, $newEmail, $name, $surname, $telephone, $newRank)
		{
			$pgName        = pg_escape_string($name);
			$pgSurname     = pg_escape_string($surname);
			$pgOldEmail    = pg_escape_string($oldEmail);
			$pgNewEmail    = pg_escape_string($newEmail);
			$pgTelephone   = pg_escape_string($telephone);

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

            $connect = new ConnectionRqst();
            $oldRank = $connect->getRank($oldEmail);

            if($oldRank == 0 && $newRank == 1)
            {
                $script = "BEGIN;DELETE FROM Collaborator WHERE userEmail='$oldEmail';
                           INSERT INTO ProjectManager VALUES('$oldEmail');COMMIT;";
                $resultScript = pg_query($this->_conn, $script);
            }

            $script = "UPDATE Contact SET name='$pgName', surname='$pgSurname', email='$pgNewEmail', telephone='$pgTelephone' WHERE email = '$pgOldEmail';";
            $resultScript = pg_query($this->_conn, $script);

            return 0;
		}
	}
?>
