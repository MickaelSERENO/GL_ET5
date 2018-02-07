<script type="text/ng-template" id="modalTask.html">
	<div class="modal-header">
		<h3 class="modal-title">Gestion tâche</h3>
	</div>
	<div class="modal-body">
		<uib-tabset>
			<uib-tab index="0" heading="Informations" class="tab-content">
				<div id="info">
					<p>Projet : </p>
					<p>Responsable de la tâche : </p>
					<div class="container-fluid">
						<div class="row">
							<div class="col-sm-4">
								<p>Début :</p>
							</div>
							<div class="col-sm-4">
								<p>Fin :</p>
							</div>
							<div class="col-sm-4">
								<p>Charge totale estimée :</p>
							</div>
						</div>
					</div>
					<!------ DESCRIPTION ---->

					<p>Description : </p>
					<!-------->
					<p>Sous-tâches : </p>

					<p>Prédecesseur : </p>

					<div class="modal-footer">
						<button type="button" class="btn btn-primary col-sm-2 col-sm-offset-5" ng-click="modify()">Modifier</button>
						<button type="button" class="btn btn-primary col-sm-2 col-sm-offset-5" ng-click="delete()">Supprimer</button>
					</div>
				</div>
			</uib-tab>
			<uib-tab heading="Avancement" index="1">
				<div id="avancement">
					<p>Avancement :</p>
					<p>Charge consommée :</p>
					<p>Reste à faire :</p>

					<div class="container-fluid">
						<div class="row">
							<div class="col-sm-4">
								<p>Charge totale estimée :</p>
							</div>
							<div class="col-sm-4">
								<p>Charge totale calculée :</p>
							</div>
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-primary col-sm-2 col-sm-offset-5" ng-click="grab()">Saisir</button>
						<button type="button" class="btn btn-primary col-sm-2 col-sm-offset-5" ng-click="modify()">Modifier</button>
					</div>
				</div>
			</uib-tab>
		</uib-tabset>
	</div>
</script>
