class ClientInfo
{
	constructor(copy)
	{
		this.name = copy.name;
		this.email=copy.email;
		this.description=copy.description;
		this.contactEmail = copy.contactEmail;
		this.contactTelephone=copy.contactTelephone;
	}
}

class Project
{
	constructor(copy)
	{
		this.managerEmail	=	copy.managerEmail;
		this.contactEmail	=	copy.contactEmail;
		this.name			= 	copy.name;
		this.startDate 		= 	copy.startDate;
		this.endDate		=	copy.endDate;
	}
}

myApp.controller("ClientsCtrl", function($scope, $timeout)
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
					currentClient=new ClientInfo(clients[i]);
					// console.log(currentClient);
					$scope.clients.push(currentClient);
				}
			});				
		}			
	}
	httpCtx.open('GET', "../AJAX/fetchClientsInfo.php",true);
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
		var httpCx = new XMLHttpRequest();
		httpCx.onreadystatechange= function()
		{
			if(httpCx.readyState == 4 && (httpCx.status == 200 || httpCx.status == 0))
			{
				$scope.$apply(function()
				{
					console.log(httpCx.responseText)
					
					var projects = JSON.parse(httpCx.responseText);
					for(var i in projects)
					{
						// console.log(clients[i]);
						currentProject=new Project(projects[i]);
						// console.log(currentClient);
						if(projects[i].contactEmail==$scope.selectedClient.contactEmail)
						{
							$scope.clientProjects.push(currentProject);
						}
					}
				});				
			}			
		}
		// console.log("../AJAX/fetchClientProjects.php?clientEmail="+$scope.selectedClient.contactEmail);
		httpCx.open('GET', "../AJAX/fetchClientProjects.php?clientEmail",true);
		httpCx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCx.send(null);
	
	
	};

	console.log("client projects : "+$scope.clientProjects);
	
});

		