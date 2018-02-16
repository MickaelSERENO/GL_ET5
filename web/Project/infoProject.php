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

	else if(!isset($_GET['projectID']) || !canAccessProject($_GET['projectID']))
	{
		http_response_code(403);
		die('Forbidden Access');
	}

	$rank          = $_SESSION['rank'];
	$projectRqst   = new ProjectRqst();
	$projectStatus = $projectRqst->getProjectStatus($_GET['projectID']);
	
	$projectInfo = $projectRqst->getInfoProject($_GET['projectID']);
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
		<header class="headerConnected">
			<?php include('../Header/header.php'); ?>
		</header>
		
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
											<div class="row smallTopSpace smallBottomSpace">
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
								<div class="buttonInfo">
									<div class="centerItem">
										<ul class="list-inline text-center">
											<div class="container-fluid">
												<div class="row">
													<div class="col-md-4"> </div>
													<div class="col-md-2">
														<li class="list-inline-item"> <div class="buttonItem"> Modifier </div></li>
													</div>
													<div class="col-md-2">
														<li class="list-inline-item"> <div class="buttonItem"> Supprimer </div></li>
													</div>
													<div class="col-md-4"> </div>
												</div>
											</div>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</uib-tab>

					<!-- Gantt tab -->
					<uib-tab id="ganttHeader" index="$index+1" heading="Planning" deselect="deselectTab()">
						<div ng-controller="ganttProjectCtrl" id="ganttDiv" class="whiteProject">

							<!-- Pop ups -->

							<!-- Add task popup-->
							<script type="text/ng-template" id="modalAdd.html">
								<div class="modal-header">
									<h3 class="modal-title">Ajouter une tâche</h3>
								</div>
								<div class="modal-body">
									<div class="container-fluid">
										<div class="row">
											<div class="col-md-12">
												Projet :
												{{project.name}}
											</div>
										</div>
										<div class="row smallTopSpace">
											<div class="col-md-6">
												Nom : 
												<input type="text" ng-model="name"></input>
											</div>
											<div class="col-md-6">
												Jalon : <input type="checkbox" ng-model="isMarker"></input>
											</div>
										</div>

										<div class="row smallTopSpace" ng-show="!isMarker">
											Collaborateur : 
											<div class="btn-group" uib-dropdown dropdown-append-to-body>
												<button type="button" class="btn btn-primary" uib-dropdown-toggle>
													{{collaborators[currentCol].name}}<span class="caret sortList"></span>
												</button>
												<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
													<li role="menuitem" ng-repeat="c in collaborators" ng-click="clickCollaborators($index)"><a href="">{{c.name}} {{c.surname}}</a></li>
												</ul>
											</div>
										</div>

										<div class="row smallTopSpace">
											<div class="col-md-4">
												Début :
												<p class="input-group">
													<input type="text" class="form-control" uib-datepicker-popup="{{dateFormat}}" ng-model="startDate" is-open="popupStartDate.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
													<span class="input-group-btn">
														<button type="button" class="btn btn-default" ng-click="openStartDate()"><i class="glyphicon glyphicon-calendar"></i></button>
													</span>
												</p>
											</div>

											<div class="col-md-4" ng-show="!isMarker">
												Fin :
												<p class="input-group">
													<input type="text" class="form-control" uib-datepicker-popup="{{dateFormat}}" ng-model="endDate" is-open="popupEndDate.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
													<span class="input-group-btn">
														<button type="button" class="btn btn-default" ng-click="openEndDate()"><i class="glyphicon glyphicon-calendar"></i></button>
													</span>
												</p>
											</div>

											<div class="col-md-4" ng-show="!isMarker">
												Charge initiale : <br/>
												<input class="numberInput" type="number" ng-model="initCharge"></input>
											</div>
										</div>

										<div class="row smallTopSpace" ng-show="!isMarker">
											<div class="col-md-12">
												Tâche parente : 
												<div class="btn-group" uib-dropdown dropdown-append-to-body>
													<button type="button" class="btn btn-primary" uib-dropdown-toggle>
														{{fullTasks[mother].name}}<span class="caret sortList"></span>
													</button>
													<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
														<li role="menuitem" ng-repeat="t in fullTasks" ng-click="clickMother($index)"><a href="">{{t.name}}</a></li>
													</ul>
												</div>
											</div>
										</div>

										<div class="row smallTopSpace">
											<div class="col-md-12">
												<div>
													Description : 
												</div>
												<textarea style="width:100%;" ng-model="description" rows="5"></textarea>
											</div>
										</div>

										<div class="row smallTopSpace" ng-show="!isMarker">
											<div class="col-md-12">
												Sous-tâches : 

												<ul class="list-inline listSpaceRight">
													<li ng-repeat="t in children track by $index">
														<div class="closeWrapper">
														<div>{{taskMother[t].name}}</div>
															<span class="close" ng-click="delChild($index)"></span>
														</div>
													</li>

													<li>
														<div class="btn-group" uib-dropdown dropdown-append-to-body>
															<button type="button" class="btn btn-primary" uib-dropdown-toggle>
																{{taskMother[0].name}}<span class="caret sortList"></span>
															</button>
															<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
																<li role="menuitem" ng-repeat="t in taskMother" ng-click="clickChildren($index)"><a href="">{{t.name}}</a></li>
															</ul>
														</div>
													</li>
												</ul>
											</div>
										</div>

										<div class="row smallTopSpace">
											<div class="col-md-12">
												Prédécesseurs : 

												<ul class="list-inline listSpaceRight">
													<li ng-repeat="t in predecessors track by $index">
														<div class="closeWrapper">
														<div>{{fullTasksPred[t].name}}</div>
															<span class="close" ng-click="delPredecessor($index)"></span>
														</div>
													</li>
													<li>
														<div class="btn-group" uib-dropdown dropdown-append-to-body>
															<button type="button" class="btn btn-primary" uib-dropdown-toggle>
																{{fullTasks[0].name}}<span class="caret sortList"></span>
															</button>
															<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
																<li role="menuitem" ng-repeat="t in fullTasksPred" ng-click="clickPredecessor($index)"><a href="">{{t.name}}</a></li>
															</ul>
														</div>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<div    class="row errorMsg" ng-show="showMsg"><div class="col-md-12">{{errorMsg}}</div></div>
									<button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
									<button class="btn btn-warning" type="button" ng-click="cancel()">Cancel</button>
								</div>
							</script>

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
											<input class="numberInput col-xs-2" type="number" ng-model="task.chargeConsumed"/>
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
											<p class="input-group col-xs-4">
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

							<!--Child-->
							<script type="text/ng-template" id="modalChild.html">
								<div class="modal-header">
									<h3 class="modal-title">Ajouter une sous tâche</h3>
								</div>
								<div class="modal-body alignedDiv">
									<ng-form name="myForm" novalidate>
										<div>
											Ajouter une sous-tâche : 
										</div>
										<div class="btn-group sortList" uib-dropdown dropdown-append-to-body>
											<button type="button" class="btn btn-primary" uib-dropdown-toggle>
												{{currentTaskTxt()}}<span class="caret sortList"></span>
											</button>
											<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
												<li role="menuitem" ng-repeat="t in fullTasks" ng-click="clickTask($index)"><a href="">{{t.name}}</a></li>
											</ul>
										</div>
									</ng-form>
								</div>
								<div class="modal-footer">
									<button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
									<button class="btn btn-warning" type="button" ng-click="cancel()">Cancel</button>
								</div>
							</script>


							<!--Successors-->
							<script type="text/ng-template" id="modalSuccessor.html">
								<div class="modal-header">
									<h3 class="modal-title">Ajouter un prédécesseur</h3>
								</div>
								<div class="modal-body alignedDiv">
									<ng-form name="myForm" novalidate>
										<div>
											Ajouter un prédécesseur : 
										</div>
										<div class="btn-group sortList" uib-dropdown dropdown-append-to-body>
											<button type="button" class="btn btn-primary" uib-dropdown-toggle>
												{{currentTaskTxt()}}<span class="caret sortList"></span>
											</button>
											<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
												<li role="menuitem" ng-repeat="t in fullTasks" ng-click="clickTask($index)"><a href="">{{t.name}}</a></li>
											</ul>
										</div>
									</ng-form>
								</div>
								<div class="modal-footer">
									<button class="btn btn-primary" type="button" ng-click="ok()">OK</button>
									<button class="btn btn-warning" type="button" ng-click="cancel()">Cancel</button>
								</div>
							</script>

							<script>
								
							</script>

							<!-- toolbar -->
							<ul class="list-inline smallTopSpace">

	<?php if(canModifyProject($_GET['projectID'])) : ?>
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
	<?php if(canModifyProject($_GET['projectID']) && $projectStatus != 'CLOSED_INVISIBLE') : ?>
								<li ng-hide="projectClosed()">
									<button class="btn btn-primary" ng-click="onEditionClick()">
										{{editionTxt}}
									</button>
								</li>
	<?php endif; ?>	
	<?php if(($rank == 1 || $rank == 2) && canModifyProject($_GET['projectID'])) : ?>
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
									<div id="infoGantt" class="col-xs-3">
										<div class="row" id="sortingDiv">
											<div class="col-md-4">
												Trier par :
											</div>
											<div class="col-md-8">
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
										</div>
										<div class="row smallTopSpace" id="scaleDiv">
											<div class="col-md-4">
												Échelle :
											</div>

											<div class="col-md-8">
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
	<?php if(($rank == 1 || $rank == 2) && canModifyProject($_GET['projectID'])) : ?>
											<button type="button" class="btn btn-primary alignedDiv smallBottomSpace" ng-click="openAddTask()">
												Add
											</button>
	<?php endif;?>

											<script type="text/ng-template" id="treeViewTasks.html">
												<div class="taskNode">
													<span ng-click="toggleExpandTask($parent)" class="glyphicon glyphicon-menu-down" ng-show="task.canReduce()"></span>
													<span ng-click="toggleExpandTask($parent)" class="glyphicon glyphicon-menu-right" ng-show="task.canExpand()"></span>
													<div class="taskBackground" ng-dblclick="openTask(task)" ng-click="selectTask(task, $event)" ng-class="{'lateTask' : !(task.stats == undefined || task.stats == 'STARTED' || task.stats == 'NOT_STARTED')}">
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
	<?php if(canModifyProject($_GET['projectID'])) : ?>
										<div id="actionDiv" ng-style="{'visibility' : showActionDiv() && !projectClosed() ? 'visible' : 'hidden'}">

	<?php if($projectStatus == "STARTED") : ?>
											<div class="actionButton" ng-click="openTaskAdv()" ng-show="taskSelected != null && secondTaskSelected == null && taskSelected.children.length == 0 && !projectClosed()">
												<div style="background-color:blue;width:20px;height:20px"></div>
											</div>
	<?php endif;?>

	<?php if(($projectRqst->isManager($_SESSION['email'], $_GET['projectID']) || $rank == 2) &&
			  $projectStatus != "CLOSED_INVISIBLE" && $projectStatus != "CLOSED_VISIBLE") : ?>
											<div class="actionButton" ng-click="openDateModal()" ng-show="taskSelected != null && secondTaskSelected == null && taskSelected.children.length == 0&& editionMode == true">
												<div style="background-color:red;width:20px;height:20px"></div>
											</div>
											<div class="actionButton" ng-click="openCollModal()">
												<div style="background-color:yellow;width:20px;height:20px" ng-show="taskSelected != null && secondTaskSelected == null && taskSelected.children.length == 0&& editionMode == true"></div>
											</div>
											<div class="actionButton" ng-click="openChild()" ng-show="taskSelected != null && editionMode == true && levelHierarchy(taskSelected) <= 2 && (secondTaskSelected == null || canAddTask(secondTaskSelected, taskSelected))">
												<div style="background-color:black;width:20px;height:20px"></div>
											</div>
											<div class="actionButton" ng-click="openSuccessor()" ng-show="taskSelected != null && editionMode == true && (secondTaskSelected == null || canAddPredecessor(secondTaskSelected, taskSelected))">
												<div style="background-color:green;width:20px;height:20px"></div>
											</div>
	<?php endif; ?>
										</div>
	<?php endif; ?>
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
