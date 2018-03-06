var myApp       = angular.module('myApp', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

myApp.controller("ContactCtrl", function($scope, $timeout, $uibModal)
{		
	$scope.contact       = contact;
	$scope.userRank      = rank;

	console.log(rank);

    $scope.errorMsg      = "";

    $scope.active        = true;
    $scope.inactive      = false;
	$scope.name          = "";
	$scope.surname       = "";
	$scope.email         = "";
	$scope.inactive      = "";
	$scope.telephone     = "";
	$scope.entreprise    = "";
	$scope.status        = "";
	$scope.rank          = 0;
	$scope.isContact     = true;

	$scope.listStatus    = ["Collaborateur", "Responsable de Projet", "Contact Client"];

	$scope.inModifyStats = false;
	$scope.modifyText    = "Modifier";

	$scope.modify        = function()
	{
		if($scope.inModifyStats)
		{
			$scope.inModifyStats = false;
			$scope.modifyText    = "Modifier";
            $scope.ok();
		}
		else
		{
			$scope.inModifyStats = true;
			$scope.modifyText    = "Confirmer";
		}
	};

    $scope.ok        = function()
    {
        //TODO
    };

	$scope.reinitPwd = function()
	{
		$scope.opts = 
		{
			backdrop : true,
			backdropClick : true,
			dialogFade : false,
			keyboard : true,
			templateUrl : "confirmModal.html",
			controller : "ConfirmModal",
			controllerAs : "$ctrl",
			resolve : {
						title       : function() {return "Confirmation";},
						textContent : function() {return "Voulez vous réinitialiser le mot de passe de l'utilisateur ?";}
					  }
		};

		var modalInstance = $uibModal.open($scope.opts);
		modalInstance.result.then(
			function() //ok
			{
			},
			function() //cancel
			{
			});
	};

	$scope.cancel    = function()
	{
		$scope.name       = $scope.contact.name;
		$scope.surname    = $scope.contact.surname;
		$scope.email      = $scope.contact.email;
        $scope.active     = $scope.contact.active;
		$scope.telephone  = $scope.contact.telephone;
		$scope.entreprise = $scope.contact.entreprise;
		$scope.status     = $scope.contact.status;
		$scope.rank       = $scope.contact.rank;
		$scope.isContact  = $scope.rank == 2;
	};

	$scope.cancel();

	$scope.setInactive  = function()
	{
        var httpCtx = new XMLHttpRequest();
        httpCtx.onreadystatechange = function()
        {
            if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
            {
                if(httpCtx.responseText != '-1')
                {
                    $scope.active           = false;
                    $scope.contact.active   = false;
                    $scope.errorMsg         = "";
                }
                else
                {
                    $scope.errorMsg = "L'utilisateur ne peut pas être rendu inactif";
                }
            }
        }
        httpCtx.open('GET', "/AJAX/setActive.php?email="+encodeURIComponent(contactEmail)+"&active=false", true);
        httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        httpCtx.send(null);
	};

	$scope.setActive    = function()
	{
        var httpCtx = new XMLHttpRequest();
        httpCtx.onreadystatechange = function()
        {
            if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
            {
                if(httpCtx.responseText != '-1')
                {
                    $scope.active           = true;
                    $scope.contact.active   = true;
                    $scope.errorMsg         = "";
                }
                else
                {
                    $scope.errorMsg = "L'utilisateur ne peut pas être rendu actif";
                }
            }
        }
        httpCtx.open('GET', "/AJAX/setActive.php?email="+encodeURIComponent(contactEmail)+"&active=true", true);
        httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        httpCtx.send(null);
	};
});	
