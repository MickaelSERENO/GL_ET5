myApp.controller("TaskModal", function($scope, $uibModalInstance, task)
{
	$scope.task = task;

	$scope.modify= function()
	{
		$uibModalInstance.close();
	};

	$scope.delete = function()
	{
		$uibModalInstance.dismiss();
	};

	$scope.grab = function()
	{
		$uibModalInstance.dismiss();
	};
});
