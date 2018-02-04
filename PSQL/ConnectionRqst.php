<?php
	require_once __DIR__.'/../PSQL/PSQLDatabase.php';

	$adminRank          = 2;
	$projectManagerRank = 1;
	$collaboratorRank   = 0;

	class ConnectionRqst extends PSQLDatabase
	{
		public function emailExist($email, $admin)
		{
			$script = "";
			if($admin)
				$script = "SELECT contactEmail FROM EndUser INNER JOIN Administrator ON (EndUser.contactEmail = Administrator.userEmail) WHERE userEmail = '$email';";
			else
				$script = "SELECT contactEmail FROM EndUser INNER JOIN (SELECT userEmail FROM ProjectManager UNION SELECT userEmail FROM Collaborator) AS Col ON (EndUser.contactEmail = Col.userEmail) WHERE userEmail = '$email';";

			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);

			if($row == null)
				return false;
			return true;
		}

		//Check if the idents are valid or not
		public function identValid($email, $pwd, $admin)
		{
			$script = "";
			if($admin)
				$script = "SELECT contactEmail, password, isActive FROM EndUser INNER JOIN Administrator ON (EndUser.contactEmail = Administrator.userEmail) WHERE userEmail = '$email' AND isActive = TRUE;";
			else

				$script = "SELECT contactEmail, password, isActive FROM EndUser INNER JOIN (SELECT userEmail FROM ProjectManager UNION SELECT userEmail FROM Collaborator) AS Col ON (EndUser.contactEmail = Col.userEmail) WHERE userEmail = '$email' AND isActive = TRUE;";

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

				return $adminRank; //As project manager
			}

			return $collaboratorRank; //As collaborator
		}

		public function sendPasswordNotification($email)
		{

		}
	}
?>
