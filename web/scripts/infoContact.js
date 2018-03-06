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
	$scope.clientEmail   = "";
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
		if($scope.isContact)
		{
			var httpCtx = new XMLHttpRequest();
			httpCtx.onreadystatechange = function()
			{
				if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
				{
					if(httpCtx.responseText == '0')
					{
						$scope.validateValues();
					}
					else if(httpCtx.responseText == '1')
					{
						$scope.errorMsg = "Cet email existe déjà";
					}
				}
			};
			httpCtx.open('GET', "/AJAX/modifyClientContact.php?newEmail="+encodeURIComponent($scope.email)+"&oldEmail="+encodeURIComponent($scope.contact.email)+"&telephone="+encodeURIComponent($scope.telephone)+"&clientEmail="+encodeURIComponent($scope.clientEmail)+"&name="+encodeURIComponent($scope.name)+"&surname="+encodeURIComponent($scope.surname), true);
			httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			httpCtx.send(null);
		}
		else
		{
			var httpCtx = new XMLHttpRequest();
			httpCtx.onreadystatechange = function()
			{
				if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
				{
					if(httpCtx.responseText == '0')
					{
						$scope.validateValues;
					}
					else if(httpCtx.responseText == '1')
					{
						$scope.errorMsg = "Cet email existe déjà";
					}
				}
			};
			httpCtx.open('GET', "/AJAX/modifyEndUser.php?newEmail="+encodeURIComponent($scope.email)+"&oldEmail="+encodeURIComponent($scope.contact.email)+"&telephone="+encodeURIComponent($scope.telephone)+"&name="+encodeURIComponent($scope.name)+"&surname="+encodeURIComponent($scope.surname)+"&rank="+$scope.rank, true);
			httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			httpCtx.send(null);
		}
    };

	$scope.validateValues = function()
	{
		$scope.errorMsg         = "";

		$scope.contact.name        = $scope.name;
		$scope.contact.surname     = $scope.surname;
		$scope.contact.email       = $scope.email;
		$scope.contact.active      = $scope.active;
		$scope.contact.telephone   = $scope.telephone;
		$scope.contact.entreprise  = $scope.entreprise;
		$scope.contact.status      = $scope.status;
		$scope.contact.rank        = $scope.rank;
		$scope.contact.clientEmail = $scope.clientEmail;
	};

	$scope.openClient = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					var data = JSON.parse(httpCtx.responseText);
					$scope.opts = 
					{
						backdrop : true,
						backdropClick : true,
						dialogFade : false,
						keyboard : true,
						templateUrl : "modalAddColl.html",
						controller : "SelectData",
						controllerAs : "$ctrl",
						resolve : {
									data       : function() {return data;},
									showFields : function() {return showFields = ["name", "email", "telephone"];},
									fields     : function() {return fields =
												{
													name      : {label: "Nom"},
													email     : {label: "Email"},
													telephone : {label: "Téléphone"}
												};},
									okText     : function() {return "Ajouter";},
									title      : function() {return "Changement de client";}
								  }
					};

					var modalInstance = $uibModal.open($scope.opts);
					modalInstance.result.then(
						function(client) //ok
						{
							$scope.clientEmail = client.email;
							$scope.entreprise  = client.name;
						},
						function() //cancel
						{
						});
				}
			}
		}

		httpCtx.open('GET', "/AJAX/fetchClientsInfo.php", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
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
		$scope.name        = $scope.contact.name;
		$scope.surname     = $scope.contact.surname;
		$scope.email       = $scope.contact.email;
        $scope.active      = $scope.contact.active;
		$scope.telephone   = $scope.contact.telephone;
		$scope.entreprise  = $scope.contact.entreprise;
		$scope.status      = $scope.contact.status;
		$scope.rank        = $scope.contact.rank;
		$scope.clientEmail = $scope.contact.clientEmail;
		$scope.isContact   = $scope.rank == 2;

		$scope.errorMsg      = "";
		$scope.inModifyStats = false;
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

	$scope.changeRank = function(r)
	{
		$scope.rank = r;
	};
});	
