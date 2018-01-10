<?php
	session_start();
	if(isset($_SESSION["email"]))
	{
		header('Location: /dashboard');
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Projet GL</title>	
		<script type="text/javascript" src="/scripts/bower_components/xmlhttprequest/XMLHttpRequest.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular/angular.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-animate/angular-animate.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-sanitize/angular-sanitize.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<script type="text/javascript" src="/scripts/new-password.js"></script>
		<script type="text/javascript" src="/scripts/setup.js"></script>
		<link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="CSS/style.css">
	</head>

	<body ng-app="myApp">
		<div ng-controller="passwordController">
			<div id="topBanner">
				<p>PoPS2017</p>
			</div>
			<div id="centralPart" class="alignElem">
				<div class="container-fluid">
					<div class="row topSpace">
						<div class="col-sm-3">
							<a href="/connection.php"><p>Retour vers la page de connexion</p></a>
						</div>
						<div class="col-sm-6">
							<div class="mainTitle">Mot de passe oublié</div>
						</div>
					</div>
					<div class="row topSpace">
						<div class="col-sm-3 col-sm-offset-1">
							<p>Utilisateur : </p>
						</div>
						<div class="col-sm-6">
						  <input type="text" class="form-control" ng-model="email">
						</div>
					</div>
					<div class="row topSpace">
						<button class="btn btn-primary col-sm-2 col-sm-offset-5" type="button" ng-click="trySending()">
							<p>Contacter l'administrateur</p>
						</button>

						<div class="col-lg-4 col-md-4 col-sm-4">
							<p class="errorMsg" ng-show="showMsg">{{logMsg}}</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
