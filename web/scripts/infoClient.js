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

	console.log("client projects : "+$scope.clientProjects);
	
});

		