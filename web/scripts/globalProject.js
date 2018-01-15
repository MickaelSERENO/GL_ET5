var myApp       = angular.module('myApp', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller("globalProjectCtrl", function($scope, $timeout)
{
	$scope.activeTab = 0;
});
