var project;

var myApp = angular.module('myApp', ['angular-notification-icons', 'ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller('dashboardController', function($scope, $uibModal, $interval)
{
	$scope.rank = rank;
	$scope.notifs = notifs;
	if(rank == 1)
	{
		$scope.projects = projects;
	}
	if(rank != 2)
	{
		$scope.itasks = tasks;
	}
	
	$scope.linkTo = function(id, place)
	{
		switch(place)
		{
			case 'notif':
				window.location.href = '/dashboard/notifications.php?notifId=' + id;
				break;
			case 'project':
				window.location.href = '/Project/infoProject.php?projectID=' + id;
				break;
			default:
				//TODO
				var a=1;
		}
	};

	$scope.fetchNotifs = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				$scope.notifs = JSON.parse(httpCtx.responseText);
			}
		}
		httpCtx.open('GET', "/AJAX/fetchNotifs.php?unread=true", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	}

	$scope.openTask = function(task)
	{
		console.log(task);
		project = new Project($scope.itasks[0].project);
		task = new Task(task);
		if(task == null)
			return;

		$scope.opts = 
		{
			backdrop : true,
			backdropClick : true,
			dialogFade : false,
			keyboard : true,
			templateUrl : "modalTask.html",
			controller : "TaskModal",
			controllerAs : "$ctrl",
			resolve : {task		: function() {return task;},
						project : function() {return project;},
						tasks   : function() {return null;},
					  }
		};

		var modalInstance = $uibModal.open($scope.opts);
		modalInstance.result.then(
			function()
			{
			},
			function() //cancel
			{
			});
	};

	$interval(function()
	{
		$scope.fetchNotifs();
	}, 500);
});
