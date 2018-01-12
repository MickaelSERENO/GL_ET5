<?php
	session_start();
//	if(!isset($_SESSION["email"]))
//	{
//		header('Location: /connection.php');
//	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Projet GL</title>	
		<script type="text/javascript">
			var projectID = <?= $_GET['projectID'] ?>; 
		</script>
		<script type="text/javascript" src="/scripts/bower_components/xmlhttprequest/XMLHttpRequest.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular/angular.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-animate/angular-animate.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-sanitize/angular-sanitize.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>
		<script type="text/javascript" src="/scripts/setup.js"></script>
		<script type="text/javascript" src="/scripts/infoProject.js"></script>
		<script type="text/javascript" src="/scripts/ganttProject.js"></script>


		<link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="/CSS/style.css">
	</head>

	<body ng-app="myApp">
		<div>
			<uib-tabset  active="active">

				<!-- Information tab -->
				<uib-tab index="0" heading="Information">
					<div ng-controller="infoProjectCtrl">
						A faire part Stacy
					</div>
				</uib-tab>

				<!-- Gantt tab -->
				<uib-tab index="1" heading="Planning">
					<div ng-controller="ganttProjectCtrl">
						<!-- toolbar -->
						<ul class="list-inline">
							<li>
								<button class="btn btn-primary" ng-model="dispUnstarted" uib-btn-checkbox btn-checkbox-true="1" btn-checkbox-false="0">
									En cours
								</button>
							</li>
							<li>
								<button class="btn btn-primary" ng-click="expandTasks">
									Étendre
								</button>
							</li>
							<li>
								<button class="btn btn-primary" ng-click="reduceTasks">
									Serrer
								</button>
							</li>
						</ul>
						<div class="hLine"></div>

						<!-- the central part of the page -->
						<div class="row">
							<!-- Left part of the gantt tab containing information about tab -->
							<div class="infoGantt col-xs-3">
								<div id="sortingDiv">
									Trier par :
									<div class="btn-group sortList" uib-dropdown dropdown-append-to-body>
										<button type="button" class="btn btn-primary" uib-dropdown-toggle>
											{{sortTask[currentSorting]}}<span class="caret sortList"></span>
										</button>
										<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
											<li role="menuitem" ng-click="changeSorting(1)"><a href="">Date</a></li>
											<li role="menuitem" ng-click="changeSorting(2)"><a href="">Nom</a></li>
										</ul>
									</div>
								</div>

								<div class="hLine"></div>

								<!-- The list of tasks"-->
								<div id="taskTreeView">
									<script type="text/ng-template" id="treeViewTasks.html">
										<div ng-click="toggleExpandTask($parent)" class="taskNode">
											<span class="glyphicon glyphicon-menu-down" ng-show="task.canReduce()"></span>
											<span class="glyphicon glyphicon-menu-right" ng-show="task.canExpand()"></span>
											{{task.name}}
										</div>
											
										<ul ng-show="task.expand">
											<li ng-repeat="task in task.children" ng-include="'treeViewTasks.html'"></li>
										</ul>
									</script>
									<ul>
										<li ng-repeat="task in tasks" ng-include="'treeViewTasks.html'"></li>
									</ul>
								</div>
							</div>

							<div id="gantt" class="col-xs-9">
								<canvas id="ganttCanvas">
								</canvas>
							</div>
						</div>
					</div>
				</div>
			</uib-tab>
		</uib-tabset>
	</div>
</body>
</html>
