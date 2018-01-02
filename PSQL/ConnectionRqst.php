<?php
	require_once __DIR__.'/../PSQL/PSQLDatabase.php';
	use PSQLDatabase;

	class ConnectionRqst extends PSQLDatabase
	{
		//Check if the idents are valid or not
		public function identValid($email, $pwd, $admin)
		{
			$script = "";
			if($admin)
				$script = "SELECT contactEmail, password, isActive FROM EndUser INNER JOIN Administrator ON (EndUser.contactEmail = Administrator.userEmail) WHERE userEmail = '$email' AND isActive = TRUE;";
			else

				$script = "SELECT contactEmail, password, isActive FROM EndUser INNER JOIN (SELECT * FROM ProjectManager UNION SELECT * FROM Collaborator) AS COL ON (EndUser.contactEmail = Col.userEmail) WHERE userEmail = '$email' AND isActive = TRUE;";

			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);

			if($row == null)
				return false;

			return password_verify($pwd, $row[1]);
		}

		//Get the rank of the end user (Collaborator or Project Manager. -1 if None)
		public function getRank($email)
		{
			$scriptCol       = "SELECT userEmail FROM Collaborator WHERE userEmail = '$email';";
			$resultScriptCol = pg_query($this->_conn, $scriptCol);
			$rowCol          = pg_fetch_row($resultScriptCol);

			if($rowCol == null)
			{
				$scriptPM       = "SELECT userEmail FROM ProjectManager WHERE userEmail = '$email';";
				$resultScriptPM = pg_query($this->_conn, $scriptPM);
				$rowPM          = pg_fetch_row($resultScriptPM);
				if($rowPM == null)
					return -1; //The user is not a collaborator nor a project manager

				return 1; //As project manager
			}

			return 0; //As collaborator
		}
	}
?>
