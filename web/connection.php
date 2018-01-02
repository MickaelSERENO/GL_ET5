<?php
	session_start();
	require_once __DIR__.'/../PSQL/ConnectionRqst.php';
	use ConnectionRqst;

	$IDENT_ERROR    = 0;
	$SUCCESSFUL     = 1;
	$ALREADY_LOGGED = 2;

	if(isset($_SESSION["connected"]))
	{
		echo($ALREADY_LOGGED);
		return;
	}

	$rqst = new ConnectionRqst();		
	if($rqst->identValid($_POST["email"], $_POST["pwd"], $_POST["isAdmin"]))
	{
		$_SESSION["connected"] = true;
		echo($SUCCESSFUL);
		return;
	}

	else
	{
		echo($IDENT_ERROR);
		return;
	}
?>
