<?php
	require_once __DIR__.'/../../PSQL/TaskRqst.php';

	session_start();

	//Redirect if not signed in
	if(!isset($_SESSION["email"]))
	{
		header('Location: /connection.php');
	}

	//Check the rank of this user
	$rank = $_SESSION["rank"];
	if($rank != 2) //If not admin
	{
		$taskRqst = new TaskRqst();

		//Is it a collaborator of this project ?
		if(!$taskRqst->isCollaborator($_SESSION["email"], $_GET["projectID"]))
		{
			http_response_code(403);
			die('Forbidden Access');
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Projet GL</title>	
		<script type="text/javascript">
			var projectID = <?= $_GET["projectID"] ?>; 
			var rank      = <?= $_SESSION["rank"] ?>;
		</script>
		<script type="text/javascript" src="/scripts/bower_components/xmlhttprequest/XMLHttpRequest.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular/angular.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-animate/angular-animate.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-sanitize/angular-sanitize.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>
		<script type="text/javascript" src="/scripts/setup.js"></script>
		<script type="text/javascript" src="/scripts/globalProject.js"></script>
		<script type="text/javascript" src="/scripts/infoProject.js"></script>
		<script type="text/javascript" src="/scripts/ganttProject.js"></script>


		<link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="/CSS/style.css">
	</head>

	<body ng-app="myApp">
		<div ng-controller="globalProjectCtrl">
			<uib-tabset active="activeTab">
				<!-- Information tab -->
				<uib-tab id="infoHeader" index="0" heading="Information" deselect="deselectTab()">
					<div ng-controller="infoProjectCtrl">
						A faire part Stacy
					</div>
				</uib-tab>

				<!-- Gantt tab -->
				<uib-tab id="ganttHeader" index="$index+1" heading="Planning" deselect="deselectTab()">
					<div ng-controller="ganttProjectCtrl" id="ganttDiv">
						<!-- toolbar -->
						<ul class="list-inline smallTopSpace">

<?php if($rank == 1 || $rank == 2) : ?>
							<li>
								<button class="btn btn-primary" ng-click="closeProject()">
									Clôturer
								</button>
							</li>
<?php endif; ?>	
							
							<li>
								<button class="btn btn-primary" ng-model="dispUnstarted" uib-btn-checkbox btn-checkbox-true="1" btn-checkbox-false="0">
									En cours
								</button>
							</li>
							<li>
								<button class="btn btn-primary" ng-click="expandTasks()">
									Étendre
								</button>
							</li>
							<li>
								<button class="btn btn-primary" ng-click="reduceTasks()">
									Serrer
								</button>
							</li>
<?php if($rank == 1 || $rank == 2) : ?>
							<li>
								<button class="btn btn-primary" ng-click="editionMode()">
									Mode édition
								</button>
							</li>
							<li>
								<button class="btn btn-primary" ng-click="notification()">
									Notifications
								</button>
							</li>
<?php endif; ?>	
						</ul>
						<div class="hLine"></div>

						<div class="container-fluid">
							<!-- the central part of the page -->
							<div class="row">
								<!-- Left part of the gantt tab containing information about tab -->
								<div class="infoGantt col-xs-3">
									<div class="row">
										<div id="sortingDiv" class="col-xs-6">
											Trier par :
											<div class="btn-group sortList" uib-dropdown dropdown-append-to-body>
												<button type="button" class="btn btn-primary" uib-dropdown-toggle>
													{{sortTask[currentSorting]}}<span class="caret sortList"></span>
												</button>
												<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
													<li role="menuitem" ng-click="changeSorting(0)"><a href="">Date</a></li>
													<li role="menuitem" ng-click="changeSorting(1)"><a href="">Nom</a></li>
												</ul>
											</div>
										</div>

										<div id="scaleDiv" class="col-xs-6">
											Échelle :
											<div class="btn-group sortList" uib-dropdown dropdown-append-to-body>
												<button type="button" class="btn btn-primary" uib-dropdown-toggle>
													{{scale[currentScale]}}<span class="caret sortList"></span>
												</button>
												<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
													<li role="menuitem" ng-click="changeScale(0)"><a href="">Jour</a></li>
													<li role="menuitem" ng-click="changeScale(1)"><a href="">Semaine</a></li>
												</ul>
											</div>
										</div>
									</div>

									<div class="hLine"></div>

									<!-- The list of tasks"-->
									<div id="taskTreeView">
										<script type="text/ng-template" id="treeViewTasks.html">
											<div class="taskNode">
												<span ng-click="toggleExpandTask($parent)" class="glyphicon glyphicon-menu-down" ng-show="task.canReduce()"></span>
												<span ng-click="toggleExpandTask($parent)" class="glyphicon glyphicon-menu-right" ng-show="task.canExpand()"></span>
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
									<canvas id="ganttCanvas" width=1600 height=800 ng-click="canvasClick($event)">
									</canvas>
									<div id="actionDiv" ng-show="showActionDiv()">
										<div class="actionButton">
											<div style="background-color:blue;width:20px;height:20px"></div>
										</div>
										<div class="actionButton">
											<div style="background-color:blue;width:20px;height:20px"></div>
										</div>
										<div class="actionButton">
											<div style="background-color:blue;width:20px;height:20px"></div>
										</div>
										<div class="actionButton">
											<div style="background-color:blue;width:20px;height:20px"></div>
										</div>
										<span style="display:block;clear:both;"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</uib-tab>
		</uib-tabset>
	</div>
</body>
</html>
