var myApp       = angular.module('myApp', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

class Contact
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

myApp.controller("ContactCtrl", function($scope, $timeout)
{		
	$scope.contacts=[];
	
	console.log("$em : "+$scope.email);
	var httpCtx = new XMLHttpRequest();
	httpCtx.onreadystatechange= function()
	{
		if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
		{
			$scope.$apply(function()
			{
				console.log(httpCtx.responseText)
				
				var contacts = JSON.parse(httpCtx.responseText);
				for(var i in contacts)
				{
					currentContact=new Contact(contacts[i]);
					$scope.contacts.push(currentContact);
				}
			});				
		}			
	}
	
	httpCtx.open('GET', "../AJAX/fetchContacts.php",true);
	httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	httpCtx.send(null);
	// console.log($scope.contacts);
});

		