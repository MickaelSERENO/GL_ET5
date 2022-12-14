var myApp = angular.module('myApp', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller('formController', function($scope, $timeout)
{
	formScope      = $scope;
	$scope.showMsg = false;
	$scope.email   = "";

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
									  /*$timeout(function()
										  {
											  $scope.showMsg = false;
										  }, 1000);*/
								  });
				}

				//Move to the home page
				else if(httpCtx.responseText == '1' || httpCtx.responseText == '2')
				{
					window.location.href = "/dashboard/index.php";
				}
			}
		}
		httpCtx.open("POST", "/AJAX/identRequest.php", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send("requestID=3&email="+$scope.email+"&pwd="+$scope.pwd+"&isAdmin="+($scope.isAdmin ? 1 : 0));
	};
});

myApp.directive('myEnter', function() 
{
    return function(scope, element, attrs)
	{
        element.bind("keydown keypress", function (event)
		{
            if(event.keyCode === 13) 
			{
                scope.$apply(function ()
				{
                    scope.$eval(attrs.myEnter);
                });

                event.preventDefault();
            }
        });
    };
});

window.onload = function()
{
	console.log("ok");
}
