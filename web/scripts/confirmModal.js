myApp.controller("ConfirmModal", function($scope, $uibModalInstance, title, textContent)
{
	$scope.title       = title;
	$scope.ok          = function() {$uibModalInstance.close();}
	$scope.cancel      = function() {$uibModalInstance.dismiss();}
	$scope.textContent = textContent;
});
