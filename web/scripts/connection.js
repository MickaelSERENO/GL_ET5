var myApp = angular.module('myApp', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller('formController', function($scope)
{
	$scope.pwdMsg = "Le mot de passe a un format incorrect";
	$scope.pwd = "";

});
