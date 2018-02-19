var project;

var myApp = angular.module('myApp', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller('dashboardController', function($scope, $uibModal)
{
	$scope.rank = rank;
	$scope.notifs = notifs;
	if(rank == 1)
	{
		$scope.projects = projects;
	}
	if(rank != 2)
	{
		$scope.tasks = tasks;
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
	$scope.openTask = function(itask)
	{
		project = new Project(itask.project);
		task = new Task(itask.task);
		console.log(task)
		console.log(project)
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
			resolve : {task    : function() {return task;},
						project    : function() {return project;},
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
});
