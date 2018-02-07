<?php
    session_start();

    $_SESSION["email"] = 'jean.dupont@email.com';

	if(!isset($_SESSION["email"]))
	{
		header('Location: /connection.php');
	} else
    {
        header('Location: /list/list.html');
    }
?>