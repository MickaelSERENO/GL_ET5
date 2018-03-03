<?php
    session_start();

//    $_SESSION["email"] = 'jean.dupont@email.com';
//    $_SESSION["email"] = 'stacy.gromat@email.com';

	if(!isset($_SESSION["email"]))
	{
		header('Location: /connection.php');
	}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Projet GL</title>
	<script type="text/javascript" src="/scripts/bower_components/xmlhttprequest/XMLHttpRequest.js"></script>
    <script type="text/javascript" src="/scripts/bower_components/angular/angular.js"></script>
    <script type="text/javascript" src="/scripts/bower_components/angular/checklist-model.js"></script>
	<script type="text/javascript" src="/scripts/bower_components/angular-animate/angular-animate.js"></script>
	<script type="text/javascript" src="/scripts/bower_components/angular-sanitize/angular-sanitize.js"></script>
	<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
	<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>
    <script type="text/javascript" src="/scripts/connection.js"></script>
    <script type="text/javascript" src="/scripts/list.js"></script>

    <link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/CSS/style.css">
    <link rel="stylesheet" type="text/css" href="/CSS/ganttStyle.css">
</head>

<body ng-app="myApp" ng-init="category='project';">
<header class="headerConnected">
	<?php include('../Header/header.php'); ?>
</header>

<?php include('../../Libraries/ProjectModal.php'); ?>
<!-- The add project pop up-->
<script type="text/javascript" src="/scripts/addProjectModal.js"></script>
<script type="text/ng-template" id="modalAddProject.html">
	<div class="modal-header">
		<h3 class="modal-title">Création de projet</h3>
	</div>

	<div class="modal-body">
		<div class="container-fluid">
			<div class="row">
				<div class="descriptionProject col-md-8">
					<div class="row smallTopSpace">
						<div class="col-md-6">
							<div class="flexDiv">
								<div> Client : </div>
								<div> &nbsp; {{clientName}} </div>
								<img ng-click="openClient()" class="settingImg" src="/CSS/img/settings.svg" alt="Modifier client">
							</div>
						</div>
						<div class="col-md-6">
							<div class="flexDiv">
								<div> Contact client : </div>
								<div> &nbsp; {{contactFirstName}} {{contactLastName}} </div>
								<img ng-click="openClientContact()" class="settingImg" src="/CSS/img/settings.svg" alt="Modifier contact client">
							</div>
						</div>
					</div>
					<div class="row smallTopSpace">
						<div class="col-md-12 flexDiv">
							<div> Responsable de projet : </div>
							<div> &nbsp; {{managerFirstName}} {{managerLastName}} </div>
							<img ng-click="openProjectManager()" class="settingImg" src="/CSS/img/settings.svg" alt="Modifier contact client">
						</div>
					</div>
					<div class="row smallTopSpace">
						<div class="col-md-6">
							<div class="flexDiv">
								<div> Début : </div>
								<p class="flexDiv">
									<input type="text" class="form-control" uib-datepicker-popup="{{dateFormat}}" ng-model="startDate" is-open="popupStartDate.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
									<button type="button" class="btn btn-default" ng-click="openStartDate()"><i class="glyphicon glyphicon-calendar"></i></button>
								</p>
							</div>
						</div>
						<div class="col-md-6">
							<div class="flexDiv">
								<div> Fin : </div>
								<p class="flexDiv">
									<input type="text" class="form-control" ng-disabled="!inModifyStats" uib-datepicker-popup="{{dateFormat}}" ng-model="endDate" is-open="popupEndDate.opened" datepicker-options="dateOptions2" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
									<button type="button" class="btn btn-default" ng-click="openEndDate()"><i class="glyphicon glyphicon-calendar"></i></button>
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
							<div> <textarea style="width:100%" rows="3" ng-model="description" ></textarea></textarea></div>
						</div>
					</div>
				</div>
				<div class="collabProject col-md-4">
					<div class="flexDiv">
						Collaborateurs :
						<img src="/Resources/Images/add_icon.png" width=20 height=20 class="mousePointer smallBottomSpace smallLeftSpace" ng-click="openAddCollaborators()">
						</div>
					
					<div class="listCollabProject inModifyStats">
						<div class="row" ng-repeat="coll in collaborators">
							<div class="closeWrapper">
								<div>{{coll.name}} {{coll.surname}}</div>
								<span class="close" ng-click="delColl($index)" ng-show="coll.email != managerEmail"></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<button type="button" class="btn btn-primary" ng-click="ok();">Ajouter</button>
		<button type="button" class="btn btn-warning" ng-click="cancel()">Annuler</button>
	</div>
</script>



