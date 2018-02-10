myApp.controller("CollaboratorModal", function($scope, $uibModalInstance, colls, project, task)
{
	$scope.collaborators      = [new EndUser({name : "\"Vide\"", surname : "", email : ""})].concat(colls);
	$scope.currentColl        = 0;
	$scope.task               = task;
	$scope.middleDate         = new Date(parseInt(task.startDate.getTime() + (task.endDate.getTime() - task.startDate.getTime())*task.advancement / 100));

	$scope.dateFormat  = "dd/MM/yyyy";
	$scope.dateOptions = 
		{
			formatYear: 'yy',
			maxDate: task.endDate,
			minDate: task.startDate,
			startingDay: 1
		};

	$scope.startTime = $scope.task.startDate.getTime() - $scope.task.startDate.getTimezoneOffset()*60*1000; 
	$scope.endTime   = $scope.task.endDate.getTime()   - $scope.task.endDate.getTimezoneOffset()*60*1000; 

	$scope.popupDate = {opened : false};

	$scope.openDate = function()
	{
		$scope.popupDate.opened = true;
	};

	$scope.canShowDate = function()
	{
		return $scope.task.collaboratorEmail != null;
	};

	for(var i=0; i < $scope.collaborators.length; i++)
	{
		if(task.collaboratorEmail === $scope.collaborators[i].email)
		{
			$scope.currentColl = i;
			break;
		}
	}

	$scope.clickCollaborators = function(id)
	{
		$scope.currentColl = id;
	};

	$scope.currentCollTxt = function()
	{
		return $scope.collaborators[$scope.currentColl].name + " " + $scope.collaborators[$scope.currentColl].surname;
	};

	$scope.dateCorrect = function()
	{
		if($scope.middleDate == undefined)
			return false;
		var middleTime = $scope.middleDate.getTime() - $scope.middleDate.getTimezoneOffset()*60*1000; 
	
		return middleTime <= $scope.endTime && middleTime >= $scope.startTime;
	};

	$scope.ok = function()
	{
		if(!$scope.dateCorrect())
			return;

		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			//Check for errors
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					$uibModalInstance.close();
				}
				else
				{
					$uibModalInstance.dismiss();
				}
			}
		}
		httpCtx.open('GET', "/AJAX/projectColls.php?projectID="+projectID+"&requestID=1&taskID=" + $scope.task.id + "&collEmail=" + $scope.collaborators[$scope.currentColl].email+"&middleDate="+$scope.middleDate.getTime()/1000, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.cancel = function()
	{
		$uibModalInstance.dismiss();
	};
});

myApp.controller("DateModal", function($scope, $uibModalInstance, task, minDate, maxDate)
{
	$scope.task        = Object.assign({}, task);
	$scope.dateFormat  = "dd/MM/yyyy";
	$scope.dateOptions = 
		{
			formatYear: 'yy',
			maxDate: maxDate,
			minDate: minDate,
			startingDay: 1
		};

	console.log(maxDate);
	console.log(minDate);

	$scope.popupStart = {opened : false};
	$scope.popupEnd   = {opened : false};

	$scope.openEnd = function()
	{
		$scope.popupEnd.opened = true;
	};


	$scope.openStart = function()
	{
		$scope.popupStart.opened = true;
	};

	$scope.ok = function()
	{
		var httpCtx = new XMLHttpRequest();
		var startTime = $scope.task.startDate.getTime() - $scope.task.startDate.getTimezoneOffset()*60*1000; 
		var endTime   = $scope.task.endDate.getTime()   - $scope.task.endDate.getTimezoneOffset()*60*1000; 
		httpCtx.onreadystatechange = function()
		{
			//Check for errors
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					$uibModalInstance.close({startTime : startTime, endTime : endTime});
				}
				else
				{
					$uibModalInstance.dismiss();
				}
			}
		}
		httpCtx.open('POST', "/AJAX/timeTask.php?projectID="+projectID+"&requestID=1&taskID=" + $scope.task.id + "&startDate="+startTime+"&endDate="+endTime, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.cancel = function()
	{
		$uibModalInstance.dismiss();
	};
});

myApp.controller("AdvModal", function($scope, $uibModalInstance, task)
{
	$scope.task = Object.assign({}, task);

	$scope.$watch('task.chargeConsumed', function(newValue)
	{
		if(newValue > $scope.task.initCharge)
			$scope.task.chargeConsumed = $scope.task.initCharge;
		else if (newValue < 0)
			$scope.task.chargeConsumed = 0;
		$scope.task.remaining   = $scope.task.computedCharge - $scope.task.chargeConsumed;
		$scope.task.advancement = parseInt(100 * $scope.task.chargeConsumed / $scope.task.computedCharge);
	});

	$scope.ok = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					$uibModalInstance.close($scope.task);
				}
				else
				{
					$uibModalInstance.dismiss();
				}
			}
		}
		httpCtx.open('POST', "/AJAX/advTask.php?projectID="+projectID+"&requestID=0&taskID=" + $scope.task.id + "&advancement="+$scope.task.advancement+"&chargeConsumed="+$scope.task.chargeConsumed+"&remaining="+$scope.task.remaining, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.cancel = function()
	{
		$uibModalInstance.dismiss();
	};
});

