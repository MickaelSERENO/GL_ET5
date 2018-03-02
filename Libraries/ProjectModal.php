<!-- Collaborator modal -->
<script type="text/javascript" src="/scripts/projectModal.js"></script>
<script type="text/ng-template" id="modalAddColl.html">
	<div class="modal-header">
		<h3 class="modal-title">Ajout de collaborateur</h3>
	</div>

	<div class="modal-body">

		<div style="margin: 10px 10px">
			<label>
				<input ng-model="searchText" ng-keypress="keyPressSearch($event)" style="height: 50%;color: #989898;border-radius: 10px 10px" type="text" placeholder=" Recherche... " name="Recherche">
			</label>
			<img ng-click="goSearch()" style="cursor:pointer;height: 30px;margin:0px 0px 5px 0px" src="/CSS/img/search.svg" alt="Research"/>
		</div>

		<table class="table tableList" style="table-layout: fixed;">
			<thead>
				<tr>
					<th ng-repeat="field in showFields" ng-style="(field == orderColumn || '-'+field == orderColumn)?{'background': 'linear-gradient(to right, #00c1fc, #eaeaea)'}:{}">
						<label ng-click="order(field)" style="cursor:pointer">{{fields[field].label}}</label>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr ng-repeat="row in dataList | orderBy: orderColumn track by $index" ng-show="inPage($index)" ng-dblclick="ok()" ng-click="selectColl($index)" ng-style="(row == currentColl) ? {'background-color': 'grey'}:{}">
					<td ng-repeat="field in showFields">
						{{row[field]}}
					</td>
				</tr>
			</tbody>
		</table>


		<div class="pagination">
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



	</div>

	<div class="modal-footer">
		<button type="button" class="btn btn-primary" ng-click="ok();">{{okText}}</button>
		<button type="button" class="btn btn-warning" ng-click="cancel()">Annuler</button>
	</div>
</script>
