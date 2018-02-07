<?php
	require_once __DIR__.'/../../PSQL/NotifRqst.php';
	session_start();
/*
	// Redirect if not signed in
	if(!isset($_SESSION["email"]))
	{
		header('Location: /connection.php');
	}
*/
	$_SESSION["email"] = "jean.dupont@email.com";
	$notifRequest = new NotifRqst();
	$listeNotifs = $notifRequest->getNotifs($_SESSION["email"],false);
	
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
		
		<script type="text/javascript" src="/scripts/setup.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="/CSS/style.css">
		<link rel="stylesheet" type="text/css" href="/CSS/notifications.css">	
	</head>

	<body ng-app="myApp">
		<div ng-controller="formController">
			<div id="topBanner">
				<p>PoPS2017</p>
			</div>
			<div id="centralPart">
				<div class="alignElem">
					<table>
					<tbody>
						<tr>
							<td>Id</td>
							<td>Notification</td>
						</tr>
						<?php
							foreach($listeNotifs as $notif)
							{
						?>	
						<?php 
							echo '<tr ng-click="selectedNotif=' . $notif->id . '">'
						?>							
							<td>
								<?php		
									echo $notif->id;
							
								?>
							</td>
							<td>
								<?php	
									echo $notif->title;
							
								?>
							</td>
						</tr>
						<?php	
							}
						?>
					</tbody>
					</table>
				<p>{{ selectedNotif }}</p>
				</div>
			</div>

		</div>
	</body>

<script>
var app = angular.module('myApp', []);
app.controller('formController', function($scope) {
    $scope.count = 0;
});
</script> 
</html>