<div ng-controller="listControler">
    <div id="centralPart">
        <div style="margin: 10px">
            <div style="margin: 10px 10px;">
				<div style="float:left;">
					<label>
						<input ng-model="searchText" ng-keypress="keyPressSearch($event)" style="height: 50%;color: #989898;border-radius: 10px 10px" type="text" placeholder=" Research ... " name="Research"  maxlength="10"/>
					</label>
					<img ng-click="goSearch()" style="cursor:pointer;height: 30px;margin:0px 0px 5px 0px" src="/CSS/img/search.svg" alt="Research"/>
					<img ng-click="showSearchSetting = !showSearchSetting" style="cursor:pointer;height: 30px;margin:0px 0px 5px 0px" src="/CSS/img/settings.svg" alt="Paramètre de research"/>
				</div>
				<img ng-click="createProject()" style="float:right;cursor:pointer;height: 30px;margin:0px 0px 5px 0px" src="/Resources/Images/add_icon.png" alt="Ajout de projet"/>
            </div>

            <table class="table tableList" style="table-layout: fixed;">
                <thead>
                <tr>
                    <th ng-repeat="field in showFields" ng-style="(field == orderColumn || '-'+field == orderColumn)?{'background': 'linear-gradient(to right, #00c1fc, #eaeaea)'}:{}">
                        <label ng-click="order(field)" style="cursor:pointer">{{fileds[field].label}}</label>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr ng-repeat="row in dataList | orderBy: orderColumn track by $index" ng-show="inPage($index)" ng-click="goToDetail(row[detailField])">
                    <td ng-repeat="field in showFields">
                        {{row[field]}}
                    </td>
                </tr>
                </tbody>
            </table>

            <div id="pagination">
                <div style="display: inline-block">
                    <a ng-click="goPage(1)" style="border-radius: 3px" href="#">&verbar;&lang;</a>
                    <a ng-click="goPage(pagination.current-1)" style="border-radius: 3px" href="#">&lang;&lang;</a>
                    <a ng-show="pagination.pagesForSelect[0]>1" style="border-radius: 3px" href="#">...</a>
                    <a style="border-radius: 3px" href="#" ng-click="goPage(p)" ng-repeat="p in pagination.pagesForSelect" ng-class="{active:pagination.current == p}"
                    >{{p}}</a>
                    <a ng-show="pagination.pagesForSelect[pagination.pagesForSelect.length-1]<pagination.nbPages" style="border-radius: 3px" href="#">...</a>
                    <a ng-click="goPage(pagination.current+1)" style="border-radius: 3px" href="#">&rang;&rang;</a>
                    <a ng-click="goPage(pagination.nbPages)" style="border-radius: 3px" href="#">&rang;&verbar;	</a>
                    <div style="display: inline-block; margin: 5px 0px 0px 50px;color: black">
                        <select ng-model="pagination.perPage" ng-change="changePerPage()">
                            <option ng-repeat="x in pagination.perPageOptions">{{x}}</option>
                        </select>
                        <label> par page</label>
                    </div>
                    <div style="display: inline-block; margin: 5px 0px 0px 50px;color: black">
                        <label> Total: {{pagination.nbPages}} pages</label>
                    </div>
                </div>
            </div>

            <div id="search_box" ng-show="showSearchSetting" style="background-color:#f8efc0;border-radius:10px; padding: 10px; width: 30%;">
                <div style="text-align: center">
                    <label> Paramètres de research</label>
                </div>
                <hr style="margin: 0px">
                <div style="font-size: 10px;">
                    <div>
                        <label style="display: block;background: linear-gradient(to right, #00b3ee, white)">
                            Researche dans les champs suivants
                        </label>
                        <label ng-repeat="field in allSearchFields">
                            <input type="checkbox" checklist-model="userSearchFields" checklist-value="field" style="margin-left: 15px">
                            {{fileds[field].label}}
                        </label>
                    </div>
                </div>
                <hr style="margin: 0px">
                <div style="float: right; ">
                    <button ng-click="showSearchSetting = !showSearchSetting" style="background-color:#00c1fc;font-size:14px;weight:20px;height: 30px;border-radius: 5px">
                        Enregistrer
                    </button>
                </div>
            </div>

            <div id="filter_box">
                <div id="cli_on" ng-click="showFilters = !showFilters">+</div>
                <div ng-show="showFilters" ng-style="showFilters?{'width':'300px'}:{'right':'30px'}">
                    <div style="text-align: center">
                        <label> Filters </label>
                    </div>
                    <hr style="margin: 0px">
                    <div ng-show="category=='project'" style="font-size: 10px;">
                        <div>
                            <div>
                                <label style="display: block;background: linear-gradient(to right, #00b3ee, white)">
                                    Status </label>
                                <label ng-repeat="status in projectStatus">
                                    <input type="checkbox"  checklist-model="user.projectStatus" checklist-value="status" ng-click="goFilter()">
                                    {{status}}
                                </label>
                            </div>
                            <div>
                                <label style="display: block;background: linear-gradient(to right, #00b3ee, white)">
                                    Terminé depuis </label>
                                <select ng-model="user.projectFinishedFor" ng-change="goFilter()">
                                    <option></option>
                                    <option ng-repeat="x in projectFinishedFor">{{x}}</option>
                                </select>
                                <label> mois</label>
                            </div>
                        </div>
                        <div ng-show="isManager">
                            <label style="display: block;background: linear-gradient(to right, #00b3ee, white)">
                                Responsable </label>
                            <label>
                                <input type="checkbox" ng-model="user.myprojects" ng-click="goFilter()">
                                Mes projets
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="parentDisable" ng-show="showSearchSetting"></div>
</div>

</body>
</html>
