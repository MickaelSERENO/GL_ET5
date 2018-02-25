
var app = angular.module('myApp', []);
app.controller('formController', function($scope) 
{
	$scope.count = 0;
	$scope.listNotifJS = listNotifJS;
	$scope.openedNotif;
 
	$scope.openNotif = function(notif)
	{
		console.log(notif);
		$scope.openedNotif = notif;
		var httpCtx = new XMLHttpRequest();

		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '1')
					alert("An unknown error occured");
			}
		}
		$scope.openedNotif.read = true;
		//httpCtx.open('GET', "/", true);
		console.log($scope.openedNotif);
	};
});
