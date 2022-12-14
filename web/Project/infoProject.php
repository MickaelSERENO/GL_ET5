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
	if($projectInfo == null)
	{
		http_response_code(403);
		die('Forbidden Access');
	}
		
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Projet GL</title>	
		<script type="text/javascript">
			var projectID         = <?= $_GET["projectID"] ?>;
			var rank              = <?= $_SESSION["rank"] ?>;
			var email             = <?= "'".$_SESSION["email"]."'" ?>;
			var projectInfo       = <?= json_encode($projectInfo) ?>;
			var ganttTaskID       = <?= isset($_GET["taskID"]) ? $_GET["taskID"] : -1 ?>;
			projectInfo.startDate = new Date(<?= $projectInfo->startDate->getTimestamp() ?>*1000);
			projectInfo.endDate   = new Date(<?= $projectInfo->endDate->getTimestamp() ?>*1000);
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
		<script type="text/javascript" src="/scripts/confirmModal.js"></script>

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
					<uib-tab id="infoHeader" index="0" heading="Information" deselect="deselectTab()" ng-click="goToInfoProject()">
						<div ng-controller="infoProjectCtrl">
							<?php include('../../Libraries/ProjectModal.php'); ?>
							<?php include('../../Libraries/confirmModal.php'); ?>
							<div class="infoProject whiteProject">
								<div class="container-fluid">
									<div class="row">
										<h3 class="col-md-6"> <input type="text" ng-model="name" ng-disabled="!inModifyStats"></input> </h3>
										<div class="col-md-6" style="vertical-align:middle"> Visible : <input type="checkbox" ng-disabled="status=='STARTED' || status == 'NOT_STARTED' || !inModifyStats" ng-model="projectIsVisible"></input></div>
									</div>
									<div class="row">
										<div class="descriptionProject col-md-8">
											<div class="row smallTopSpace">
												<div class="col-md-6">
													<div class="flexDiv">
														<div> Client : </div>
														<div> &nbsp; {{projectInfo.clientName}} </div>
														<img ng-click="openClient()" class="settingImg" src="/CSS/img/settings.svg" alt="Modifier client" ng-show="inModifyStats">
													</div>
												</div>
												<div class="col-md-6">
													<div class="flexDiv">
														<div> Contact client : </div>
														<div> &nbsp; <a style="color:black;" ng-href="/Contact/infoContact.php?contactID={{contactEmail | encodeURIComponent}}">{{contactFirstName}} {{contactLastName}}</a></div>
														<img ng-click="openClientContact()" class="settingImg" src="/CSS/img/settings.svg" alt="Modifier contact client" ng-show="inModifyStats">
													</div>
												</div>
											</div>
											<div class="row smallTopSpace">
												<div class="col-md-12 flexDiv">
													<div> Responsable de projet : </div>
													<div> &nbsp; <a style="color:black;" ng-href="/Contact/infoContact.php?contactID={{managerEmail | encodeURIComponent}}">{{managerFirstName}} {{managerLastName}}</a></div>
<?php if($rank == 2) : ?>
													<img ng-click="openProjectManager()" class="settingImg" src="/CSS/img/settings.svg" alt="Modifier contact client" ng-show="inModifyStats">
