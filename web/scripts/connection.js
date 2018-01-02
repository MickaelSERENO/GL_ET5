var myApp = angular.module('myApp', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller('formController', function($scope, $timeout)
{
	$scope.showMsg = false;
	$scope.pwd     = "";
	$scope.email   = "";
	$scope.isAdmin = "";

	//Try to be connected via AJAX protocol
	$scope.tryConnection = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				//Error ident
				//Show the error message
				if(httpCtx.responseText == '0')
				{
					$scope.$apply(function()
								  {
									  $scope.showMsg = true;
									  $timeout(function()
										  {
											  $scope.showMsg = false;
										  }, 1000);
								  });
				}

				//Successful or already logged 
				//Move to the home page
				else if(httpCtx.responseText == '1' || httpCtx.responseText == '2')
				{

				}
			}
		}
		httpCtx.open("POST", "connection.php", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send("email="+$scope.email+"&pwd="+$scope.pwd+"&isAdmin="+$scope.isAdmin);
	};
});

window.onload = function()
{
	//Test if we are already logged
	var httpCtx = new XMLHttpRequest();
	httpCtx.onreadystatechange = function()
	{
		if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
		{
			if(httpCtx.responseText == '2')
			{
				//We are already logged. Move to home page
				console.log("already logged");
			}
		}
	}
	httpCtx.open("POST", "connection.php", true);
	httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	httpCtx.send("email=&pwd=&isAdmin=");

	var banner = document.getElementById('topBanner');
	var centralPart = document.getElementById('centralPart');
	centralPart.style.marginTop = banner.offsetTop + banner.clientHeight + 10 + 'px';
}
