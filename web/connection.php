<?php
	session_start();
	require_once __DIR__.'/../PSQL/ConnectionRqst.php';
	use ConnectionRqst;

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
?>
