<?php
	require_once __DIR__.'/../../PSQL/TaskRqst.php';
	require_once __DIR__.'/../../PSQL/ProjectRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	//Redirect if not signed in
	if(!isset($_SESSION["email"]))
	{
		header('Location: /connection.php');
		return;
	}

	else if(!isset($_GET['projectID']) || !canAccessProjet($_GET['projectID']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}

	$rank          = $_SESSION['rank'];
	$projectRqst   = new ProjectRqst();
	$projectStatus = $projectRqst->getProjectStatus($_GET['projectID']);
	
	$projectInfo = $projectRqst->getInfoProject($_GET['projectID']);
	$blankSpace = " ";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Projet GL</title>	
		<script type="text/javascript">
			var projectID = <?= $_GET["projectID"] ?>; 
			var rank      = <?= $_SESSION["rank"] ?>;
			var email     = <?= "'".$_SESSION["email"]."'" ?>;
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
		<script type="text/javascript" src="/scripts/ganttModal.js"></script>
		<script type="text/javascript" src="/scripts/taskModal.js"></script>

		<link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="/CSS/style.css">
		<link rel="stylesheet" type="text/css" href="/CSS/ganttStyle.css">
	</head>

	<body ng-app="myApp">
		<div id="topBanner">
			<p>PoPS2017</p>
		</div>
		<div id="centralPart">
			<div ng-controller="globalProjectCtrl" id="ganttBody">
				<uib-tabset active="activeTab">
					<!-- Information tab -->
					<uib-tab id="infoHeader" index="0" heading="Information" deselect="deselectTab()">
						<div ng-controller="infoProjectCtrl">
							<div class="infoProject whiteProject">
								<h3> <?= $projectInfo->name ?> </h3>
								<div class="container-fluid">
									<div class="row">
										<div class="descriptionProject col-md-8">
											<div class="row smallTopSpace">
												<div class="col-md-6">
													<div class="flexDiv">
														<div> Client : </div>
														<div> &nbsp; <?= $projectInfo->clientName ?> </div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="flexDiv">
														<div> Contact client : </div>
														<div> &nbsp; <?= $projectInfo->contactFirstName ?> <?= $projectInfo->contactLastName ?> </div>
													</div>
												</div>
											</div>
											<div class="row smallTopSpace">
												<div class="col-md-12 flexDiv">
													<div> Responsable de projet : </div>
													<div> &nbsp; <?= $projectInfo->managerFirstName ?> <?= $projectInfo->managerLastName ?> </div>
												</div>
											</div>
											<div class="row smallTopSpace">
												<div class="col-md-6">
													<div class="flexDiv">
														<div> Début : </div>
														<div> &nbsp;  </div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="flexDiv">
														<div> Fin : </div>
														<div> &nbsp;  </div>
													</div>
												</div>
											</div>
											<div class="row smallTopSpace">
												<div class="col-md-12">
													<div> Description : </div>
												</div>
											</div>
											<div class="row smallTopSpace">
												<div class="col-md-12">
													<div> &nbsp; &nbsp; &nbsp; <?= $projectInfo->description ?> </div>
												</div>
											</div>
										</div>
										<div class="collabProject col-md-4">
											<p> Collaborateurs : </p>
											
											<div class="listCollabProject">
												<?php
												foreach($projectInfo->listCollab as $collab)
												{
													echo "<div>".$collab->name." ".$collab->surname."</div>";
												}
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div>
								<!-- bouton -->
							</div>
						</div>
					</uib-tab>

					<!-- Gantt tab -->
					<uib-tab id="ganttHeader" index="$index+1" heading="Planning" deselect="deselectTab()">
						<div ng-controller="ganttProjectCtrl" id="ganttDiv" class="whiteProject">

							<!-- Pop ups -->
							<!-- Task popup -->
							<?php include('../../Libraries/TaskPopUp.php'); ?>

							<!-- Advancement -->
							<script type="text/ng-template" id="modalAdv.html">
								<div class="modal-header">
									<h3 class="modal-title">Saisir Avancement</h3>
								</div>
								<div class="modal-body">
									<div class="container-fluid">
										<div class="row">
											<div class="col-xs-4">Charge consommée : </div>
											<input class="col-xs-2" type="number" id="advInput" ng-model="task.chargeConsumed"/>
											<div class="col-xs-2"> jour(s) </div>
										</div>

										<div class="row topSpace">
											<div class="col-xs-4">Avancement : </div>
											<div class="col-xs-2">{{task.advancement}} %</div>
											<div class="col-xs-4">
												<uib-progressbar value="task.advancement" id="advProgressbar"><span style="color:white; white-space:nowrap;">{{task.advancement}} %</span></uib-progressbar>
											</div>
										</div>

										<div class="row topSpace">
											<div class="col-xs-4">Reste à faire: </div>
											<div class="col-xs-2">{{task.remaining}}</div>
											<div class="col-xs-2"> jour(s) </div>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
									<button class="btn btn-warning" type="button" ng-click="cancel()">Cancel</button>
								</div>
							</script>

							<!-- Collaborator -->
							<script type="text/ng-template" id="modalColl.html">
								<div class="modal-header">
									<h3 class="modal-title">Changement de collaborateur</h3>
								</div>
								<div class="modal-body">
									<div class="container-fluid">
										<div class="row btn-group topSpace" uib-dropdown dropdown-append-to-body>
											<div class="col-xs-6">
												Collaborateur :
											</div>
											<div class="col-xs-6">
												<button type="button" class="btn btn-primary" uib-dropdown-toggle>
													{{currentCollTxt()}}<span class="caret sortList"></span>
												</button>
												<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
													<li role="menuitem" ng-repeat="c in collaborators" ng-click="clickCollaborators($index)"><a href="">{{c.name}} {{c.surname}}</a></li>
												</ul>
											</div>
										</div>

										<div class="row topSpace" ng-show="canShowDate()">
											<div class="col-xs-6">Date d'effet : </div>
											<p class="input-group class-xs-4">
												<input type="text" class="form-control" uib-datepicker-popup="{{dateFormat}}" ng-model="middleDate" is-open="popupDate.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
												<span class="input-group-btn">
													<button type="button" class="btn btn-default" ng-click="openDate()"><i class="glyphicon glyphicon-calendar"></i></button>
												</span>
											</p>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<div class="container-fluid">
										<div class="row">
											<div class="errorMsg col-sm-7" ng-style="{'visibility' : !dateCorrect() ? 'visible' : 'hidden'}">La date n'est pas comprise dans les dates de la tâche</div>
											<div class="col-sm-5">
												<button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
												<button class="btn btn-warning" type="button" ng-click="cancel()">Cancel</button>
											</div>
										</div>
									</div>
								</div>
							</script>

							<!--Date-->
							<script type="text/ng-template" id="modalDate.html">
								<div class="modal-header">
									<h3 class="modal-title">Changement de date</h3>
								</div>
								<div class="modal-body">
									<ng-form name="myForm" novalidate>
										<div class="container-fluid">
											<div class="row alignedDiv">
												<div class="col-xs-6">
													Début :
													<p class="input-group">
														<input type="text" class="form-control" uib-datepicker-popup="{{dateFormat}}" ng-model="task.startDate" is-open="popupStart.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
														<span class="input-group-btn">
															<button type="button" class="btn btn-default" ng-click="openStart()"><i class="glyphicon glyphicon-calendar"></i></button>
														</span>
													</p>
												</div>

												<div class="col-xs-6">
													Fin :
													<p class="input-group">
														<input type="text" class="form-control" uib-datepicker-popup="{{dateFormat}}" ng-model="task.endDate" is-open="popupEnd.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
														<span class="input-group-btn">
															<button type="button" class="btn btn-default" ng-click="openEnd()"><i class="glyphicon glyphicon-calendar"></i></button>
														</span>
													</p>
												</div>
												</div>
											</div>
										</div>
									</ng-form>
								</div>
								<div class="modal-footer">
									<button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
									<button class="btn btn-warning" type="button" ng-click="cancel()">Cancel</button>
								</div>
							</script>
							<!-- toolbar -->
							<ul class="list-inline smallTopSpace">

	<?php if($rank == 1 || $rank == 2) : ?>
								<li>
									<button class="btn btn-primary" ng-click="closeProject()">
										{{closeTxt}}
									</button>
								</li>
	<?php endif; ?>	
								
								<li>
									<button class="btn btn-primary" ng-click="unstartedClick()">
										{{unstartedTxt}}
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
	<?php if(($projectRqst->isManager($_SESSION['email'], $_GET['projectID']) || $rank == 2) && 
			  $projectStatus != 'CLOSED_INVISIBLE' && $projectStatus != 'CLOSED_VISIBLE') : ?>
								<li ng-hide="projectClosed()">
									<button class="btn btn-primary" ng-click="onEditionClick()">
										{{editionTxt}}
									</button>
								</li>
	<?php endif; ?>	
	<?php if($rank == 1 || $rank == 2) : ?>
								<li>
									<button class="btn btn-primary" ng-click="onNotificationClick()">
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

												<button type="button" class="btn btn-primary" ng-click="changeAsc()">{{asc ? "Az" : "Za"}}</button>
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
											<button type="button" class="btn btn-primary alignedDiv smallBottomSpace">
												Add
											</button>

											<script type="text/ng-template" id="treeViewTasks.html">
												<div class="taskNode">
													<span ng-click="toggleExpandTask($parent)" class="glyphicon glyphicon-menu-down" ng-show="task.canReduce()"></span>
													<span ng-click="toggleExpandTask($parent)" class="glyphicon glyphicon-menu-right" ng-show="task.canExpand()"></span>
													<div class="taskBackground" ng-dblclick="openTask(task)">
														{{task.name}}
													</div>
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
										<canvas id="ganttCanvas" width=1600 height=800 ng-dblclick="openCanvasTaskModal($event)" ng-click="canvasClick($event)">
										</canvas>
										<div id="actionDiv" ng-style="{'visibility' : showActionDiv() && !projectClosed() ? 'visible' : 'hidden'}">

	<?php if($projectStatus == "STARTED") : ?>
											<div class="actionButton" ng-click="openTaskAdv()" ng-style="{'visibility' : showActionDiv() && !projectClosed() ? 'visible' : 'hidden'}">
												<div style="background-color:blue;width:20px;height:20px"></div>
											</div>
	<?php endif;?>

	<?php if(($projectRqst->isManager($_SESSION['email'], $_GET['projectID']) || $rank == 2) &&
			  $projectStatus != "CLOSED_INVISIBLE" && $projectStatus != "CLOSED_VISIBLE") : ?>
											<div class="actionButton" ng-click="openDateModal()" ng-style="{'visibility' : showActionDiv() && editionMode == true ? 'visible' : 'hidden'}">
												<div style="background-color:red;width:20px;height:20px"></div>
											</div>
											<div class="actionButton" ng-click="openCollModal()" ng-style="{'visibility' : showActionDiv() && editionMode == true ? 'visible' : 'hidden'}">
												<div style="background-color:yellow;width:20px;height:20px"></div>
											</div>
											<div class="actionButton" ng-click="addSubTask()" ng-style="{'visibility' : showActionDiv() && editionMode == true ? 'visible' : 'hidden'}">
												<div style="background-color:black;width:20px;height:20px"></div>
											</div>
											<div class="actionButton" ng-click="addPredecessorTask()" ng-style="{'visibility' : showActionDiv() && editionMode == true ? 'visible' : 'hidden'}">
												<div style="background-color:green;width:20px;height:20px"></div>
											</div>
	<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</uib-tab>
				</uib-tabset>
			</div>
		</div>
	</body>
</html>
