var myApp = angular.module('myApp', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller('passwordController', function($scope, $timeout)
{
	$scope.showMsg = false;
	$scope.email   = "";
	$scope.logMsg  = "";

	$scope.trySending = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				console.log("ok");
				$scope.$apply(function()
				{
					//Change the log message following the return from the server
					if(httpCtx.responseText == '1')
						$scope.logMsg = "La demande d'un nouveau mot de passe a été envoyé";

					else
						$scope.logMsg = "L'adresse email est incorrecte";

					//And display it
					$scope.showMsg = true;
					$timeout(function()
					{
						$scope.showMsg = false;
					}, 1000);
				});
			}
		}
		httpCtx.open("POST", "/AJAX/identRequest.php", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send("requestID=4&email="+$scope.email);
	};
});
