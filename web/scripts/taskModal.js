myApp.controller("TaskModal", function($scope, $uibModalInstance, task, project, tasks, colls, $uibModal)
{
	$scope.collaborators = [new EndUser({name : "\"Vide\"", surname : "", email : "NULL"})].concat(colls);

	$scope.currentColl = 0;
	for(var i = 0; i < $scope.collaborators.length; i++)
		if($scope.collaborators[i].email == task.collaboratorEmail)
		{
			$scope.currentColl = i;
			break;
		}

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
	$scope.currentDate = new Date();
	$scope.startDate   = task.startDate;
	$scope.endDate     = task.endDate;

	$scope.popupStartDate = {opened : false};
	$scope.popupEndDate   = {opened : false};

	$scope.isMarker     = false;

	$scope.name         = task.name;
	$scope.description  = task.description;

	$scope.forms = {};

	$scope.fullTasks     = [{name : "\"Vide\"", id:"-1"}];
	$scope.fullTasksPred = [{name : "\"Vide\"", id:"-1"}];
	$scope.taskMother    = [{name : "\"Vide\"", id:"-1"}];
	for(var i = 0; i < task.children.length; i++)
		if(task.children[i].counted)
			$scope.taskMother.push(task.children[i]);

	for(var i = 0; i < tasks.length; i++)
		if(!tasks[i].isMarker)
			$scope.taskMother.push(tasks[i]);

	console.log($scope.taskMother);

	$scope.predecessors  = [];
	$scope.mother        = 0;
	$scope.children      = [];

	$scope.fixChildren   = [];
	for(var i = 0; i < task.children.length; i++)
		if(task.children[i].counted == false)
			$scope.fixChildren.push(task.children[i]);

	$scope.errorMsg      = "";
	$scope.showMsg       = false;

	$scope.initCharge     = task.initCharge;
	$scope.advancement    = task.advancement;
	$scope.remaining      = task.remaining;
	$scope.chargeConsumed = task.chargeConsumed;
	$scope.computedCharge = task.computedCharge;

	$scope.openStartDate = function()
	{
		$scope.popupStartDate.opened = true;
	};

	$scope.openEndDate = function()
	{
		$scope.popupEndDate.opened = true;
	};

	$scope.inactiveAdv    = true;
	$scope.modifyTextAdv  = "Modifier";
	$scope.modifyIndexAdv = 0;
	$scope.modifyAdv = function()
	{
		if ($scope.modifyIndexAdv == 0){
			$scope.modifyIndexAdv = 1;
			$scope.modifyTextAdv = "Valider";
			$scope.inactiveAdv = false;
		}else if($scope.modifyIndexAdv == 1){
			$scope.modifyIndexAdv = 2;
			$scope.modifyTextAdv = "Sauvegarde en cours...";
			$scope.inactiveAdv = true;
			$scope.sendAdv();
		}
	};

	$scope.inactive    = true;
	$scope.modifyText  = 'Modifier';
	$scope.modifyIndex = 0;
	$scope.modify = function () {
		if ($scope.modifyIndex == 0){
			$scope.modifyIndex = 1;
			$scope.modifyText = "Valider";
			$scope.inactive = false;
		}else if($scope.modifyIndex == 1){
			$scope.modifyTask();
		}
	};

	$scope.modifyTask = function()
	{
		if(!$scope.canAdd())
		{
			$scope.showMsg = true;
			console.log("arf");
			$scope.inactive = false;
		}
		else
		{
			$scope.inactive = true;
			$scope.modifyIndex = 2;
			$scope.modifyText = "Sauvegarde en cours...";

			var startTime    = ($scope.startDate.getTime() - $scope.startDate.getTimezoneOffset()*60*1000)/1000; 
			var endTime      = ($scope.endDate.getTime()   - $scope.endDate.getTimezoneOffset()*60*1000)/1000; 
			var predList     = [];
			var childrenList = [];
			for(var i = 0; i < $scope.predecessors.length; i++)
				predList.push($scope.fullTasksPred[$scope.predecessors[i]].id);

			for(var i = 0; i < $scope.children.length; i++)
				childrenList.push($scope.taskMother[$scope.children[i]].id);

			var pred         = encodeURIComponent(JSON.stringify(predList));
			var children     = encodeURIComponent(JSON.stringify(childrenList));

			var httpCtx = new XMLHttpRequest();
			httpCtx.onreadystatechange = function()
			{
				//Check for errors
				if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
				{
					$scope.modifyIndex = 0;
					$scope.modifyText  = "Modifier";
					if(httpCtx.responseText != "-1")
					{
					}
					else
						$uibModalInstance.dismiss();
				}
			};

			console.log($scope.description);

			if($scope.isMarker)
			{
				httpCtx.open('GET', "/AJAX/changeTask.php?isMarker=1&projectID="+$scope.project.id+"&startDate="+startTime+"&name="+encodeURIComponent($scope.name)+"&description="+encodeURIComponent($scope.description)+"&predecessors="+pred+"&taskID="+$scope.task.id, true);
			}
			else
			{
				httpCtx.open('GET', "/AJAX/changeTask.php?isMarker=0&projectID="+$scope.project.id+"&name="+encodeURIComponent($scope.name)+"&startDate="+startTime+"&endDate="+endTime+"&collEmail="+encodeURIComponent($scope.collaborators[$scope.currentColl].email)+"&mother="+$scope.taskMother[$scope.mother].id+"&description="+encodeURIComponent($scope.description)+"&predecessors="+pred+"&children="+children+"&taskID="+$scope.task.id, true);
			}
			httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			httpCtx.send(null);
		}
	};

	$scope.sendAdv = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			//Check for errors
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				$scope.modifyIndexAdv = 0;
				$scope.modifyTextAdv  = "Modifier";
				if(httpCtx.responseText != "-1")
				{
				}
				else
					$uibModalInstance.dismiss();
			}
		};
		httpCtx.open('GET', "/AJAX/chargeTask.php?request=0&taskID="+$scope.task.id+"&advancement="+$scope.advancement+"&chargeConsumed="+$scope.chargeConsumed+"&remaining="+$scope.remaining+"&initCharge="+$scope.initCharge+"&computedCharge="+$scope.computedCharge, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);

	};

	$scope.delete = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			//Check for errors
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != "-1")
					$uibModalInstance.close();
				else
					$uibModalInstance.dismiss();
			}
		};
		httpCtx.open('GET', "/AJAX/deleteTask.php?taskID="+$scope.task.id, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};


	$scope.clickCollaborators = function(i)
	{
		$scope.currentColl = i;
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
		$scope.updateDate();
	};

	$scope.clickChildren = function(i)
	{
		if(i != 0)
			$scope.children.push(i);
		$scope.updateDate();
	};

	$scope.updateDate = function()
	{
		var startTime = $scope.startDate.getTime();
		var endTime   = $scope.endDate.getTime();

		for(var i = 0; i < $scope.children.length; i++)
		{
			if(startTime > $scope.taskMother[$scope.children[i]].startDate.getTime())
				$scope.startTime = $scope.taskMother[$scope.children[i]].startDate.getTime();

			if(endTime < $scope.taskMother[$scope.children[i]].endDate.getTime())
				$scope.endTime = $scope.taskMother[$scope.children[i]].endDate.getTime();
		}

		for(var i = 0; i < $scope.fixChildren.length; i++)
		{
			if(startTime > $scope.fixChildren[i].startDate.getTime())
				$scope.startTime = $scope.fixChildren[i].startDate.getTime();

			if(endTime < $scope.fixChildren[i].endDate.getTime())
				$scope.endTime = $scope.fixChildren[i].endDate.getTime();
		}


		$scope.startDate = new Date(startTime);
		$scope.endDate   = new Date(endTime);
		
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

				if(levelHierarchy($scope.taskMother[$scope.children[i]]) >= 2)
				{
					$scope.errorMsg = "Une sous-tâche ne peut avoir un niveau de hiérarchie supérieur à 3";
					return false;
				}
			}

			//Check if a predecessor is not a child
			for(var i=0; i < $scope.children.length; i++)
				for(var j=0; j < $scope.predecessors.length; j++)
					if($scope.taskMother[$scope.children[i]] == $scope.fullTasksPred[$scope.predecessors[i]])
					{
						$scope.errorMsg = "Une sous-tâche ne peut être prédécesseur";
						return false;
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

	$scope.addTask = function(t)
	{
		console.log(t.id);
		if(t.id != $scope.task.id)
		{
			if(!t.isMarker)
				$scope.fullTasks.push(t);
			$scope.fullTasksPred.push(t);
		}

		for(var i =0; i < t.children.length; i++)
			$scope.addTask(t.children[i]);
	};

	$scope.deleteTask = function(currentTask, taskToDelete)
	{
		for(var i =0; i < currentTask.successors.length; i++)
			if(currentTask.successors[i].id == taskToDelete.id)
				currentTask.successors.splice(i, 1);

		if(currentTask.mother == taskToDelete)
			currentTask.mother = null;

		for(var i = 0; i < currentTask.children.length; i++)
		{
			if(currentTask.children[i].id == taskToDelete.id)
			{
				currentTask.children.splice(i, 1);
			}
			else
				$scope.deleteTask(currentTask.children[i], taskToDelete);
		}

	};

	//Need to delete this task in the tree task
	for(var i =0; i < tasks.length; i++)
		$scope.deleteTask(tasks[i], $scope.task);

	for(var i =0; i < $scope.tasks.length; i++)
		$scope.addTask($scope.tasks[i]);

	//Fill predecessors
	for(var i =0; i < $scope.task.predecessors.length; i++)
		for(var j =0; j < $scope.fullTasksPred.length; j++)
			if($scope.task.predecessors[i].id == $scope.fullTasksPred[j].id)
				$scope.predecessors.push(j);

	//Fill children
	for(var i =0; i < $scope.task.children.length; i++)
		if($scope.task.children[i].counted)
			for(var j=0; j < $scope.taskMother.length; j++)
				if($scope.task.children[i].id == $scope.taskMother[j].id)
					$scope.children.push(j);

	if($scope.task.mother != null)
		for(var i = 0; i < $scope.fullTasks.length; i++)
			if($scope.task.mother.id == $scope.fullTasks[i].id)
				$scope.mother = i;

	$scope.isManager = (rank==2 || project.managerEmail == email);

	$scope.grab = function()
	{
		$scope.opts = 
		{
			backdrop : true,
			backdropClick : true,
			dialogFade : false,
			keyboard : true,
			templateUrl : "modalAdv.html",
			controller : "AdvModal",
			controllerAs : "$ctrl",
			resolve : {task    : function() {return $scope.task;}
					  }
		};

		var modalInstance = $uibModal.open($scope.opts);
		modalInstance.result.then(
			function(taskCpy) //ok
			{
				$scope.task.advancement    = taskCpy.advancement;
				$scope.task.remaining      = taskCpy.remaining;
				$scope.task.chargeConsumed = taskCpy.chargeConsumed;
			}, 
			function() //cancel
			{
			});
	};

    $scope.changeChargeConsumed = function(newValue)
    {
        if(newValue < 0)
            newValue = 0;

		$scope.chargeConsumed = parseInt(newValue);
		if($scope.chargeConsumed == undefined)
			$scope.chargeConsumed = 0;

		if($scope.chargeConsumed == 0)
			$scope.advancement = 0;

        //Compute remaining and computedCharge
		if($scope.advancement == 0)
		{
			$scope.remaining      = $scope.initCharge - $scope.chargeConsumed;
			$scope.computedCharge = $scope.initCharge;
		}
		else
		{
			$scope.remaining      = parseInt((1.0-$scope.advancement/100.0)*$scope.chargeConsumed / ($scope.advancement/100.0));
			$scope.computedCharge = $scope.remaining + $scope.chargeConsumed;
		}
    };

    $scope.changeAdvancement = function(newValue)
    {
        if(newValue < 0)
            newValue = 0;
        else if(newValue > 100)
            newValue = 100;
		newValue = parseInt(newValue);
		if(newValue == undefined)
			newValue = 0;

		$scope.advancement = newValue; 
		console.log("modified");

		if($scope.advancement == 0)
			$scope.remaining      = $scope.task.initCharge - $scope.task.chargeConsumed;
		else
			$scope.remaining      = parseInt((1.0-$scope.advancement/100.0)*$scope.chargeConsumed / ($scope.advancement/100.0));
        $scope.computedCharge = $scope.remaining + $scope.chargeConsumed;
    };

	$scope.changeInitCharge = function(newValue)
	{
		if(newValue < 1)
			newValue = 1;
		if(newValue < $scope.chargeConsumed)
			newValue = $scope.chargeConsumed;
		newValue              = parseInt(newValue);
		if(newValue == undefined)
			newValue = ($scope.chargeConsumed < 1) ? 1 : $scope.chargeConsumed;

		$scope.initCharge     = newValue;
		$scope.computedCharge = newValue;
		$scope.remaining      = $scope.initCharge - $scope.chargeConsumed
		$scope.advancement    = parseInt($scope.chargeConsumed / (1.0*newValue)*100);

		$scope.forms.advForm.advancement.$$scope.advancement = $scope.advancement;
	};

	$scope.cancelModifyAdv = function()
	{
		modifyIndexAdv = 0;
		modifyText     = "Modifier";
	};

	$scope.changeDesc = function(newValue)
	{
		$scope.description = newValue;
	};

	$scope.changeName = function(newValue)
	{
		$scope.name = newValue;
	};

	console.log($scope.children);
});
