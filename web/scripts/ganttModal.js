myApp.controller("AddModal", function($scope, $uibModalInstance, project, colls, tasks)
{
	$scope.projectID = projectID;
	$scope.project   = project;

	$scope.dateFormat  = "dd/MM/yyyy";
	$scope.dateOptions = 
		{
			formatYear: 'yy',
			maxDate: project.endDate,
			minDate: project.startDate,
			startingDay: 1
		};

	$scope.startDate   = new Date();
	$scope.endDate     = new Date();


	$scope.popupStartDate = {opened : false};
	$scope.popupEndDate   = {opened : false};

	$scope.name         = "";
	$scope.description  = "";

	$scope.initCharge   = 0;
	$scope.currentCol   = 0;

	$scope.fullTasks    = [{name : "\"Vide\"", id:"NULL"}];
	$scope.taskMother   = [{name : "\"Vide\"", id:"NULL"}].concat(tasks);
	$scope.predecessors = [];
	$scope.mother       = 0;
	$scope.children     = [];

	$scope.collaborators = [new EndUser({name : "\"Vide\"", surname : "", email : ""})].concat(colls);
	$scope.currentCol    = 0;

	$scope.openStartDate = function()
	{
		$scope.popupStartDate.opened = true;
	};

	$scope.openEndDate = function()
	{
		$scope.popupEndDate.opened = true;
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

	$scope.ok        = function()
	{
	};

	$scope.cancel    = function()
	{
	};

	$scope.addTask = function(task)
	{
		$scope.fullTasks.push(task);
		for(var i =0; i < task.children.length; i++)
			$scope.addTask(task.children[i]);
	};

	for(var i =0; i < tasks.length; i++)
		$scope.addTask(tasks[i]);

});

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

function orderRelationShip(pred, succ)
{
	if(pred == succ)
		return true;
	for(var i = 0; i < pred.children.length; i++)
		if(orderRelationShip(pred.children[i], succ))
			return true;
	return false;
};

function canAddPredecessor(pred, succ)
{
	if(pred == succ)
		return false;

	//The date must be correct
	if(pred.endDate.getTime() <= succ.startDate.getTime() && !orderRelationShip(pred, succ))
		return true;

	return false;
}

myApp.controller("SuccessorModal", function($scope, $uibModalInstance, tasks, task)
{
	$scope.task = task;
	$scope.fullTasks     = [];
	$scope.currentTaskID = 0;

	$scope.addToTask = function(currentTask)
	{
		if(canAddPredecessor(currentTask, task))
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

function orderSuccessor(currentTask, comparison)
{
	if(currentTask.id == 1)
		console.log("ok");
	if(currentTask == comparison)
		return true;

	for(var i = 0; i < currentTask.successors.length; i++)
		if(orderSuccessor(currentTask.successors[i], comparison))
			return true;

	var mother = comparison;
	while(mother != null)
	{
		for(var j = 0; j < currentTask.children.length; j++)
			if(orderSuccessor(currentTask.children[j], mother) && mother != currentTask.children[j])
				return true;
		mother = mother.mother;
	}

	return false;
}

function orderPredecessor(currentTask, comparison)
{
	if(currentTask == comparison)
		return true;

	for(var i = 0; i < currentTask.predecessors.length; i++)
		if(orderPredecessor(currentTask.predecessors[i], comparison))
			return true;

	var mother = comparison;
	while(mother != null)
	{
		for(var j = 0; j < currentTask.children.length; j++)
			if(orderPredecessor(currentTask.children[j], mother) && mother != currentTask.children[j])
				return true;
		mother = mother.mother;
	}

	return false;
}

function orderRelationShip(currentTask, comparison)
{
	if((orderSuccessor(currentTask, comparison) || orderPredecessor(currentTask, comparison)) && currentTask != comparison)
		return true;

	return false;
}

function hierarchyMother(currentTask, comparison)
{
	if(currentTask == comparison)
		return true;

	var origin = currentTask;
	var mother = currentTask.mother;
	while(mother != null)
	{
		for(var i = 0; i < mother.children.length; i++)
			if(mother.children[i] != origin && hierarchyChildren(mother.children[i], comparison))
				return true;

		if(hierarchyMother(mother, comparison))
			return true;
		origin = mother;
		mother = mother.mother;
	}
	return false;
}

function hierarchyChildren(currentTask, comparison)
{
	if(currentTask == comparison)
		return true;
	for(var i = 0; i < currentTask.children.length; i++)
		if(hierarchyChildren(currentTask.children[i], comparison))
			return true;
	return false;
};

function hierarchyRelationship(currentTask, comparison)
{
	if(hierarchyMother(currentTask, comparison) || hierarchyChildren(currentTask, comparison))
		return true;

	return false;
};

function datePredecessor(currentTask, origin)
{
	var mother = currentTask;

	while(mother != null)
	{
		for(var i = 0; i < origin.predecessors.length; i++)
		{
			if(origin.predecessors[i].endDate.getTime() < currentTask.startDate.getTime() || 
				datePredecessor(mother, origin.predecessors[i]))
				return false;
		}
		mother = mother.mother;
	}
	return true;
};

function levelHierarchy(task)
{
	var i =0;
	var mother = task;
	while(mother != null)
	{
		i++;
		mother = mother.mother;
	}
	return i;
};

function canAddTask(child, mother)
{
	var valid = true;

	if(child == mother || !(child instanceof(Task)))
		return false;

	else if(hierarchyRelationship(child, mother))
	{
		if(child.mother != mother.mother)
			valid = false;
	}
	
	else if(child.mother != null)
		return false;

	var motherMother = mother;
	while(motherMother != null)
	{
		if(orderRelationShip(child, motherMother))
			return false;
		motherMother = motherMother.mother;
	}

	if(valid && datePredecessor(child, mother))
		return levelHierarchy(mother) <= 2;
	return false;
}

myApp.controller("ChildModal", function($scope, $uibModalInstance, tasks, task)
{
	$scope.task = task;
	$scope.fullTasks     = [];
	$scope.currentTaskID = 0;

	$scope.addToTask = function(currentTask)
	{
		if(canAddTask(currentTask, task))
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
