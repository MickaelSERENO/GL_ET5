//Global variables
var canvas;
var canvasCtx;

//Task class
class AbstractTask
{
	constructor(cpy)
	{
		this.expand       = true;
		this.id           = cpy.id;
		this.name         = cpy.name;
		this.description  = cpy.description;
		this.predecessors = [];
		this.successors   = [];
		this.children     = [];
		this.counted      = true;
		this.isMarker     = cpy.isMarker;
		this.startDate    = new Date(cpy.startDate);
		this.endDate      = new Date(cpy.endDate);
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
	constructor(cpy)
	{
		super(cpy);
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
		console.log("toggle");
		task.task.expand = !task.task.expand;
	};

	//Load tasks
	var httpCtx = new XMLHttpRequest();
	httpCtx.onreadystatechange = function()
	{
		if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
		{
			$scope.$apply(function()
			{
				var tasks = JSON.parse(httpCtx.responseText);
				console.log(tasks);

				$scope.tasks = [];
				var allTasks = [];

				//Construct all tasks
				for(var i=0; i < tasks.tasks.length; i++)
				{
					var isMother = true;
					for(var j=0; j < tasks.children.length; j++)
					{
						if(tasks.children[j].idChild === tasks.tasks[i].id)
						{
							isMother = false;
							break;
						}
					}

					var currentTask = new Task(tasks.tasks[i]);
					allTasks.push(currentTask);

					if(isMother)
						$scope.tasks.push(currentTask);
				}

				//Construct chldren-tree
				for(var i=0; i < tasks.children.length; i++)
					for(var j=0; j < allTasks.length; j++)
						if(tasks.children[i].idMother === allTasks[j].id)
							for(var k=0; k < allTasks.length; k++)
								if(allTasks[k].id === tasks.children[i].idChild)
									allTasks[j].children.push(allTasks[k]);

				//Fill the successors
				for(var i=0; i < tasks.successors.length; i++)
					for(var j=0; j < allTasks.length; j++)
						if(tasks.successors[i][0] === allTasks[j].id)
							for(var k=0; k < allTasks.length; k++)
								if(allTasks[k].id === tasks.successors[i][1])
								{
									allTasks[j].successors.push(allTasks[k]);
									allTasks[k].predecessors.push(allTasks[j]);
								}

				console.log($scope.tasks);
			});
		}
	}
	httpCtx.open('GET', "/AJAX/fetchTasks.php?projectID="+projectID, true);
	httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	httpCtx.send(null);
	
});
