<script type="text/javascript" src="/scripts/taskModal.js"></script>
<script type="text/javascript" src="/scripts/ganttProject.js"></script>
<script type="text/ng-template" id="modalTask.html">
	<div class="modal-header">
		<h3 class="modal-title">Gestion tâche : {{task.name}}</h3>
	</div>
	<div class="modal-body">
		<uib-tabset>
			<uib-tab index="0" heading="Informations" class="tab-content">
				<div id="info">
					<div class="row topSpace">

						<div class="col-lg-9">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon2">Nom de la tâche :</span>
								<input type="text" class="form-control" ng-disabled="inactive"  placeholder="taskname" aria-describedby="basic-addon2" value="{{task.name}}">

							</div>
							<br>
						</div>


					</div>
					<!-- DEBUT ROW-->
					<div class="row topSpace">

						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon2">Projet :</span>
								<input type="text" class="form-control" ng-disabled=true  placeholder="projectname" aria-describedby="basic-addon2" value="<?= $projectInfo->name ?>">

							</div>
							<br>
						</div>

						<div class="col-md-4" ng-show = "IsVisible">
							Jalon : <input type="checkbox" ng-model="isMarker"></input>
						</div>

					</div>



					<!-- DEBUT ROW-->
					<div class="row topSpace">

						<div class="col-lg-11">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon2">Responsable de la tâche :</span>
								<input type="text" class="form-control" ng-disabled="inactive"  placeholder="taskresp" aria-describedby="basic-addon2" value="{{task.collaboratorEmail}}">
								<div class="input-group-btn">

									<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
									</button>
									<div class="dropdown-menu dropdown-menu-right">
										<a class="dropdown-item" href="#">Action</a>
										<a class="dropdown-item" href="#">Another action</a>
										<a class="dropdown-item" href="#">Something else here</a>
										<div role="separator" class="dropdown-divider"></div>
										<a class="dropdown-item" href="#">Separated link</a>
									</div>
								</div>
							</div>
							<br>
						</div>


					</div>


					<div class="row topSpace">
						<div class="col-md-4">
							Début :
							<p class="input-group">
								<input type="text" class="form-control" ng-disabled="inactive" uib-datepicker-popup="{{dateFormat}}" ng-model="task.startDate" is-open="popupStartDate.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
								<span class="input-group-btn">
									<button type="button" class="btn btn-default" ng-show = "IsVisible" ng-click="openStartDate()"><i class="glyphicon glyphicon-calendar"></i></button>
								</span>
							</p>
						</div>

						<div class="col-md-4" ng-show="!isMarker">
							Fin :
							<p class="input-group">
								<input type="text" class="form-control" ng-disabled="inactive" uib-datepicker-popup="{{dateFormat}}" ng-model="task.endDate" is-open="popupEndDate.opened" datepicker-options="dateOptions" ng-required="true" close-text="Fermer" clear-text="Effacer" current-text="Aujourd'hui"/>
								<span class="input-group-btn">
									<button type="button" class="btn btn-default" ng-show = "IsVisible" ng-click="openEndDate()"><i class="glyphicon glyphicon-calendar"></i></button>
								</span>
							</p>
						</div>
					</div>

					<div class="row topSpace">
						<div class="col-lg-7">
							<div class="input-group">
								<span class="input-group-addon"  id="basic-addon2">Charge totale estimée :</span>
								<input type="text" class="form-control" ng-disabled="inactive" placeholder="estimate1" aria-describedby="basic-addon2" value="{{task.initCharge}}">
								<span class="input-group-addon" id="basic-addon2">jour(s)</span>
							</div>
							<br>
						</div>


					</div>

					<!------ DESCRIPTION ---->
					<div class="row topSpace">
						<div class="col-lg-10">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon2">Description :</span>
								<textarea class="form-control" id="descriptionTextarea" rows="3" ng-disabled="inactive">{{task.description}}</textarea>
							</div>
							<br>
						</div>



					</div>
					<!-------->
					<div class="row topSpace">
						<div class="col-md-12">
							<div class="col-xs-3">Sous-tâche(s) :</div>

							<ul class="list-inline listSpaceRight">
								<li ng-repeat="t in children track by $index">
									<div class="closeWrapper">
										<div>{{taskMother[t].name}}</div>
										<span class="close" ng-click="delChild($index)"></span>
									</div>
								</li>

								<li>
									<div class="btn-group" uib-dropdown dropdown-append-to-body>
										<div class="col-xs-7" ng-show = "IsVisible"><button type="button" class="btn btn-primary" uib-dropdown-toggle>
											<i class="glyphicon glyphicon-plus"></i>
										</button>
									</div>
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
						<div class="col-xs-3">Prédecesseur(s) :</div>

						<ul class="list-inline listSpaceRight">
							<li ng-repeat="t in predecessors track by $index">
								<div class="closeWrapper">
									<div>{{fullTasksPred[t].name}}</div>
									<span class="close" ng-click="delPredecessor($index)"></span>
								</div>
							</li>
							<li>
								<div class="btn-group" uib-dropdown dropdown-append-to-body>
									<div class="col-xs-6" ng-show = "IsVisible"><button type="button" class="btn btn-primary" uib-dropdown-toggle>
										<i class="glyphicon glyphicon-plus"></i>
									</button>
								</div>
							</li>
						</ul>
					</div>

				</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-primary" ng-click="inactive = !inactive;modify();ShowHide()">{{ modifyText }} </button>
				<button type="button" class="btn btn-warning" ng-click="delete()">Supprimer</button>
			</div>
		</uib-tab>

		<uib-tab heading="Avancement" index="1">
			<div id="avancement">
				<div class="row topSpace">

					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Avancement :</span>
							<input type="text" class="form-control" ng-disabled="inactive"  placeholder="Avancement" aria-describedby="basic-addon2" value="{{task.advancement}}">
							<span class="input-group-addon" id="basic-addon2">%</span>
						</div>
						<br>
					</div>

					<div class="col-xs-4">
						<uib-progressbar value="task.advancement" id="advProgressbar"><span style="color:white; white-space:nowrap;">{{task.advancement}} %</span></uib-progressbar>
					</div>



				</div>

				<div class="row topSpace">
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Charge consommée :</span>
							<input type="text" class="form-control" ng-disabled="inactive"  placeholder="Resteafaire" aria-describedby="basic-addon2" value="{{task.chargeConsumed}}">
							<span class="input-group-addon" id="basic-addon2">jour(s)</span>
						</div>
						<br>
					</div>
				</div>



				<div class="row topSpace">
					<div class="col-lg-6">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Reste à faire :</span>
							<input type="text" class="form-control" ng-disabled=true  placeholder="Resteafaire" aria-describedby="basic-addon2" value="{{task.remaining}}">
							<span class="input-group-addon" id="basic-addon2">jour(s)</span>
						</div>
						<br>
					</div>
				</div>

				<div class="row topSpace">
					<div class="col-lg-7">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Charge totale estimée :</span>
							<input type="text" class="form-control" ng-disabled="inactive"  placeholder="estimate" aria-describedby="basic-addon2" value="{{task.initCharge}}">
							<span class="input-group-addon" id="basic-addon2">jour(s)</span>
						</div>
						<br>
					</div>

					<div class="col-lg-7">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon2">Charge totale calculée :</span>
							<input type="text" class="form-control" ng-disabled=true placeholder="estimate" aria-describedby="basic-addon2" value="{{task.computedCharge}}">
							<span class="input-group-addon" id="basic-addon2">jour(s)</span>
						</div>
						<br>
					</div>
				</div>

			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-primary" ng-click="grab()">Saisir</button>
				<button type="button" class="btn btn-warning" ng-click="inactive = !inactive;modify();ShowHide()">{{ modifyText }} </button>
			</div>
		</div>
	</uib-tab>
</uib-tabset>

</script>
