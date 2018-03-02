myApp.controller("infoProjectCtrl", function($scope, $timeout)
{
	$scope.projectInfo      = projectInfo;
	$scope.name             = "";
	$scope.clientName       = "";
	$scope.contactFirstName = "";
	$scope.contactLastName  = "";
	$scope.managerFirstName = "";
	$scope.managerLastName  = "";
	$scope.description      = "";
	$scope.collaborators    = [];
	$scope.startDate        = new Date();
	$scope.endDate          = new Date();

	$scope.popupStartDate = {opened : false};
	$scope.popupEndDate   = {opened : false};
	$scope.dateFormat  = "dd/MM/yyyy";
	$scope.dateOptions =
	{
		formatYear: 'yy',
		startingDay: 1
	};
	$scope.openStartDate = function()
	{
		$scope.popupStartDate.opened = true;
	};

	$scope.openEndDate = function()
	{
		$scope.popupEndDate.opened = true;
	};

	$scope.inModifyStats = false;

	$scope.modify = function()
	{
		if($scope.inModifyStats == false)
		{
			$scope.inModifyStats = true;
		}
	};

	$scope.cancel   = function()
	{
		$scope.name             = (" " + $scope.projectInfo.name).slice(1);
		$scope.clientName       = (" " + $scope.projectInfo.clientName).slice(1);
		$scope.contactFirstName = (" " + $scope.projectInfo.contactFirstName).slice(1);
		$scope.contactLastName  = (" " + $scope.projectInfo.contactLastName).slice(1);
		$scope.managerFirstName = (" " + $scope.projectInfo.managerFirstName).slice(1);
		$scope.managerLastName  = (" " + $scope.projectInfo.managerLastName).slice(1);
		$scope.description      = (" " + $scope.projectInfo.description).slice(1);
		$scope.managerEmail     = (" " + $scope.projectInfo.managerEmail);
		$scope.collaborators    = [];
		$scope.startDate        = new Date($scope.projectInfo.startDate);
		$scope.endDate          = new Date($scope.projectInfo.endDate);
		for(var i = 0; i < $scope.projectInfo.listCollab.length; i++)
			$scope.collaborators.push($scope.projectInfo.listCollab[i]);

		$scope.inModifyStats    = false;
	};

	$scope.cancel();

	$scope.delete = function()
	{
	};

	$scope.delColl = function(index)
	{
		$scope.collaborators.splice(index, 1);
	};

	$scope.validate = function()
	{
		$scope.inModifyStats                = false;
		$scope.projectInfo.name             = (" " + $scope.name).slice(1);
		$scope.projectInfo.clientName       = (" " + $scope.clientName).slice(1);
		$scope.projectInfo.contactFirstName = (" " + $scope.contactFirstName).slice(1);
		$scope.projectInfo.contactLastName  = (" " + $scope.contactLastName).slice(1);
		$scope.projectInfo.managerFirstName = (" " + $scope.managerFirstName).slice(1);
		$scope.projectInfo.managerLastName  = (" " + $scope.managerLastName).slice(1);
		$scope.projectInfo.managerEmail     = (" " + $scope.managerEmail);
		$scope.projectInfo.description      = (" " + $scope.description).slice(1);
		$scope.projectInfo.listCollab       = [];
		for(var i = 0; i < $scope.collaborators.length; i++)
			$scope.projectInfo.listCollab.push($scope.collaborators[i]);
		//TODO
	};

	$scope.openClient = function()
	{
	};

	$scope.openClientContact = function()
	{
	};

	$scope.openProjectManager = function()
	{
	};

	$scope.openAddCollaborators = function()
	{
	};

	$scope.$on('clickGanttHeader', function(event, data)
	{
		$scope.cancel();
	});
});
