<?php
session_start();

//$_SESSION["email"] = 'jean.dupont@email.com';
//$_SESSION["email"] = 'stacy.gromat@email.com';
//$_SESSION["email"] = 'karine.legros@woodcorp.com';

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
    <script type="text/javascript" src="/scripts/bower_components/angular/angular.js"></script>
    <script type="text/javascript" src="/scripts/bower_components/angular/checklist-model.js"></script>
    <script type="text/javascript" src="/scripts/connection.js"></script>
    <script type="text/javascript" src="/scripts/list.js"></script>

    <link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/CSS/style.css">
</head>

<body ng-app="myApp" ng-init="category='task';">
<header class="headerConnected">
			<?php include('../Header/header.php'); ?>
		</header>
<div ng-controller="listControler">
    <div id="centralPart">
        <div style="margin: 10px">
            <div style="margin: 10px 10px">
                <label>
                    <input ng-model="searchText" ng-keypress="keyPressSearch($event)" style="height: 50%;color: #989898;border-radius: 10px 10px" type="text" placeholder=" Research ... " name="Research"  maxlength="10">
                </label>
                <img ng-click="goSearch()" style="cursor:pointer;height: 30px;margin:0px 0px 5px 0px" src="/CSS/img/search.svg" alt="Research">
                <img ng-click="showSearchSetting = !showSearchSetting" style="cursor:pointer;height: 30px;margin:0px 0px 5px 0px" src="/CSS/img/settings.svg" alt="Paramètre de research">
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
                    <div ng-show="category=='task'" style="font-size: 10px;">
                        <div ng-show="isCollaborator">
                            <div>
                                <label style="display: block;background: linear-gradient(to right, #00b3ee, white)">
                                    Status </label>
                                <label ng-repeat="status in taskStatus">
                                    <input type="checkbox"  checklist-model="user.taskStatus" checklist-value="status" ng-click="goFilter()">
                                    {{status}}
                                </label>
                            </div>
                            <div>
                                <label style="display: block;background: linear-gradient(to right, #00b3ee, white)">
                                    &nbsp; </label>
                                <label>
                                    dans projet terminé depuis </label>
                                <br />
                                <select ng-model="user.taskProjectFinishedFor" ng-change="goFilter()">
                                    <option></option>
                                    <option ng-repeat="x in taskProjectFinishedFor">{{x}}</option>
                                </select>
                                <label> mois</label>
                            </div>
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
