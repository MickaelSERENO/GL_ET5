myApp.controller("CollaboratorModal", function($scope, $uibModalInstance, colls, project, task)
{
	$scope.collaborators      = [new EndUser({name : "\"Vide\"", surname : "", email : ""})].concat(colls);
	$scope.currentColl        = 0;
	$scope.task               = task;

	for(var i=0; i < colls.length; i++)
	{
		if(task.collaboratorEmail == colls[i].email)
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
		httpCtx.open('GET', "/AJAX/projectColls.php?projectID="+projectID+"&requestID=1&taskID=" + $scope.task.id + "&collEmail=" + $scope.collaborators[$scope.currentColl].email, true);
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

	$scope.ok= function()
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
		var startTime = $scope.task.startDate.getTime() - $scope.task.startDate.getTimezoneOffset()*60*1000; 
		var endTime   = $scope.task.endDate.getTime()   - $scope.task.endDate.getTimezoneOffset()*60*1000; 
		httpCtx.open('POST', "/AJAX/timeTask.php?projectID="+projectID+"&requestID=1&taskID=" + $scope.task.id + "&startDate="+startTime+"&endDate="+endTime, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.cancel = function()
	{
		$uibModalInstance.dismiss();
	};
});
