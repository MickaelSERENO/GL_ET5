myApp.controller("TaskModal", function($scope, $uibModalInstance, task, project, tasks)
{
	$scope.task    = task;
	$scope.project = project;
	$scope.tasks   = tasks;

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

	$scope.canAdd = function()
	{
		var startTime = $scope.startDate.getTime() - $scope.startDate.getTimezoneOffset()*60*1000; 
		for(var i = 0; i < $scope.predecessors.length; i++)
			if($scope.fullTasksPred[$scope.predecessors].endDate.getTime() > startTime)
			{
				$scope.errorMsg = "Un des prédécesseur se termine avant la date de début de la tâche";
				return false;
			}

		if($scope.isMarker)
		{
			if($scope.startDate == undefined)
			{
				$scope.errorMsg = "La date de début n'est pas comprehensible";
				return false;
			}

			if(startTime < project.startDate.getTime())
			{
				$scope.errorMsg = "Les dates ne correspondent pas";
				return false;
			}

			if($scope.containDuplicate($scope.predecessors, 1))
			{
				$scope.errorMsg = "Doublon dans la liste des prédécesseurs";
				return false;
			}
		}

		else
		{
			if($scope.startDate == undefined || $scope.endDate == undefined)
			{
				$scope.errorMsg = "Les dates de début ou de fin ne sont pas compréhensibles";
				return false;
			}

			var startTime = $scope.startDate.getTime() - $scope.startDate.getTimezoneOffset()*60*1000; 
			var endTime   = $scope.endDate.getTime()   - $scope.endDate.getTimezoneOffset()*60*1000; 
			if(endTime < startTime || endTime > project.endDate.getTime() || startTime < project.startDate.getTime())
			{
				$scope.errorMsg = "Les dates ne correspondent pas";
				return false;
			}
			
			//Check if we have duplication
			//Predecessors and children
			if($scope.containDuplicate($scope.predecessors, 1))
			{
				$scope.errorMsg = "Doublon dans la liste des prédécesseurs";
				return false;
			}

			else if($scope.containDuplicate($scope.children, 1))
			{
				$scope.errorMsg = "Doublon dans la liste des sous-tâches";
				return false;
			}

			//Check if the mother is not in the children list
			for(var i =0; i < $scope.children.length; i++)
			{
				if($scope.taskMother[$scope.children[i]] == $scope.fullTasks[$scope.mother])
				{
					$scope.errorMsg = "Une tâche parente ne peut être une sous-tâche";
					return false;
				}

				if(levelHierarchy($scope.taskMother[$scope.children[i]]) <= 2)
				{
					$scope.errorMsg = "Une sous-tâche ne peut avoir un niveau de hiérarchie supérieur à 3";
					return false;
				}
			}

			//Check if the predecessor is not in the mother succession list
			if($scope.mother > 0)
			{
				for(var i =0; i < $scope.predecessors.length; i++)
				{
					if($scope.fullTasks[$scope.mother] == $scope.fullTasksPred[$scope.predecessors[i]])
					{
						$scope.errorMsg = "Une relation d'ordre existe entre un des prédécesseur et la tâche parent";
						return false;
					}

					var motherMother = $scope.fullTasks[$scope.mother];
					while(motherMother != null)
					{
						if(orderRelationShip($scope.fullTasksPred[$scope.predecessors[i]], motherMother) || hierarchyChildren($scope.fullTasksPred[$scope.predecessors[i]], motherMother))
						{
							$scope.errorMsg = "Une relation d'ordre existe entre un des prédécesseur et la tâche parent";
							return false;
						}
						motherMother = motherMother.mother;
					}
				}
			}

		}

		return true;
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

	$scope.addTask = function(task)
	{
		if(!task.isMarker)
			$scope.fullTasks.push(task);
		$scope.fullTasksPred.push(task);

		for(var i =0; i < task.children.length; i++)
			$scope.addTask(task.children[i]);
	};

	for(var i =0; i < tasks.length; i++)
		$scope.addTask(tasks[i]);

});
