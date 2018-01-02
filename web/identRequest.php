<?php
	session_start();
	require_once __DIR__.'/../PSQL/ConnectionRqst.php';
	use ConnectionRqst;
	
	//RequestID
	$ALREADY_CONNECTED = 1;
	$EMAIL_EXIST       = 2;
	$SIGNIN            = 3;

	if($_POST["requestID"] == $ALREADY_CONNECTED)
	{
		if(isset($_SESSION["email"]))
		{
			echo(1);
			return;
		}
		echo(0);
		return;
	}

	//Return
	else if($_POST["requestID"] == $EMAIL_EXIST)
	{

	}

	else if($_POST["requestID"] == $SIGN_IN)
	{
		//Ident values
		$IDENT_ERROR    = 0;
		$SUCCESSFUL     = 1;
		$ALREADY_LOGGED = 2;

		//Ranks
		$COLLABORATOR    = 0;
		$PROJECT_MANAGER = 1;
		$ADMIN           = 2;

		if(isset($_SESSION["email"]))
		{
			echo($ALREADY_LOGGED);
			return;
		}

		//Launch the request to the DB to know if the user exists
		$rqst = new ConnectionRqst();		
		if($rqst->identValid($_POST["email"], $_POST["pwd"], $_POST["isAdmin"]))
		{
			$_SESSION["email"]   = $_POST["email"];
			if(!$_POST["isAdmin"])
				$_SESSION["rank"] = $rqst->getRank($_POST["email"]);
			else
				$_SESSION["rank"] = $ADMIN;
			echo($SUCCESSFUL);
			return;
		}

		else
		{
			echo($IDENT_ERROR);
			return;
		}
	}

	//Return ERROR (-1)
	else
	{
		echo("-1");
		return;
	}
?>
