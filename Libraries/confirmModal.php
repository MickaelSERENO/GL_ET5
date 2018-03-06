<script type="text/ng-template" id="confirmModal.html">
	<div class="modal-header">
		<h3>{{title}}</h3>
	</div>
	<div class="modal-body">
		<center><p>{{textContent}}</p></center>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-primary" ng-click="ok()">OK</button>
		<button type="button" class="btn btn-warning" ng-click="cancel()">Annuler</button>
	</div>
</script>
