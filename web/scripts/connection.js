var myApp = angular.module('myApp', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller('formController', function($scope, $timeout)
{
	$scope.showMsg = false;
	$scope.pwd     = "";
	$scope.email   = "";
	$scope.isAdmin = "";

	$scope.tryConnection = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText == '0')
				{
					console.log("not logged");
					$scope.$apply(function()
								  {
									  $scope.showMsg = true;
									  $timeout(function()
										  {
											  $scope.showMsg = false;
										  }, 1000);
								  });
				}
				else
					console.log("logged");
			}
		}
		console.log($scope.email);
		httpCtx.open("POST", "connection.php", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send("email="+$scope.email+"&pwd="+$scope.pwd+"&isAdmin="+$scope.isAdmin);
	};
});
