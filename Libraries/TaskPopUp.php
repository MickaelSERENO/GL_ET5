<script type="text/javascript">
	var email = <?= "'".$_SESSION['email']."'" ?>;
	var rank  = <?= $_SESSION['rank'] ?>;
</script>
<script type="text/javascript" src="/scripts/taskModal.js"></script>
<script type="text/ng-template" id="modalTask.html">
<div class="modal-header">
	<h3 class="modal-title">Gestion tâche : {{name}}</h3>
</div>
<div class="modal-body">
	<uib-tabset>
		<uib-tab index="0" heading="Informations" class="tab-content">
			<div id="info">
				<div class="row topSpace">

					<div class="col-lg-9">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Nom de la tâche :</span>
							<input type="text" class="form-control" ng-disabled="inactive"  placeholder="taskname" aria-describedby="basic-addon2" ng-model="name" ng-change="changeName(name)"></input>

						</div>
						
					</div>


				</div>
				<!-- DEBUT ROW-->
				<div class="row topSpace">
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Projet :</span>
							<input type="text" class="form-control" ng-disabled=true  placeholder="projectname" aria-describedby="basic-addon2" value="{{project.name}}"></input>
						</div>
					</div>

					<div class="col-md-4" ng-show = "IsVisible">
						Jalon : <input type="checkbox" ng-model="isMarker" ng-disable=true></input>
					</div>
				</div>

				<!-- DEBUT ROW-->
				<div class="row topSpace">
					<div class="col-md-8">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Responsable de la tâche :</span>
							<input type="text" class="form-control" ng-disabled="true" value="{{collaborators[currentColl].email}}"></input>
						</div>
					</div>
					<div class="col-md-4 btn-group" uib-dropdown dropdown-append-to-body ng-show="!inactive && children.length==0">
						<button type="button" class="btn btn-primary" uib-dropdown-toggle>
							<span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
						</button>
						<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
							<li role="menuitem" ng-repeat="c in collaborators" ng-click="clickCollaborators($index)"><a href="">{{c.name}}</a></li>
						</ul>
					</div>
					
				</div>


				<div class="row topSpace">
					<div class="col-md-4">
						Début :
						<p class="input-group">
							<input type="text" class="form-control" ng-disabled="inactive || children.length > 0" uib-datepicker-popup="{{dateFormat}}" ng-model="task.startDate" is-open="popupStartDate.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
							<span class="input-group-btn">
								<button type="button" class="btn btn-default" ng-show = "!(inactive || children.length > 0)" ng-click="openStartDate()"><i class="glyphicon glyphicon-calendar"></i></button>
							</span>
						</p>
					</div>

					<div class="col-md-4" ng-show="!isMarker">
						Fin :
						<p class="input-group">
							<input type="text" class="form-control" ng-disabled="inactive || children.length > 0" uib-datepicker-popup="{{dateFormat}}" ng-model="task.endDate" is-open="popupEndDate.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
							<span class="input-group-btn">
								<button type="button" class="btn btn-default" ng-show = "!(inactive || children.length > 0)" ng-click="openEndDate()"><i class="glyphicon glyphicon-calendar"></i></button>
							</span>
						</p>
					</div>
				</div>

				<div class="row topSpace" ng-show="!isMarker">
					<div class="col-lg-7">
						<div class="input-group">
							<span class="input-group-addon"  id="basic-addon2">Charge totale estimée :</span>
							<input type="text" class="form-control" ng-disabled="inactive" placeholder="estimate1" aria-describedby="basic-addon2" value="{{task.initCharge}}"></input>
							<span class="input-group-addon" id="basic-addon2">jour(s)</span>
						</div>
					</div>
				</div>

				<!------ DESCRIPTION ---->
				<div class="row topSpace">
					<div class="col-lg-10">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Description :</span>
							<textarea class="form-control" id="descriptionTextarea" rows="3" ng-disabled="inactive" ng-model="description"ng-change="changeDesc(description)"></textarea>
						</div>
					</div>
				</div>

				<!-------->
				<div class="row topSpace" ng-show="!isMarker">
					<div class="col-md-12">
						<div>Tâche parente :</div>
						<div class="btn-group" uib-dropdown dropdown-append-to-body ng-show="!inactive">
							<button type="button" class="btn btn-primary" uib-dropdown-toggle>
							{{fullTasks[mother].name}}<span class="caret sortList"></span>
							</button>
							<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
								<li role="menuitem" ng-repeat="t in fullTasks" ng-click="clickMother($index)"><a href="">{{t.name}}</a></li>
							</ul>
						</div>
					</div>
				</div>

				<!-------->
				<div class="row topSpace" ng-show="!isMarker">
					<div class="col-md-12">
						<div>Sous-tâche(s) :</div>

						<ul class="list-inline listSpaceRight">
							<li ng-repeat="t in fixChildren track by $index">
								<div>
									<div>{{t.name}}</div>
								</div>
							</li>

							<li ng-repeat="t in children track by $index">
								<div class="closeWrapper">
									<div>{{taskMother[t].name}}</div>
									<span class="close" ng-click="delChild($index)" ng-show="!inactive"></span>
								</div>
							</li>

							<li>
								<div class="btn-group" uib-dropdown dropdown-append-to-body ng-show="!inactive">
									<button type="button" class="btn btn-primary" uib-dropdown-toggle>
										"Vide"<span class="caret sortList"></span>
									</button>
									<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
										<li role="menuitem" ng-repeat="t in taskMother" ng-click="clickChildren($index)"><a href="">{{t.name}}</a></li>
									</ul>
								</div>
							</li>
						</ul>
					</div>
				</div>


			<div class="row topSpace">
				<div class="col-md-12">
					<div>Prédecesseur(s) :</div>

					<ul class="list-inline listSpaceRight">
						<li ng-repeat="t in predecessors track by $index">
							<div class="closeWrapper">
								<div>{{fullTasksPred[t].name}}</div>
								<span class="close" ng-show="!inactive" ng-click="delPredecessor($index)"></span>
							</div>
						</li>
						<li>
							<div class="btn-group" uib-dropdown dropdown-append-to-body ng-show="!inactive">
								<button type="button" class="btn btn-primary" uib-dropdown-toggle>
									"Vide"<span class="caret sortList"></span>
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

		<div class="modal-footer" ng-show="isManager">
			<div ng-show="showMsg">{{errorMsg}}</div>
			<button type="button" class="btn btn-primary" ng-click="modify();">{{ modifyText }} </button>
			<button type="button" class="btn btn-warning" ng-click="delete()">Supprimer</button>
		</div>
	</uib-tab>

	<uib-tab heading="Avancement" index="1">
		<form name="forms.advForm">
			<div id="avancement">
				<div class="row topSpace">
					<div class="col-xs-6">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Avancement :</span>
							<input type="text" ng-disabled="inactiveAdv" name="advancement" ng-model="advancement" ng-change="changeAdvancement(advancement);"></input>
							<span class="input-group-addon" id="basic-addon2">%</span>
						</div>
					</div>

					<div class="col-xs-4">
						<uib-progressbar value="advancement" id="advProgressbar"><span style="color:white; white-space:nowrap;">{{advancement}} %</span></uib-progressbar>
					</div>
				</div>

				<div class="row topSpace">
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Charge consommée : </span>
							<input type="text" ng-disabled="inactiveAdv" ng-model="chargeConsumed" ng-change="changeChargeConsumed(chargeConsumed);"></input>
							<span class="input-group-addon" id="basic-addon2">jour(s)</span>
						</div>
						
					</div>
				</div>

				<div class="row topSpace">
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Reste à faire :</span>
							<input type="number" class="form-control" ng-disabled=true  placeholder="Resteafaire" aria-describedby="basic-addon2" ng-model="remaining"></input>
							<span class="input-group-addon" id="basic-addon2">jour(s)</span>
						</div>
						
					</div>
				</div>

				<div class="row topSpace">
					<div class="col-lg-7">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Charge totale estimée :</span>
							<input type="text" ng-disabled="inactiveAdv" ng-model="initCharge" ng-change="changeInitCharge(initCharge);">
							<span class="input-group-addon" id="basic-addon2">jour(s)</span>
						</div>
						
					</div>

					<div class="col-lg-7">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Charge totale calculée :</span>
							<input type="text" class="form-control" ng-disabled=true ng-model="computedCharge"></input>
							<span class="input-group-addon" id="basic-addon2">jour(s)</span>
						</div>
					</div>
				</div>
			</div>
		</form>

		<div class="modal-footer">
			<button type="button" class="btn btn-primary" ng-show="isManager" ng-click="modifyAdv();">{{ modifyTextAdv }} </button>
			<button type="button" class="btn btn-primary" ng-show="modifyIndexAdv == 0 && (isManager || task.collaboratorEmail == email)" ng-click="grab()">Saisir</button>
			<button type="button" class="btn btn-warning" ng-show="modifyIndexAdv == 1" ng-click="cancelModifyAdv();">Annulé</button>
		</div>
	</uib-tab>
</uib-tabset>

</script>
