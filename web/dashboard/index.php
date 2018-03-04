<?php
require_once __DIR__.'/../../PSQL/ProjectRqst.php';
require_once __DIR__.'/../../PSQL/NotifRqst.php';
require_once __DIR__.'/../../PSQL/TaskRqst.php';
session_start();

//Redirect if not signed in
if(!isset($_SESSION["email"]))
{
		header('Location: /connection.php');
}
$user				= $_SESSION["email"];
$rank				= $_SESSION["rank"];
$notifRqst			= new NotifRqst();
$notifs				= $notifRqst->getNotifs($user, true);
if($rank == 1)
{
	$projetRsqt = new ProjectRqst();
	$projects	= $projetRsqt->getManagedProjects($user, true);
}
if($rank != 2)
{
	$taskRqst = new TaskRqst();
	$tasks = $taskRqst->getTasksOfUser($user, true);
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Tableau de bord</title>	

		<script type="text/javascript">
			var rank = <?=$rank?>;
			var notifs = JSON.parse(<?= '\''.json_encode($notifs,JSON_HEX_APOS).'\''?>);
		</script>
<?php if($rank == 1): ?>
		<script type="text/javascript">
			var projects = JSON.parse(<?= '\''.json_encode($projects,JSON_HEX_APOS).'\''?>);
		</script>
<?php endif?>
<?php if($rank != 2): ?>
		<script type="text/javascript">
			var tasks = JSON.parse(<?= '\''.json_encode($tasks,JSON_HEX_APOS).'\''?>);
		</script>
<?php endif?>

		<script type="text/javascript" src="/scripts/bower_components/angular/angular.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-animate/angular-animate.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-sanitize/angular-sanitize.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>
		<script type="text/javascript" src="/scripts/dashboard.js"></script>
		<script type="text/javascript" src="/scripts/taskModal.js"></script>
		<link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="/CSS/style.css">

	</head>

	<body ng-app="myApp">
		<header class="headerConnected">
			<?php include('../Header/header.php'); ?>
		</header>
		<div ng-controller="dashboardController">
			<div id="centralPart">
				<div class="alignElem">
					<h1 class="mainTitle">Tableau de bord</h1>
					<div class="container-fluid">
						<!-- Notif -->
						<div class="row">
							<div class="col-sm-6 col-sm-offset-3">
								<h2><a href="/dashboard/notifications.php">Notifications</a></h2>
								<table class="table tableList">
									<thead>
										<tr>
											<td>Titre</td>
											<td>Expéditeur</td>
											<td>Date</td>
											<td>Projet</td>
										</tr>
									</thead>
									<tbody>
	<?php if(count($notifs) != 0) : ?>
									<tr ng-repeat="notif in notifs" ng-click="linkTo(notif.id,'notif')">
											<td>{{ notif.title }}</td>
											<td>{{ notif.senderFirstName + " " + notif.senderLastName}}</td>
											<td>{{ notif.theDate | date:'dd/MM/yyyy' }}</td>
											<td>{{ notif.projectName }}</td>
									</tr>
	<?php else: ?>
									<tr>
										<td colspan=4>Pas de nouvelles notifications</td>
									</tr>
	<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
						<!-- Projet -->
<?php if($rank==1): ?>
						<div class="row">
							<div class="col-sm-6 col-sm-offset-3">
								<h2><a href="/listProject">Projets</a></h2>
								<table class="table tableList">
									<thead>
										<tr>
											<td>Statut</td>
											<td>Nom</td>
											<td>Début</td>
											<td>Fin</td>
											<td>Client</td>
										</tr>
									</thead>
									<tbody>
<?php if(count($projects)==0): ?>
										<tr>
											<td colspan=5>Vous n'avez aucun projet en cours</td>
										</tr>
<?php else:?>
										<tr ng-repeat="project in projects" ng-click="linkTo(project.id, 'project')">
											<td>{{ project.status }}</td>
											<td>{{ project.name }}</td>
											<td>{{ project.startDate | date:'dd/MM/yyyy'}}</td>
											<td>{{ project.endDate | date:'dd/MM/yyyy'}}</td>
											<td>{{ project.clientName}}</td>
										</tr>
<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
<?php endif; ?>
						<!-- Taches -->
<?php if($rank != 2): ?>
						<div class="row">
							<div class="col-sm-6 col-sm-offset-3">
						<h2><a href="/listTask">Tâches</a></h2>
						<table class="table tableList">
							<thead>
								<tr>
									<td>Statut</td>
									<td>Nom</td>
									<td>Projet</td>
									<td>Début</td>
									<td>Fin</td>
									<td>Avancement</td>
								</tr>
							</thead>
							<tbody>
<?php if(count($tasks)==0): ?>
								<tr>
									<td colspan=6>Vous n'avez aucune tâche en cours</td>
								</tr>
<?php else: ?>
								<tr ng-repeat="task in tasks" ng-click="openTask(task)">
									<td>{{ task.stats }}</td>
									<td>{{ task.name }}</td>
									<td>{{ project.name }}</td>
									<td>{{ task.startDate | date:'dd/MM/yyyy' }}</td>
									<td>{{ task.endDate | date:'dd/MM/yyyy'}}</td>
									<td>{{ task.advancement }}%</td>
								</tr>
<?php endif; ?>			
							</tbody>
						</table>
							<?php include('../../Libraries/TaskPopUp.php'); ?>
					</div>
				</div>
<?php endif; ?>			
			</div>
		</div>
		</div>
		</div>
	</body>
</html>
