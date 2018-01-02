<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Projet GL</title>	
		<script type="text/javascript" src="bower_components/xmlhttprequest/XMLHttpRequest.js"></script>
		<script type="text/javascript" src="bower_components/angular/angular.js"></script>
		<script type="text/javascript" src="bower_components/angular-animate/angular-animate.js"></script>
		<script type="text/javascript" src="bower_components/angular-sanitize/angular-sanitize.js"></script>
		<script type="text/javascript" src="bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<script type="text/javascript" src="scripts/connection.js"></script>
		<link rel="stylesheet" type="text/css" href="bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="CSS/style.css">
	</head>

	<body ng-app="myApp">
		<div ng-controller="formController">
			<div class="centralPart centralConnection">
				<h1 class="connectionTitle">Connexion</h1>
				<div class="container-fluid">
					<!-- The email input -->
					<div class="row topSpace">
						<div class="col-sm-3 col-sm-offset-1">
							Identifiant
						</div>
						<div class="col-sm-6">
						  <input type="text" class="form-control" ng-model="email">
						</div>
					</div>	

					<!-- The password input -->
					<div class="row topSpace">
						<div class="col-sm-3 col-sm-offset-1">
							Mot de passe
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

				<div class="container-fluid">
					<div class="row topSpace">

						<button class="btn btn-primary col-sm-2 col-sm-offset-5" type="button" ng-click="tryConnection()">
							Se connecter
						</button>

						<p class="errorMsg col-lg-4 col-md-4 col-sm-4" ng-show="showMsg">L'adresse email ou le mot de passe a un format incorrect</p>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
