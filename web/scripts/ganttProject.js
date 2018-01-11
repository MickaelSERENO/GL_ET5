//Global variables
var canvas;
var canvasCtx;

//Task class
class AbstractTask
{
	constructor()
	{
		this.expand       = true;
		this.id           = id;
		this.name         = name;
		this.description  = "";
		this.predecessors = [];
		this.successors   = [];
		this.children     = [];
		this.counted      = true;
		this.isJalon      = false;
		this.startDate    = new Date();
		this.endDate      = new Date();
	}

	addChild(task)
	{
		this.children.push(task);
	}

	canReduce()
	{
		return this.children.length > 0 && this.expand;
	}

	canExpand()
	{
		return this.children.length > 0 && !this.expand;
	}
}

class Task extends AbstractTask
{
	constructor()
	{
		super();
	}
}

myApp.controller("ganttProjectCtrl", function($scope, $timeout)
{
	//Variables
	$scope.currentSorting = 0;
	$scope.dispUnstarted  = 0;
	$scope.sortTask		  = ["date", "nom"];

	$scope.tasks = [];

	//Init canvas
	canvas    = document.getElementById('ganttCanvas');
	canvasCtx = canvas.getContext('2d');

	//Toolbar functions
	$scope.$watch('dispUnstarted', function()
	{
		//TODO
	});

	$scope.expandTasks = function()
	{
		//TODO
	};

	$scope.reduceTasks = function()
	{
		//TODO
	};

	//Function for tasks tree view
	$scope.toggleExpandTask = function(task)
	{
		task.task.expand = !task.task.expand;
	};

	//Load tasks
	var httpCtx = new XMLHttpRequest();
	httpCtx.onreadystatechange = function()
	{
		if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
		{
			JSON.parse(httpCtx.responseText);
		}
	}
	httpCtx.open("GET", "/AJAX/fetchTasks.php", true);
	httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	httpCtx.send("projectID="+projectID);
	
});
