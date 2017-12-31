<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Projet GL</title>	
		<script type="test/javascript" src="bower_components/angular/angular.js"></script>
		<script type="test/javascript" src="bower_components/angular-animate/angular-animate.js"></script>
		<script type="test/javascript" src="bower_components/angular-sanitize/angular-sanitize.js"></script>
		<script type="test/javascript" src="bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<script type="test/javascript" src="scripts/connection.js"></script>
		<link rel="stylesheet" type="text/css" href="bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="CSS/style.css">
	</head>

	<body ng-app="myApp">
		<form action="/connection.php" ng-controller="formController" method="post">
			<div class="centralPart centralConnection">
				<h1 class="connectionTitle">Connexion</h1>
				<div class="container">
					<!-- The email input -->
					<div class="row topSpace">
						<div class="col-lg-2 col-md-2 col-sm-3 col-md-offset-2">
							Identifiant
						</div>
						<div class="col-lg-4 col-md-4 col-sm-6">
						  <input type="text" class="form-control" id="email" name="email">
						</div>
					</div>	

					<!-- The password input -->
					<div class="row topSpace">
						<div class="col-lg-2 col-md-2 col-sm-3 col-md-offset-2">
							Mot de passe
						</div>
						<div class="col-lg-4 col-md-4 col-sm-6">
						  <input type="password" class="form-control" id="email" ng-model="pwd" name="pwd">
						</div>
					</div>	
				</div>

				<div class="checkbox topSpace">
					<label>
						<input type="checkbox" name="isAdmin">
						Se connecter en tant que Administrateur
					</label>
				</div>

				<button class="btn btn-primary topSpace" type="submit">
					Se connecter
				</button>
			</div>
		</form>
	</body>
</html>
