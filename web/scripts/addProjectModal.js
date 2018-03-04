myApp.controller("addProjectModal", function($scope, $uibModalInstance, $uibModal, $filter, collList)
{
	$scope.name             = "";
	$scope.clientName       = "";
	$scope.clientEmail      = "";
	$scope.contactFirstName = "";
	$scope.contactLastName  = "";
	$scope.contactEmail     = "";
	$scope.managerFirstName = "";
	$scope.managerLastName  = "";
	$scope.managerEmail     = "";
	$scope.description      = "";
	$scope.collaborators    = collList;
	$scope.startDate        = new Date();
	$scope.endDate          = new Date();
	$scope.errorMsg         = "";

	$scope.popupStartDate = {opened : false};
	$scope.popupEndDate   = {opened : false};
	$scope.dateFormat  = "dd/MM/yyyy";
	$scope.dateOptions =
	{
		formatYear  : 'yy',
		startingDay : 1,
		maxDate     : $scope.endDate
	};
	$scope.dateOptions2 =
	{
		formatYear  : 'yy',
		startingDay : 1,
		minDate     : $scope.startDate
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
		if($scope.maxDate != null && newValue.getTime() < $scope.startDate.getTime())
			newValue = new Date($scope.startDate);
		$scope.endDate = newValue;
		$scope.updateDate();
	});

	$scope.$watch('startDate', function(newValue)
	{
		if(newValue == undefined)
			return;
		if($scope.minDate != null && newValue.getTime() > $scope.endDate.getTime())
			newValue = new Date($scope.endDate);
		$scope.startDate = newValue;
		$scope.updateDate();
	});

	$scope.delColl = function(index)
	{
		$scope.collaborators.splice(index, 1);
	};

	$scope.ok = function()
	{
		if($scope.name == "")
		{
			$scope.errorMsg = "Le nom ne peut pas être vide";
			return;
		}

		else if($scope.managerEmail == "")
		{
			$scope.errorMsg = "Le projet doit avoir un manager";
			return;
		}
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					//TODO send the new project object
					var proj = 
						{
							id: httpCtx.responseText,
							manageremail: $scope.managerEmail,
							managername: $scope.managerFirstName,
							contactemail: $scope.contactEmail,
							contactname: $scope.contactFirstName,
							clientname: $scope.clientName,
							name: $scope.name,
							description: $scope.description,
							startdate: $scope.formatDate($scope.startDate),
							enddate: $scope.formatDate($scope.endDate),
							status: "NOT_STARTED"
						}
					$uibModalInstance.close(proj);
				}
			}
		};

		var startTime     = ($scope.startDate.getTime() - $scope.startDate.getTimezoneOffset()*60*1000)/1000; 
		var endTime       = ($scope.endDate.getTime()   - $scope.endDate.getTimezoneOffset()*60*1000)/1000; 

		var collaborators = [];
		for(var i = 0; i < $scope.collaborators.length; i++)
			collaborators.push($scope.collaborators[i].email);
		collaborators = JSON.stringify(collaborators);

		httpCtx.open('GET', "/AJAX/addProject.php?&name="+encodeURIComponent($scope.name)+"&description="+encodeURIComponent($scope.description)+"&startTime="+startTime+"&endTime="+endTime+"&collaborators="+collaborators+"&managerEmail="+$scope.managerEmail+"&clientEmail="+$scope.clientEmail+"&contactClientEmail="+$scope.contactEmail, true);
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

	$scope.updateDate = function()
	{
		$scope.dateOptions.maxDate  = $scope.endDate;
		$scope.dateOptions2.minDate = $scope.startDate;
	};

	$scope.cancel = function()
	{
		$uibModalInstance.dismiss();
	};

	$scope.formatDate = function(date) 
	{
		var d = new Date(date),
			month = '' + (d.getMonth() + 1),
			day = '' + d.getDate(),
			year = d.getFullYear();

		if (month.length < 2) month = '0' + month;
		if (day.length < 2) day = '0' + day;

		return [year, month, day].join('-');
	}
});
