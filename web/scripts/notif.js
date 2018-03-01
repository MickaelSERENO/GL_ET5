
var app = angular.module('myApp', []);
app.controller('formController', function($scope) 
{
	$scope.count = 0;
	$scope.listNotifJS = listNotifJS;
	
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
				{

					alert(httpCtx.responseText);
					alert("An unknown error occured");
				}
			}
		}
		$scope.openedNotif.read = true;
		httpCtx.open('GET', "/AJAX/readNotif.php?notifID="+notif.id, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
		console.log($scope.openedNotif);
	};
	if(notifID)
	{
		console.log(listNotifJS.map(function(e){return e.id;}).indexOf(notifID));
		$scope.openNotif(listNotifJS[listNotifJS.map(function(e){return e.id;}).indexOf(notifID)]);
	}
});
