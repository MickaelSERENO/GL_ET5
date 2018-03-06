<?php
	session_start();
	if(!isset($_SESSION['email']))
	{
		header('Location: /connection.php');
	}
?>


<!DOCTYPE html>
<html>
	<head>
	
		<meta charset="UTF-8">
		<title>Projet GL</title>
		<script type="text/javascript">
			var rank = <?= $_SESSION['rank'] ?>;
		</script>
		<script type="text/javascript" src="/scripts/bower_components/xmlhttprequest/XMLHttpRequest.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular/angular.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-animate/angular-animate.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-sanitize/angular-sanitize.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>
		
		<script type="text/javascript" src="/scripts/globalClients.js"></script>
		<script type="text/javascript" src="/scripts/infoClient.js"></script>

		<link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="/CSS/style.css">
				
	</head>

	<body ng-app="myApp">
		<header class="headerConnected">
			<?php include('../Header/header.php'); ?>
		</header>

		<script type="text/ng-template" id="modalAddClient.html">
			<div class="modal-header">
				<h3 class="modal-title">Ajout d&apos;un nouveau client</h3>
			</div>

			<div class="modal-body">
				<div class="container-fluid">
					<div class="row smallTopSpace">
						Nom : <input type="text" ng-model="name"></input>
					</div>

					<div class="row smallTopSpace">
						Email : <input type="text" ng-model="email"></input>
					</div>

					<div class="row smallTopSpace">
						Téléphone : <input type="text" ng-model="telephone"></input>
					</div>

					<div class="row smallTopSpace">
						Description :
					</div>
					<div class="row">
						<textarea rows="3" ng-model="description" style="width:100%"></textarea>
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<div class="errorMsg" ng-show="errorMsg!=''">{{errorMsg}}</div>
				<button type="button" class="btn btn-primary" ng-click="add()">Ajouter</button>
				<button type="button" class="btn btn-warning" ng-click="cancel()">Annuler</button>
			</div>
		</script>
	
		<div ng-controller="globalClientsCtrl">
			<div id="centralPart">
				<!-- <h1 class="mainTitle">Clients</h1> -->
				<div ng-controller="ClientsCtrl">
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-4" id="listClients" >
								<table class="table tableList">
									<thead>
										<tr>
											<td>
												<h2 style="float:left;vertical-align:top">Clients</h2> 
												<div style="float:right"><img ng-show="rank == 1 || rank == 2" src="/Resources/Images/add_icon.png" ng-click="openAddClient()" width=32 height=32/></div>
											</td>
										</tr>
									</thead>
									<tbody>
										<tr ng-repeat="client in clients">
											<td ng-click="getClientInfo(client)">{{ client.name }}</td>
										</tr>
									</tbody>
								</table>
							</div>
							
							<div class="col-md-8" id="infosClient" ng-show="selectedClient!=null">
								<div class="row" id="descriptionClient">
									<div class="row">
										<h2> {{selectedClient.name}} </h2>
									</div>
									<div class="row">
										<span> Description : {{ selectedClient.description }} </span>
									</div>
									<div class="row">
										<span> Adresse email : {{ selectedClient.email}} </span>
									</div>
									<div class="row">
										<span> Telephone : {{ selectedClient.telephone}} </span>
									</div>
								</div>
								
								<div class="row" id="listProjectsClient">
									<div class="row">
										<h2> Projets </h2>
									</div>
									<div class="row">
										<table class="table tableList">
											<thead>
												<tr>
													<td> Nom du projet</td>
													<td> Responsable</td>
													<td> Client </td>
													<td> Début </td>
													<td> Fin </td>
												</tr>
											</thead>
											<tbody>
												<tr ng-repeat="project in clientProjects">
													<td> {{ project.name }}</td>
													<td> {{ project.managerEmail }}</td>
													<td> {{ project.client }}</td>
													<td> {{ project.startDate }}</td>
													<td> {{ project.endDate }}</td>
												</tr>
											</tbody>
										<table>
									</div>
								</div>
								
								<div class="row" id="listContactsClient">
									<div class="row">
										<h2> Contacts Client </h2>
									</div>
									<div class="row">
										<table class="table tableList">
											<thead>
												<tr>
													<td> Prénom </td>
													<td> Nom </td>
													<td> Email </td>
													<td> Téléphone </td>
												</tr>
											</thead>
											<tbody>
												<tr ng-repeat="contact in clientContacts">
													<td> {{ contact.name }}</td>
													<td> {{ contact.surname }}</td>
													<td> {{ contact.email }}</td>
													<td> {{ contact.telephone }}</td>
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
