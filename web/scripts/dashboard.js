var myApp = angular.module('myApp', []);

myApp.controller('dashboardController', function($scope)
{
	$scope.notifs = notifs;
	$scope.projects= projects;
	$scope.tasks = tasks;


	$scope.linkTo = function(id, place)
	{
		console.log('lala');
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
});