myApp.controller("SuccessorModal", function($scope, $uibModalInstance, tasks, task)
{
	$scope.task = task;
	$scope.fullTasks     = [];
	$scope.currentTaskID = 0;

	$scope.orderRelationShip = function(currentTask)
	{
		if(currentTask == task)
			return true;
		for(var i = 0; i < currentTask.children.length; i++)
			if($scope.orderRelationShip(currentTask.children[i]))
				return true;
		return false;
	};

	$scope.addToTask = function(currentTask)
	{
		if(currentTask == task)
			return;

		//The date must be correct
		if(currentTask.endDate.getTime() <= task.startDate.getTime() && !$scope.orderRelationShip(currentTask))
			$scope.fullTasks.push(currentTask);

		for(var i=0; i < currentTask.children.length; i++)
			$scope.addToTask(currentTask.children[i]);
	};

	for(var i=0; i < tasks.length; i++)
		$scope.addToTask(tasks[i]);

	$scope.currentTaskTxt = function()
	{
		if($scope.fullTasks.length == 0)
			return "";
		return $scope.fullTasks[$scope.currentTaskID].name;
	};

	$scope.clickTask = function(id)
	{
		$scope.currentTaskID = id;
	};

	$scope.ok = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			//Check for errors
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					$uibModalInstance.close($scope.fullTasks[$scope.currentTaskID]);
				}
				else
				{
					$uibModalInstance.dismiss();
				}
			}
		}
		httpCtx.open('GET', "/AJAX/predecessorTask.php?projectID="+projectID+"&requestID=0&idPred=" + $scope.fullTasks[$scope.currentTaskID].id + "&idSucc=" + $scope.task.id, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.cancel = function()
	{
		$uibModalInstance.dismiss();
	};
});

myApp.controller("ChildModal", function($scope, $uibModalInstance, tasks, task)
{
	$scope.task = task;
	$scope.fullTasks     = [];
	$scope.currentTaskID = 0;

	//Test if the task we are looking in has an order (predecessor / successor) relationship with the desired task
	$scope.orderSuccessor = function(currentTask, comparison)
	{
		if(currentTask == comparison)
			return true;

		for(var i = 0; i < currentTask.successors.length; i++)
			if($scope.orderSuccessor(currentTask.successors[i], comparison))
				return true;
		return false;
	};

	$scope.orderPredecessor = function(currentTask, comparison)
	{
		if(currentTask == comparison)
			return true;

		for(var i = 0; i < currentTask.predecessors.length; i++)
			if($scope.orderPredecessor(currentTask.predecessors[i], comparison))
				return true;
		return false;
	};

	$scope.orderRelationShip = function(currentTask, comparison)
	{
		if(($scope.orderSuccessor(currentTask, comparison) || $scope.orderPredecessor(currentTask, comparison)) && currentTask != comparison)
			return true;

		return false;
	};

	//Test if the task are in the same hierarchy
	$scope.hierarchyMother = function(currentTask)
	{
		if(currentTask == task)
			return true;

		var origin = currentTask;
		var mother = currentTask.mother;
		while(mother != null)
		{
			for(var i = 0; i < mother.children.length; i++)
				if(mother.children[i] != origin && $scope.hierarchyChildren(mother.children[i]))
					return true;

			if($scope.hierarchyMother(mother))
				return true;
			origin = mother;
			mother = mother.mother;
		}
		return false;
	};

	$scope.hierarchyChildren = function(currentTask)
	{
		if(currentTask == task)
			return true;
		for(var i = 0; i < currentTask.children.length; i++)
			if($scope.hierarchyChildren(currentTask.children[i]))
				return true;
		return false;
	};
	$scope.hierarchyRelationship = function(currentTask)
	{
		if($scope.hierarchyMother(currentTask) || $scope.hierarchyChildren(currentTask))
			return true;

		return false;
	};

	$scope.datePredecessor = function(currentTask, origin)
	{
		var mother = currentTask;

		while(mother != null)
		{
			for(var i = 0; i < origin.predecessors.length; i++)
			{
				if(origin.predecessors[i].endDate.getTime() < currentTask.startDate.getTime() || 
					$scope.datePredecessor(mother, task.predecessors[i]))
					return false;
			}
			mother = mother.mother;
		}
		return true;
	};

	$scope.addToTask = function(currentTask)
	{
		var valid = true;

		if(currentTask == task || !(currentTask instanceof(Task)))
			return;

		else if($scope.hierarchyRelationship(currentTask))
		{
			if(currentTask.mother != task.mother)
				valid = false;
		}
		
		else if(currentTask.mother != null)
			return;

		var mother = task;
		while(mother != null)
		{
			if($scope.orderRelationShip(currentTask, mother))
				return;
			mother = mother.mother;
		}

		if(valid && $scope.datePredecessor(currentTask, task))
			$scope.fullTasks.push(currentTask);

		for(var i=0; i < currentTask.children.length; i++)
			$scope.addToTask(currentTask.children[i]);
	};

	for(var i=0; i < tasks.length; i++)
		$scope.addToTask(tasks[i]);

	$scope.currentTaskTxt = function()
	{
		if($scope.fullTasks.length == 0)
			return "";
		return $scope.fullTasks[$scope.currentTaskID].name;
	};

	$scope.clickTask = function(id)
	{
		$scope.currentTaskID = id;
	};

	$scope.ok = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			//Check for errors
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					$uibModalInstance.close();
				}
				else
				{
					$uibModalInstance.dismiss();
				}
			}
		}
		httpCtx.open('GET', "/AJAX/childTask.php?projectID="+projectID+"&requestID=0&idChild=" + $scope.fullTasks[$scope.currentTaskID].id + "&idMother=" + $scope.task.id, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.cancel = function()
	{
		$uibModalInstance.dismiss();
	};
});
