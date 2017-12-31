<?php
	require_once __DIR__.'/../PSQL/ConnectionRqst.php';
	use ConnectionRqst;

	$rqst = new ConnectionRqst();		
	if($rqst->identValid($_POST["email"], $_POST["pwd"], $_POST["isAdmin"]))
		error_log("ok");
	else
		error_log("error");
?>
