var myApp = angular.module('myApp', ['angular-notification-icons', 'ngAnimate', 'ngSanitize', 'ui.bootstrap']);
myApp.controller('formController', function($scope, $timeout, $interval) 
{
	$scope.listNotifJS = listNotifJS;

	$scope.fetchNotifs = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				$scope.listNotifJS = JSON.parse(httpCtx.responseText);
			}
		}
		httpCtx.open('GET', "/AJAX/fetchNotifs.php?unread=false", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	}

	$scope.fetchNotifs();

	$scope.openNotif = function(notif)
	{
		$scope.openedNotif = notif;
		var httpCtx = new XMLHttpRequest();

		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '1')
				{
					alert("An unknown error occured");
				}
			}
		}
		httpCtx.open('GET', "/AJAX/readNotif.php?notifID="+notif.id, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	if(notifID)
	{
		$scope.openNotif($scope.listNotifJS[$scope.listNotifJS.map(function(e){return e.id;}).indexOf(notifID)]);
	}

	$interval(function()
	{
		$scope.fetchNotifs();
	}, 500);
});
