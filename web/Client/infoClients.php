<?php
	session_start();
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
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>
		
		<script type="text/javascript" src="/scripts/setup.js"></script>
		
		<script type="text/javascript" src="/scripts/globalClients.js"></script>
		<script type="text/javascript" src="/scripts/infoClient.js"></script>

		<link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="/CSS/style.css">
				
	</head>

	<body ng-app="myApp">
		<div ng-controller="globalClientsCtrl">
			<div id="centralPart">
					<!-- <h1 class="mainTitle">Clients</h1> -->
		
					<div ng-controller="ClientsCtrl">
					<div class="container-fluid">
						<div class="row"  >
						
							<div class="col-md-4" id="listClients" >
								<table class="table tableList">
									<thead>
										<tr><td><h2>Clients</h2></td></tr>
									</thead>
									<tbody>
										<tr ng-repeat="client in clients">
											<td ng-click="getClientInfo(client)">{{ client.name }}</td>
										</tr>
									</tbody>
								</table>
							</div>
							
							
							<div class="col-md-8" id="infosClient">
								<div class="row" id="descriptionClient">
									<div class="row"  ><h2> {{selectedClient.name}} </h2></div>
									
									<div class="row" ><span> Description : {{ selectedClient.description }} </span></div>
									<div class="row" ><span> Adresse email : {{ selectedClient.email}} </span> </div>
									<div class="row" ><span> Telephone : {{ selectedClient.contactTelephone}} </span> </div>
								</div>
								
								<div class="row" id="listProjectsClient">
									<div class="row"> <h2> Projets </h2> </div>
									<div class="row">
										<table class="table tableList">
											<thead>
												<tr>
													<td> Nom du projet</td>
													<td> Responsable</td>
													<td> Contact Client </td>
													<td> Début </td>
													<td> Fin </td>
												</tr>
											</thead>
											<tbody>
												<tr ng-repeat="project in clientProjects">
													<td> {{ project.name }}</td>
													<td> {{ project.managerEmail }}</td>
													<td> {{ project.contactEmail }}</td>
													<td> {{ project.startDate }}</td>
													<td> {{ project.endDate }}</td>
												</tr>
											</tbody>
										<table>
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