<?php endif; ?>
												</div>
											</div>
											<div class="row smallTopSpace">
												<div class="col-md-6">
													<div class="flexDiv">
														<div> D??but : </div>
                                                        <p class="flexDiv">
                                                            <input type="text" class="form-control" ng-disabled="!inModifyStats" uib-datepicker-popup="{{dateFormat}}" ng-model="startDate" is-open="popupStartDate.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
                                                            <button type="button" class="btn btn-default" ng-show = "inModifyStats" ng-click="openStartDate()"><i class="glyphicon glyphicon-calendar"></i></button>
                                                        </p>
													</div>
												</div>
												<div class="col-md-6">
													<div class="flexDiv">
														<div> Fin : </div>
                                                        <p class="flexDiv">
                                                            <input type="text" class="form-control" ng-disabled="!inModifyStats" uib-datepicker-popup="{{dateFormat}}" ng-model="endDate" is-open="popupEndDate.opened" datepicker-options="dateOptions2" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
                                                            <button type="button" class="btn btn-default" ng-show = "inModifyStats" ng-click="openEndDate()"><i class="glyphicon glyphicon-calendar"></i></button>
                                                        </p>
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
													<div> <textarea style="width:100%" rows="3" ng-model="description" ng-disabled="!inModifyStats"></textarea></div>
												</div>
											</div>
										</div>
										<div class="collabProject col-md-4">
                                            <div class="flexDiv">
                                                Collaborateurs :
                                                <img ng-show="inModifyStats" src="/Resources/Images/add_icon.png" width=20 height=20 class="mousePointer smallBottomSpace smallLeftSpace" ng-click="openAddCollaborators()">
                                                </div>
											
											<div class="listCollabProject" ng-class="{'modifyColl' : inModifyStats}">
                                                <div class="row" ng-repeat="coll in collaborators">
                                                    <div class="closeWrapper">

														<div> &nbsp; <a style="color:black;" ng-href="/Contact/infoContact.php?contactID={{coll.email | encodeURIComponent}}">{{coll.name}} {{coll.surname}}</a></div>
                                                        <span class="close" ng-click="delColl($index)" ng-show="inModifyStats && coll.email != managerEmail && !inCollaborator(coll.email)"></span>
                                                    </div>
                                                </div>
											</div>
										</div>
									</div>
								</div>
							</div>
<?php if($projectRqst->isManager($_SESSION['email'], $_GET['projectID']) || $rank == 2) :?>
							<div>
								<!-- bouton -->
								<div class="buttonInfo">
									<div class="centerItem">
										<ul class="list-inline text-center">
											<div class="container-fluid">
												<div class="row errorMsg" ng-show="errorMsg != ''"><div class="col-md-12">{{errorMsg}}</div></div>
												<div class="row">
													<div class="col-md-4"> </div>
													<div class="col-md-2" ng-show="!inModifyStats">
														<li class="list-inline-item"> <div class="buttonItem" ng-click="modify()"> Modifier </div></li>
													</div>
													<div class="col-md-2" ng-show="inModifyStats">
														<li class="list-inline-item"> <div class="buttonItem" ng-click="validate()"> Valider </div></li>
                                                    </div>
													<div class="col-md-2" ng-show="inModifyStats">
														<li class="list-inline-item"> <div class="buttonItem" ng-click="cancel()"> Annuler </div></li>
                                                    </div>
													<div class="col-md-2" ng-show="inModifyStats==false">
														<li class="list-inline-item"> <div class="buttonItem" ng-click="delete()"> Supprimer </div></li>
													</div>
													<div class="col-md-4"> </div>
												</div>
											</div>
										</ul>
									</div>
								</div>
							</div>
