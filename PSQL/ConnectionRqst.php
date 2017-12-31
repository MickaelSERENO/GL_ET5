<?php
	require_once __DIR__.'/../PSQL/PSQLDatabase.php';
	use PSQLDatabase;

	class ConnectionRqst extends PSQLDatabase
	{
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
	}
?>
