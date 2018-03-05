class ClientInfo
{
	constructor(copy)
	{
		this.name = copy.name;
		this.email=copy.email;
		this.description=copy.description;
		this.telephone=copy.telephone;
	}
}

class Project
{
	constructor(copy)
	{
		this.managerEmail	=	copy.managerEmail;
		this.client	        =	copy.client;
		this.name			= 	copy.name;
		this.startDate 		= 	copy.startDate;
		this.endDate		=	copy.endDate;
	}
}

class Contact
{
	constructor(copy)
	{
		this.name = copy.name;
		this.surname = copy.surname;
		this.email = copy.email;
		this.telephone = copy.telephone;
		this.entreprise = copy.entreprise;
	}
}

myApp.controller("ClientsCtrl", function($scope, $timeout, $uibModal)
{		
	//Variables
	$scope.clients=[];
	
	//Load clients
	var httpCtx = new XMLHttpRequest();
	httpCtx.onreadystatechange= function()
	{
		if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
		{
			$scope.$apply(function()
			{
				console.log(httpCtx.responseText)
				
				var clients = JSON.parse(httpCtx.responseText);
				for(var i in clients)
				{
					// console.log(clients[i]);
					currentClient = new ClientInfo(clients[i]);
					// console.log(currentClient);
					$scope.clients.push(currentClient);
				}
			});				
		}			
	}
	httpCtx.open('GET', "/AJAX/fetchClientsInfo.php",true);
	httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	httpCtx.send(null);
	// console.log($scope.clients);

	
	
	
	//RECUPERE LES INFO D'UN CLIENT CHOISI PARMI LA LISTE
	$scope.getClientInfo= function(client){
		
		//LE BON CLIENT
		clients = $scope.clients
		for(var i in clients)
		{
			// console.log(clients[i].name)
			// console.log(i)
			if(client.name==clients[i].name)
			{
				$scope.selectedClient=new ClientInfo(clients[i]);
			}
		}
		
		//RECUPERE LES PROJETS DU CLIENT
		$scope.clientProjects=[];
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange= function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				$scope.$apply(function()
				{
					console.log(httpCtx.responseText)
					var projects = JSON.parse(httpCtx.responseText);
					for(var i in projects)
					{
						currentProject=new Project(projects[i]);
						$scope.clientProjects.push(currentProject);
					}
				});				
			}
		}
		// console.log("../AJAX/fetchClientProjects.php?clientEmail="+$scope.selectedClient.contactEmail);
		httpCtx.open("GET", "../AJAX/fetchClientProjects.php?clientEmail="+encodeURIComponent($scope.selectedClient.email), true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
		
		//RECUPERE LES CONTACTS DU CLIENT
		$scope.clientContacts=[];
		var httpCx = new XMLHttpRequest();
		httpCx.onreadystatechange= function()
		{
			if(httpCx.readyState == 4 && (httpCx.status == 200 || httpCx.status == 0))
			{
				$scope.$apply(function()
				{					
					var contacts = JSON.parse(httpCx.responseText);
					for(var i in contacts)
					{
						currentContact=new Contact(contacts[i]);
						$scope.clientContacts.push(currentContact);
					}
				});				
			}			
		}
		// console.log("../AJAX/fetchClientProjects.php?clientEmail="+$scope.selectedClient.contactEmail);
		httpCx.open('GET', "../AJAX/fetchClientContacts.php?clientEmail="+encodeURIComponent($scope.selectedClient.email),true);
		httpCx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCx.send(null);
	};

	$scope.openAddClient = function()
	{
		$scope.opts = 
		{
			backdrop : true,
			backdropClick : true,
			dialogFade : false,
			keyboard : true,
			templateUrl : "modalAddClient.html",
			controller : "AddClient",
			controllerAs : "$ctrl",
			resolve : {
					  }
		};

		var modalInstance = $uibModal.open($scope.opts);
		modalInstance.result.then(
			function(client) //ok
			{
				$scope.clients.push(client);

			},
			function() //cancel
			{
			});
	};

    $scope.rank = rank;
});

myApp.controller("AddClient", function($scope, $timeout, $uibModalInstance)
{
	$scope.name        = "";
	$scope.description = "";
	$scope.telephone   = "";
	$scope.email       = "";
    $scope.errorMsg    = "";

    $scope.add = function()
    {
        if($scope.name == "")
        {
            $scope.errorMsg = "Le nom ne peut pas être vide";
            return;
        }

        else if($scope.email == "")
        {
            $scope.errorMsg = "L'adresse email ne peut pas être vide";
            return;
        }

		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
                    var client = new ClientInfo(
                        {
                            name : $scope.name,
                            email : $scope.email,
                            description : $scope.description,
                            telephone : $scope.telephone
                        });
                    $uibModalInstance.close(client);
                }
                else
                {
                    $scope.errorMsg = "Une erreur inconnue est survenue";
                    return;
                }
            }
            else
            {
                $scope.errorMsg = "Une erreur inconnue est survenue";
                return;
            }
        }
		httpCtx.open('GET', "/AJAX/addClient.php?name="+encodeURIComponent($scope.name)+"&email="+encodeURIComponent($scope.email)+"&telephone="+encodeURIComponent($scope.telephone)+"&description="+encodeURIComponent($scope.description), true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
    };

    $scope.cancel = function()
    {
        $uibModalInstance.dismiss();
    }
});
