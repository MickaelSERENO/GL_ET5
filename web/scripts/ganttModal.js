myApp.controller("CollaboratorModal", function($scope, $uibModalInstance, items)
{
	$scope.items              = items;
	$scope.collaborators      = [];
	$scope.currentColl        = -1;

	$scope.clickCollaborators = function(id)
	{
		$scope.currentColl = id;
	};

	$scope.currentCollTxt = function()
	{
		if($scope.currentColl == -1)
			return "";
		return $scope.collaborators[$scope.currentColl].name;
	};

	$scope.ok = function()
	{
		$uibModalInstance.close();
	};

	$scope.cancel = function()
	{
		$uibModalInstance.dismiss();
	};
});
