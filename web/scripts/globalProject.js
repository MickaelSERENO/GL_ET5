var myApp = angular.module('myApp', ['angular-notification-icons', 'ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller("globalProjectCtrl", function($scope, $timeout)
{
	$scope.activeTab = 0;
	$scope.goToGanttHeader = function()
	{
		$scope.$broadcast('clickGanttHeader');
	};
	if(ganttTaskID != -1)
	{
		$scope.activeTab = 1;
	}
});
