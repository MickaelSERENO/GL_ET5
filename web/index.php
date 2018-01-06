<?php
	session_start();
	if(isset($_SESSION["email"]))
	{
		header('Location: /dashboard');
	}
	else
	{
		header('Location: /connection.php');
	}
?>
