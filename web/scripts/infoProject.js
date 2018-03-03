var infoScope;

myApp.controller("infoProjectCtrl", function($scope, $timeout, $uibModal)
{
	infoScope = $scope;

	$scope.projectInfo      = projectInfo;
	$scope.name             = "";
	$scope.clientName       = "";
	$scope.clientEmail      = "";
	$scope.contactFirstName = "";
	$scope.contactLastName  = "";
	$scope.managerFirstName = "";
	$scope.managerLastName  = "";
	$scope.managerEmail     = "";
	$scope.description      = "";
	$scope.collaborators    = [];
	$scope.startDate        = new Date();
	$scope.endDate          = new Date();
	$scope.minDate          = null;
	$scope.maxDate          = null;

	$scope.taskMinDate = function()
	{
		console.log("ok");
		if(scope == null)
			return null;
		var minDate = null;
		if(scope.tasks.length > 0)
			minDate = scope.tasks[0].startDate;
		for(var i = 1; i < scope.tasks.length; i++)
		{
			var mD = scope.tasks[i].startDate;
			if(mD.getTime() < minDate.getTime())
				minDate = mD;
		}
		console.log(minDate);
		return minDate;
	}
	$scope.taskMaxDate = function()
	{
		if(scope == null)
			return null;
		var maxDate = null;
		if(scope.tasks.length > 0)
			maxDate = scope.tasks[0].endDate;
		for(var i = 1; i < scope.tasks.length; i++)
		{
			var mD = scope.tasks[i].endDate;
			if(mD.getTime() > maxDate.getTime())
				maxDate = mD;
		}
		return maxDate;
	}
	$scope.popupStartDate = {opened : false};
	$scope.popupEndDate   = {opened : false};
	$scope.dateFormat  = "dd/MM/yyyy";
	$scope.dateOptions =
	{
		formatYear  : 'yy',
		startingDay : 1,
		maxDate     : $scope.minDate
	};
	$scope.dateOptions2 =
	{
		formatYear  : 'yy',
		startingDay : 1,
		minDate     : $scope.maxDate
	};
	$scope.openStartDate = function()
	{
		$scope.popupStartDate.opened = true;
	};

	$scope.openEndDate = function()
	{
		$scope.popupEndDate.opened = true;
	};

	$scope.$watch('endDate', function(newValue)
	{
		if(newValue == undefined)
			return;
		if($scope.maxDate != null && newValue.getTime() < $scope.maxDate.getTime())
			newValue = new Date($scope.maxDate);
		$scope.endDate = newValue;
	});

	$scope.$watch('startDate', function(newValue)
	{
		if(newValue == undefined)
			return;
		if($scope.minDate != null && newValue.getTime() > $scope.minDate.getTime())
			newValue = new Date($scope.minDate);
		$scope.startDate = newValue;
	});

	$scope.inModifyStats = false;

	$scope.modify = function()
	{
		if($scope.inModifyStats == false)
		{
			$scope.inModifyStats = true;
		}
	};

	$scope.cancel   = function()
	{
		$scope.name             = (" " + $scope.projectInfo.name).slice(1);
		$scope.clientName       = (" " + $scope.projectInfo.clientName).slice(1);
		$scope.clientEmail      = (" " + $scope.projectInfo.clientEmail).slice(1);
		$scope.contactFirstName = (" " + $scope.projectInfo.contactFirstName).slice(1);
		$scope.contactLastName  = (" " + $scope.projectInfo.contactLastName).slice(1);
		$scope.contactEmail     = (" " + $scope.projectInfo.contactEmail).slice(1);
		$scope.managerFirstName = (" " + $scope.projectInfo.managerFirstName).slice(1);
		$scope.managerLastName  = (" " + $scope.projectInfo.managerLastName).slice(1);
		$scope.description      = (" " + $scope.projectInfo.description).slice(1);
		$scope.managerEmail     = (" " + $scope.projectInfo.managerEmail).slice(1);
		$scope.collaborators    = [];
		$scope.startDate        = new Date($scope.projectInfo.startDate);
		$scope.endDate          = new Date($scope.projectInfo.endDate);
		for(var i = 0; i < $scope.projectInfo.listCollab.length; i++)
			$scope.collaborators.push($scope.projectInfo.listCollab[i]);

		$scope.inModifyStats    = false;
	};

	$scope.cancel();

	$scope.delete = function()
	{
	};

	$scope.delColl = function(index)
	{
		$scope.collaborators.splice(index, 1);
	};

	$scope.validate = function()
	{

		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					$scope.inModifyStats                = false;
					$scope.projectInfo.name             = (" " + $scope.name).slice(1);
					$scope.projectInfo.clientName       = (" " + $scope.clientName).slice(1);
					$scope.projectInfo.clientEmail      = (" " + $scope.clientEmail).slice(1);
					$scope.projectInfo.contactFirstName = (" " + $scope.contactFirstName).slice(1);
					$scope.projectInfo.contactLastName  = (" " + $scope.contactLastName).slice(1);
					$scope.projectInfo.contactEmail     = (" " + $scope.contactEmail).slice(1);
					$scope.projectInfo.managerFirstName = (" " + $scope.managerFirstName).slice(1);
					$scope.projectInfo.managerLastName  = (" " + $scope.managerLastName).slice(1);
					$scope.projectInfo.managerEmail     = (" " + $scope.managerEmail);
					$scope.projectInfo.description      = (" " + $scope.description).slice(1);
					$scope.projectInfo.listCollab       = [];
					for(var i = 0; i < $scope.collaborators.length; i++)
						$scope.projectInfo.listCollab.push($scope.collaborators[i]);
				}
			}
		};

		var startTime     = ($scope.startDate.getTime() - $scope.startDate.getTimezoneOffset()*60*1000)/1000; 
		var endTime       = ($scope.endDate.getTime()   - $scope.endDate.getTimezoneOffset()*60*1000)/1000; 

		var collaborators = [];
		for(var i = 0; i < $scope.collaborators.length; i++)
			collaborators.push($scope.collaborators[i].email);
		collaborators = JSON.stringify(collaborators);

		httpCtx.open('GET', "/AJAX/modifyProject.php?projectID="+$scope.projectInfo.id+"&name="+encodeURIComponent($scope.name)+"&description="+encodeURIComponent($scope.description)+"&startTime="+startTime+"&endTime="+endTime+"&collaborators="+collaborators+"&managerEmail="+$scope.managerEmail+"&clientEmail="+$scope.clientEmail+"&contactClientEmail="+$scope.contactEmail, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.openClient = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					var data = JSON.parse(httpCtx.responseText);
					$scope.opts = 
					{
						backdrop : true,
						backdropClick : true,
						dialogFade : false,
						keyboard : true,
						templateUrl : "modalAddColl.html",
						controller : "SelectData",
						controllerAs : "$ctrl",
						resolve : {
									data       : function() {return data;},
									showFields : function() {return showFields = ["name", "email", "telephone"];},
									fields     : function() {return fields =
												{
													name      : {label: "Nom"},
													email     : {label: "Email"},
													telephone : {label: "Téléphone"}
												};},
									okText     : function() {return "Ajouter";},
									title      : function() {return "Changement de client";}
								  }
					};

					var modalInstance = $uibModal.open($scope.opts);
					modalInstance.result.then(
						function(client) //ok
						{
							$scope.clientEmail = client.email;
							$scope.clientName  = client.name;
						},
						function() //cancel
						{
						});
				}
			}
		}

		httpCtx.open('GET', "/AJAX/fetchClientsInfo.php", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.openClientContact = function()
	{
		if($scope.clientEmail == "")
			return;
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					var data = JSON.parse(httpCtx.responseText);
					$scope.opts = 
					{
						backdrop : true,
						backdropClick : true,
						dialogFade : false,
						keyboard : true,
						templateUrl : "modalAddColl.html",
						controller : "SelectData",
						controllerAs : "$ctrl",
						resolve : {
									data       : function() {return data;},
									showFields : function() {return showFields = ["name", "surname", "email", "telephone"];},
									fields     : function() {return fields =
												{
													name      : {label: "Prénom"},
													surname   : {label: "Nom"},
													email     : {label: "Email"},
													telephone : {label: "Téléphone"}
												};},
									okText     : function() {return "Ajouter";},
									title      : function() {return "Changement de contact client";}
								  }
					};

					var modalInstance = $uibModal.open($scope.opts);
					modalInstance.result.then(
						function(clientContact) //ok
						{
							$scope.contactFirstName = clientContact.name;
							$scope.contactLastName  = clientContact.surname;
							$scope.contactEmail     = clientContact.email;
						},
						function() //cancel
						{
						});
				}
			}
		}

		httpCtx.open('GET', "/AJAX/fetchClientContacts.php?clientEmail="+encodeURIComponent($scope.clientEmail), true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.openProjectManager = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					var data = JSON.parse(httpCtx.responseText);
					$scope.opts = 
					{
						backdrop : true,
						backdropClick : true,
						dialogFade : false,
						keyboard : true,
						templateUrl : "modalAddColl.html",
						controller : "SelectData",
						controllerAs : "$ctrl",
						resolve : {
									data       : function() {return data;},
									showFields : function() {return showFields = ["name", "surname", "email"];},
									fields     : function() {return fields =
												{
													name    : {label: "Prénom"},
													surname : {label: "Nom"},
													email   : {label: "Email"}
												};},
									okText     : function() {return "Ajouter";},
									title      : function() {return "Changement de responsable de projet";}
								  }
					};

					var modalInstance = $uibModal.open($scope.opts);
					modalInstance.result.then(
						function(coll) //ok
						{
							$scope.managerEmail     = coll.email;
							$scope.managerFirstName = coll.name;
							$scope.managerLastName  = coll.surname;

							var collExist = false;
							for(var i = 0; i < $scope.collaborators.length; i++)
								if($scope.collaborators[i].email == coll.email)
								{
									collExist = true;
									break;
								}
							if(!collExist)
								$scope.collaborators.push(coll);
						},
						function() //cancel
						{
						});
				}
			}
		}

		httpCtx.open('GET', "/AJAX/getActivePM.php", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.openAddCollaborators = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					var data = JSON.parse(httpCtx.responseText);
					for(var i = 0; i < $scope.collaborators.length; i++)
						for(var j = 0; j < data.length; j++)
							if(data[j].email == $scope.collaborators[i].email)
							{
								data.splice(j, 1);
								break;
							}
					$scope.opts = 
					{
						backdrop : true,
						backdropClick : true,
						dialogFade : false,
						keyboard : true,
						templateUrl : "modalAddColl.html",
						controller : "SelectData",
						controllerAs : "$ctrl",
						resolve : {
									data       : function() {return data;},
									showFields : function() {return showFields = ["name", "surname", "email"];},
									fields     : function() {return fields =
												{
													name    : {label: "Prénom"},
													surname : {label: "Nom"},
													email   : {label:"Email"}
												};},
									okText     : function() {return "Ajouter";},
									title      : function() {return "Ajout d'un collaborateur";}
								  }
					};

					var modalInstance = $uibModal.open($scope.opts);
					modalInstance.result.then(
						function(coll) //ok
						{
							$scope.collaborators.push(coll);
						},
						function() //cancel
						{
						});
				}
			}
		}


		httpCtx.open('GET', "/AJAX/getActiveColls.php", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	$scope.inCollaborator = function(collEmail)
	{
		if(scope == null)
			return true;

		for(var i = 0; i < scope.tasks.length; i++)
			if($scope.inCollaboratorRecursive(scope.tasks[i], collEmail))
				return true;
		return false;
	};

	$scope.inCollaboratorRecursive = function(task, collEmail)
	{
		if(task.collaboratorEmail == undefined)
			return false
		else if(task.collaboratorEmail == collEmail)
			return true;
		for(var i = 0; i < task.children.length; i++)
			if($scope.inCollaboratorRecursive(task.children[i], collEmail))
				return true;
		return false;

	};

	$scope.updateDate = function()
	{
		$scope.minDate              = $scope.taskMinDate();
		$scope.maxDate              = $scope.taskMaxDate();
		$scope.dateOptions.maxDate  = $scope.minDate;
		$scope.dateOptions2.minDate = $scope.maxDate;
	};

	$scope.$on('clickGanttHeader', function(event, data)
	{
		$scope.cancel();
	});
});
