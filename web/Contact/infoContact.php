<?php
	session_start();
	if(!isset($_GET['contactEmail']))
	{	//POUR TEST
		$email="contactSiliconSaclay@email.com";
	}
	else
	{
		$email=$_GET['contactEmail'];
	}
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
		
		<script type="text/javascript" src="../scripts/setup.js"></script>
		
		<script type="text/javascript" src="../scripts/infoContact.js"></script>

		<link rel="stylesheet" type="text/css" href="../scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="../CSS/style.css">
		
	</head>

	<body ng-app="myApp">
	
		    <div id="topBanner">
				<p>PoPS2017  -  {{loggerInfo.contactemail}}</p>
			</div>
			
			<div id="centralPart">
					<div class="container-fluid">
						<div class="alignElem">
							<div class="row" ng-controller="ContactCtrl" >
								<div class="row"  ><h2> Name : {{selectedContact.name}} {{selectedContact.surname}} </h2></div>
									<div class="row" ><span> Email : {{ selectedContact.email}} </span> </div>
									<div class="row" ><span> Telephone : {{ selectedContact.clientContactTelephone}} </span> </div>
							</div>
						</div>
					
					</div>

			</div>
			
				
	</body>
</html>
