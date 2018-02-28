<?php
	require_once __DIR__.'/../../PSQL/ContactsRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	//Redirect if not signed in
	if(!isset($_SESSION["email"]))
	{
		header('Location: /connection.php');
		return;
	}

	$rank = $_SESSION['rank'];
	$contactRqst = new ContactsRqst();
	$contact = $contactRqst->getContact($_GET['contactID']);
?>

<!DOCTYPE html>
<html>
	<head>
	
		<meta charset="UTF-8">
		<title>Projet GL</title>
		<script type="text/javascript" src="../scripts/bower_components/xmlhttprequest/XMLHttpRequest.js"></script>
		<script type="text/javascript" src="../scripts/bower_components/angular/angular.js"></script>
		<script type="text/javascript" src="../scripts/bower_components/angular-animate/angular-animate.js"></script>
		<script type="text/javascript" src="../scripts/bower_components/angular-sanitize/angular-sanitize.js"></script>
		<script type="text/javascript" src="../scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<script type="text/javascript" src="../scripts/bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>

		<link rel="stylesheet" type="text/css" href="../scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="../CSS/style.css">
		<link rel="stylesheet" type="text/css" href="../CSS/styleContact.css">
		
	</head>

	<body ng-app="myApp">
		<header class="headerConnected">
			<?php include('../Header/header.php'); ?>
		</header>
		
		<div id="centralPart">
			<div class="infoContact">
				<h3> &nbsp; <?= $contact->name ?> <?= $contact->surname ?> </h3>
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">
							<div class="row smallTopSpace">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Adresse email : </div>
										<div> &nbsp; <?= $contact->email ?> </div>
									</div>
								</div>
							</div>
							<div class="row smallTopSpace">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Téléphone : </div>
										<div> &nbsp; <?= $contact->telephone ?> </div>
									</div>
								</div>
							</div>
							<div class="row smallTopSpace">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Société : </div>
										<div> &nbsp; <?= $contact->entreprise ?> </div>
									</div>
								</div>
							</div>
							<div class="row smallTopSpace">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Statut : </div>
										<div> &nbsp; <?= $contact->status ?> </div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
