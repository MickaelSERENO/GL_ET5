myApp.controller('headerController', function($scope, $timeout, $interval) 
{

	$scope.goToNotif = function()
	{
		window.location.href="/dashboard/notifications.php";
	}

	$scope.fetchCountUnreadNotifs = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				$scope.countUnreadNotifs = httpCtx.responseText;
			}
		}
		httpCtx.open('GET', "/AJAX/fetchCountUnreadNotifs.php", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	}

	$scope.countUnreadNotifs = $scope.fetchCountUnreadNotifs();

	$interval(function()
	{
		$scope.fetchCountUnreadNotifs();
	}, 1000);
});