<?php endif; ?>
						</div>
					</uib-tab>

					<!-- Gantt tab -->
					<uib-tab id="ganttHeader" index="$index+1" heading="Planning" ng-click="goToGanttHeader()">
						<div ng-controller="ganttProjectCtrl" id="ganttDiv" class="whiteProject">

							<!-- Pop ups -->

							<!-- Add task popup-->
							<script type="text/ng-template" id="modalAdd.html">
								<div class="modal-header">
									<h3 class="modal-title">Ajouter une t??che</h3>
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
												D??but :
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
												T??che parente : 
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
												Sous-t??ches : 

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
												Pr??d??cesseurs : 

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
											<div class="col-xs-4">Charge consomm??e : {{task.chargeConsumed}} + </div>
											<input class="numberInput col-xs-2" type="number" ng-model="inc"/>
											<div class="col-xs-2"> jour(s) </div>
										</div>

										<div class="row topSpace">
											<div class="col-xs-4">Avancement : </div>
											<div class="col-xs-2">{{advancement}} %</div>
											<div class="col-xs-4">
												<uib-progressbar value="advancement" id="advProgressbar"><span style="color:white; white-space:nowrap;">{{advancement}} %</span></uib-progressbar>
											</div>
										</div>

										<div class="row topSpace">
											<div class="col-xs-4">Reste ?? faire: </div>
											<div class="col-xs-2">{{remaining}}</div>
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
											<div class="errorMsg col-sm-7" ng-style="{'visibility' : !dateCorrect() ? 'visible' : 'hidden'}">La date n'est pas comprise dans les dates de la t??che</div>
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
													D??but :
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
									<h3 class="modal-title">Ajouter une sous t??che</h3>
								</div>
								<div class="modal-body alignedDiv">
									<ng-form name="myForm" novalidate>
										<div>
											Ajouter une sous-t??che : 
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
									<h3 class="modal-title">Ajouter un pr??d??cesseur</h3>
								</div>
								<div class="modal-body alignedDiv">
									<ng-form name="myForm" novalidate>
										<div>
											Ajouter un pr??d??cesseur : 
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
									<img src="/Resources/Images/expand_icon.png" width=32 height=32 class="mousePointer" ng-click="expandTasks()">
								</li>
								<li>
									<img src="/Resources/Images/reduce_icon.png" width=32 height=32 class="mousePointer" ng-click="reduceTasks()">
								</li>

								<li ng-hide="projectClosed()">
									<button class="btn btn-primary" ng-click="onEditionClick()">
										{{editionTxt}}
									</button>
								</li>
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
												<img ng-click="changeAsc()" ng-src="{{asc && '/Resources/Images/up_icon.png' || '/Resources/Images/down_icon.png'}}" width="24" height="24" class="mousePointer">
											</div>
										</div>
										<div class="row smallTopSpace" id="scaleDiv">
											<div class="col-md-4">
												??chelle :
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
											<img src="/Resources/Images/add_icon.png" width=32 height=32 class="mousePointer smallBottomSpace alignedDiv" ng-click="openAddTask()">
	<?php endif;?>

											<script type="text/ng-template" id="treeViewTasks.html">
												<div class="taskNode">
													<span ng-click="toggleExpandTask($parent)" class="glyphicon glyphicon-menu-down" ng-show="task.canReduce()"></span>
													<span ng-click="toggleExpandTask($parent)" class="glyphicon glyphicon-menu-right" ng-show="task.canExpand()"></span>
													<div class="taskBackground" ng-dblclick="openTask(task)" ng-click="selectTask(task, $event)" ng-class="{'lateTask' : !(task.stats == undefined || task.stats == 'STARTED' || task.stats == 'NOT_STARTED' || task.stats == 'FINISHED')}">
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
	<?php if(canAccessProject($_GET['projectID'])) : ?>
										<div id="actionDiv" ng-style="{'visibility' : showActionDiv() && !projectClosed() ? 'visible' : 'hidden'}">

											<div class="actionButton" ng-click="openTaskAdv()" ng-show="taskSelected != null && secondTaskSelected == null && taskSelected.children.length == 0 && !projectClosed()">
												<img src="/Resources/Images/progress_bar.png" alt="advancement" width="32" height="32">
											</div>

	<?php if($projectRqst->isManager($_SESSION['email'], $_GET['projectID']) || $rank == 2) : ?>
											<div class="actionButton" ng-click="openDateModal()" ng-show="taskSelected != null && secondTaskSelected == null && taskSelected.children.length == 0&& editionMode == true">
												<img src="/Resources/Images/calendar_icon.png" alt="date" width="32" height="32">
											</div>
											<div class="actionButton" ng-click="openCollModal()" ng-show="taskSelected != null && secondTaskSelected == null && taskSelected.children.length == 0&& editionMode == true">
												<img src="/Resources/Images/coll_icon.png" alt="collaborateur" width="32" height="32">
											</div>
											<div class="actionButton" ng-click="openChild()" ng-show="taskSelected != null && editionMode == true && levelHierarchy(taskSelected) <= 2 && (secondTaskSelected == null || canAddTask(secondTaskSelected, taskSelected))">
												<img src="/Resources/Images/subtask_icon.png" alt="sous-t??che" width="32" height="32">
											</div>
											<div class="actionButton" ng-click="openSuccessor()" ng-show="taskSelected != null && editionMode == true && (secondTaskSelected == null || canAddPredecessor(secondTaskSelected, taskSelected))">
												<img src="/Resources/Images/predecessor_icon.png" alt="predecessor" width="32" height="32">
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
