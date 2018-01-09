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
		<script type="text/javascript" src="/scripts/setup.js"></script>
		<script type="text/javascript" src="/scripts/connection.js"></script>
		<link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="CSS/style.css">
	</head>

	<body ng-app="myApp">
		<div ng-controller="formController">
			<div id="topBanner">
				<p>PoPS2017</p>
			</div>
			<div id="centralPart">
				<div class="alignElem">
					<h1 class="mainTitle">Connexion</h1>
					<div class="container-fluid">
						<!-- The email input -->
						<div class="row topSpace">
							<div class="col-sm-3 col-sm-offset-1">
								<p>Identifiant</p>
							</div>
							<div class="col-sm-6">
							  <input type="text" class="form-control" ng-model="email">
							</div>
						</div>	

						<!-- The password input -->
						<div class="row topSpace">
							<div class="col-sm-3 col-sm-offset-1">
								<p>Mot de passe</p>
							</div>
							<div class="col-sm-6">
							  <input type="password" class="form-control" ng-model="pwd">
							</div>
						</div>	
					</div>

					<div class="checkbox topSpace">
						<label>
							<input type="checkbox" ng-model="isAdmin">
							Se connecter en tant que Administrateur
						</label>
					</div>

					<p> <a href="/new-password.php">Mot de passe oublié</a></p>

					<div class="container-fluid">
						<div class="row">

							<button class="btn btn-primary col-sm-2 col-sm-offset-5" type="button" ng-click="tryConnection()">
								<p>Se connecter</p>
							</button>

							<p class="errorMsg col-lg-4 col-md-4 col-sm-4" ng-show="showMsg">L'adresse email ou le mot de passe est incorrect</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>

