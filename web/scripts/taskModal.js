myApp.controller("TaskModal", function($scope, $uibModalInstance, task, project)
{
	$scope.task = task;
	$scope.project = project;

	$scope.dateFormat  = "dd/MM/yyyy";
	$scope.dateOptions =
	{
		formatYear: 'yy',
		maxDate: project.endDate,
		minDate: project.startDate,
		startingDay: 1
	};
	$scope.currentDate = task.startDate;
	$scope.startDate   = new Date();
	$scope.endDate     = new Date();


	$scope.popupStartDate = {opened : false};
	$scope.popupEndDate   = {opened : false};

	$scope.isMarker     = false;

	$scope.name         = "";
	$scope.description  = "";

	$scope.initCharge   = 0;
	$scope.currentCol   = 0;

	$scope.fullTasks     = [{name : "\"Vide\"", id:"NULL"}];
	$scope.fullTasksPred = [{name : "\"Vide\"", id:"NULL"}];
	$scope.taskMother    = [{name : "\"Vide\"", id:"NULL"}].concat(task);
	$scope.predecessors  = [];
	$scope.mother        = 0;
	$scope.children      = [];


	$scope.currentCol    = 0;

	$scope.errorMsg      = "";
	$scope.showMsg       = false;

	$scope.openStartDate = function()
	{
		$scope.popupStartDate.opened = true;
	};

	$scope.openEndDate = function()
	{
		$scope.popupEndDate.opened = true;
	};

	$scope.IsVisible = false;
	$scope.ShowHide = function () {
		//If DIV is visible it will be hidden and vice versa.
		$scope.IsVisible = $scope.IsVisible ? false : true;
	}

	$scope.delete = function()
	{
		$uibModalInstance.dismiss();
	};


	$scope.clickCollaborators = function(i)
	{
		$scope.currentCol = i;
	};

	$scope.clickMother = function(i)
	{
		$scope.mother = i;
	};
	$scope.clickPredecessor = function(i)
	{
		if(i != 0)
		$scope.predecessors.push(i);
	};

	$scope.delPredecessor = function(i)
	{
		$scope.predecessors.splice(i, 1);
	};

	$scope.delChild = function(i)
	{
		$scope.children.splice(i, 1);
	};

	$scope.clickChildren = function(i)
	{
		if(i != 0)
		$scope.children.push(i);
	};

	$scope.containDuplicate = function(arr, index=0)
	{
		for(var i = 0; i < arr.length; i++)
		for(var j = i+1; j < arr.length; j++)
		if(arr[i] == arr[j])
		return true;
		return false;
	};


	$scope.inactive = true;
	$scope.modifyText = 'Modifier';
	$scope.modify = function () {
		if ($scope.modifyText == "Modifier"){
			$scope.modifyText = "Valider";
		}else if($scope.modifyText == "Valider"){
			$scope.modifyText = "Sauvegarde en cours...";
			$scope.inactive = false;
		}
	}


});
