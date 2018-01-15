

var scope;
var project;

var dateWidth  = 60;
var dateOffset = 5;

class Project
{
	constructor(cpy)
	{
		this.id        = cpy.id;
		this.startDate = new Date(cpy.startDate);
		this.endDate   = new Date(cpy.endDate);
		console.log(cpy.startDate);
		console.log(cpy.endDate);
	}
}

//Task class
class AbstractTask
{
	constructor(cpy)
	{
		this.expand       = true;
		this.internalID   = 0;
		this.id           = cpy.id;
		this.name         = cpy.name;
		this.description  = cpy.description;
		this.predecessors = [];
		this.successors   = [];
		this.children     = [];
		this.mother       = null;
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

	isShowned()
	{
		if(this.mother != null)
			return (this.mother.expand && this.mother.isShowned());
		return true;
	}

	computeInternalId(id)
	{
		this.internalID = id;
		return 1 + this.computeChildrenInternalId(id+1);
	}

	computeChildrenInternalId(id)
	{
		var nbChildren = 0;
		for(var i = 0; i < this.children.length; i++)
			nbChildren = nbChildren + this.children[i].computeInternalId(nbChildren+id);
		return nbChildren;
	}

	getTaskInMousePos(x, y, fontSize)
	{
	}
}

class Task extends AbstractTask
{
	constructor(cpy)
	{
		super(cpy);
		this.advancement = cpy.advancement;
	}

	draw(fontSize)
	{
		var id       = this.internalID;
		var taskNode = document.getElementsByClassName('taskNode')[id];
		var yOffset  = taskNode.offsetTop; 
		var xOffset  = dateOffset + dateWidth * getNbDay(this.startDate, project.startDate) + dateWidth/2.0;
		var width    = dateWidth * (getNbDay(this.endDate, this.startDate) - 1);

		if(scope.selectingTask == this)
		{
			canvasCtx.fillStyle = "#00FFFF";
			drawRoundRect(xOffset-5, yOffset-2, width+10, fontSize+4, dateWidth/4);
			canvasCtx.fill();
		}

		canvasCtx.fillStyle   = "gray";
		drawRoundRect(xOffset, yOffset+2, width, fontSize-4, dateWidth/4);
		canvasCtx.fill();

		canvasCtx.fillStyle   = "#00FF00";
		drawRoundRect(xOffset, yOffset+2, width * this.advancement/100.0, fontSize-4, dateWidth/4);
		canvasCtx.fill();

		this.drawChildren(fontSize);
		this.drawPredecessors(fontSize);
	}

	drawChildren(fontSize)
	{
		if(!this.expand)
			return 0;

		for(var i = 0; i < this.children.length; i++)
		{
			var id         = this.internalID;
			var parentNode = document.getElementsByClassName('taskNode')[id];
			var taskNode   = document.getElementsByClassName('taskNode')[this.children[i].internalID];

			var yOffset    = taskNode.offsetTop; 

			var x     = dateOffset + dateWidth * getNbDay(this.children[i].startDate, project.startDate) + dateWidth/2.0;
			var y     = yOffset - 2;
			var width = dateWidth * getNbDay(this.children[i].endDate, this.children[i].startDate) - dateWidth;

			//Draw the rect
			this.children[i].draw(fontSize);

			//Draw the line
			canvasCtx.beginPath();
            canvasCtx.lineWidth = 3;
			canvasCtx.strokeStyle = "black";
			canvasCtx.moveTo(x + width/2, parentNode.offsetTop + fontSize-2);
			canvasCtx.lineTo(x + width/2, yOffset+2);
			canvasCtx.stroke();
		}
	}

	drawPredecessors(fontSize)
	{
		var id       = this.internalID;
		var taskNode = document.getElementsByClassName('taskNode')[this.internalID];
		var x        = dateOffset + dateWidth * getNbDay(this.startDate, project.startDate) + dateWidth/2.0;
		var yOffset  = taskNode.offsetTop;

        canvasCtx.strokeStyle = "black";
        canvasCtx.lineWidth   = 3;

		for(var i = 0; i < this.predecessors.length; i++)
		{
            if(!this.predecessors[i].isShowned())
                return;
			var idPred      = this.predecessors[i].internalID;
            var predNode    = document.getElementsByClassName('taskNode')[this.predecessors[i].internalID];
			var xPred       = dateOffset + dateWidth * getNbDay(this.predecessors[i].startDate, project.startDate) + dateWidth/2.0;
			var yOffsetPred = predNode.offsetTop; 
			var widthPred   = dateWidth * getNbDay(this.predecessors[i].endDate, this.predecessors[i].startDate) - dateWidth;

            //The line
			canvasCtx.beginPath();
            canvasCtx.moveTo(xPred + widthPred,                 yOffsetPred + fontSize/2.0);
            canvasCtx.lineTo(xPred + widthPred + dateWidth / 2, yOffsetPred + fontSize/2.0);
            canvasCtx.lineTo(xPred + widthPred + dateWidth / 2, yOffset     + fontSize/2.0);
            canvasCtx.lineTo(x,                                 yOffset     + fontSize/2.0);
			canvasCtx.stroke();

            //The sticky arrow
            canvasCtx.beginPath();
            canvasCtx.moveTo(x,     yOffset     + fontSize/2.0);
            canvasCtx.lineTo(x - 5, yOffset     + fontSize/2.0 - 5);
            canvasCtx.moveTo(x,     yOffset     + fontSize/2.0);
            canvasCtx.lineTo(x - 5, yOffset     + fontSize/2.0 + 5);
			canvasCtx.stroke();
		}
	}

	getTaskInMousePos(x, y, fontSize)
	{
		var id       = this.internalID;
		var taskNode = document.getElementsByClassName('taskNode')[id];
		var yOffset  = taskNode.offsetTop; 
		var xOffset  = dateOffset + dateWidth * getNbDay(this.startDate, project.startDate);
		var width    = dateWidth * getNbDay(this.endDate, this.startDate);

		if(x >= xOffset && x <= xOffset + width &&
		  y  >= yOffset && y <= yOffset + fontSize-4)
			return this;
		return this.getTaskChildrenInMousePos(x, y, fontSize);
	}

	getTaskChildrenInMousePos(x, y, fontSize)
	{
		for(var i = 0; i < this.children.length; i++)
		{
			var r = this.children[i].getTaskInMousePos(x, y, fontSize);
			if(r != null)
				return r;
		}
		return null;
	}
}

//Return the number of days between startDate and endDate
function getNbDay(endDate, startDate)
{
	return (endDate.getTime() - startDate.getTime()) / (3600*1000*24);
}

//Return the number of days in the project
function getNbDayProject()
{
	return getNbDay(project.endDate, project.startDate);
}

function getFontSize()
{
	var firstTaskNode = document.getElementsByClassName('taskNode')[0];
	var fontSize      = parseInt(window.getComputedStyle(firstTaskNode, null).getPropertyValue('font-size'));
	return fontSize;
}

function drawRoundRect(x, y, w, h, r)
{ 
	if (w < 2 * r)
		r = w / 2;
	if (h < 2 * r)
		r = h / 2;

	canvasCtx.beginPath();
	canvasCtx.moveTo(x + r, y);
	canvasCtx.arcTo(x + w, y,   x + w, y + h, r);
	canvasCtx.arcTo(x + w, y + h, x,   y + h, r);
	canvasCtx.arcTo(x,   y + h, x,   y,   r);
	canvasCtx.arcTo(x,   y,   x + w, y,   r);
	canvasCtx.closePath();
}

//Redraw the gantt
function redraw()
{
    topY = document.getElementById('taskTreeView').offsetTop;

	//Change the canvas size following the dates
	canvas.width = getNbDayProject() * dateWidth;

	//Clear the canvas
	canvasCtx.beginPath();
	canvasCtx.fillStyle = "white";
	canvasCtx.fillRect(0, 0, canvas.width, canvas.height);
	canvasCtx.fill();

	if(scope.tasks.length == 0)
		return;

	var fontSize = getFontSize();

	drawDate(fontSize);
	drawTasks(fontSize);
}

//Draw all the date.
function drawDate(fontSize)
{
	var currentDate = new Date(project.startDate);
	var nbDays      = getNbDayProject();

	canvasCtx.beginPath();
	canvasCtx.font        = "10pt Arial";
	canvasCtx.fillStyle   = "black";
	for(var i=0; i < nbDays; i++)
	{
		var dateStr = ('00'+currentDate.getDate()).slice(-2) + "/" + ('00' + (currentDate.getMonth()+1)).slice(-2) + "/" + (currentDate.getFullYear()%100);
		canvasCtx.fillText(dateStr, dateOffset + i*dateWidth, 25);
		currentDate.setDate(currentDate.getDate() + 1);
	}
	canvasCtx.fill();
}

//Draw tasks
function drawTasks(fontSize)
{
	for(var i=0; i < scope.tasks.length; i++)
		scope.tasks[i].draw(fontSize);
}

//Computer the tasks' internal ID, i.e the ID in this page (and not in the database)
function computeInternalId()
{
	var currentID = 0;
	console.log(scope.tasks.length);

	for(var i=0; i < scope.tasks.length; i++)
	{
		console.log(i);
		currentID = currentID + scope.tasks[i].computeInternalId(currentID);
	}

}

myApp.controller("ganttProjectCtrl", function($scope, $timeout, $interval)
{
	scope = $scope;

	//Variables
	$scope.currentSorting = 0;
	$scope.dispUnstarted  = 0;
	$scope.sortTask		  = ["date", "nom"];

	$scope.tasks          = [];
	$scope.selectingTask  = null;

	//Init canvas
	canvas    = document.getElementById('ganttCanvas');
	canvasCtx = canvas.getContext('2d');

	//Function called when the gantt tab is opened
	$scope.$on('ganttOpened', function(event, param)
	{
	});

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

	//Function called when the canvas is clicked
	$scope.canvasClick      = function(event)
	{
		//Left click
		if(event.button == 0)
		{
			var fontSize = getFontSize();
			for(var i = 0; i < $scope.tasks.length; i++)
			{
				var r = $scope.tasks[i].getTaskInMousePos(event.offsetX, event.offsetY, getFontSize());
				if(r != null)
				{
					$scope.selectingTask =  r;
					break;
				}
			}
		}
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
				project   = new Project(tasks.project);

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
								{
									allTasks[j].children.push(allTasks[k]);
									allTasks[k].mother = allTasks[j];
								}

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
				computeInternalId();
			});
		}
	}
	httpCtx.open('GET', "/AJAX/fetchTasks.php?projectID="+projectID, true);
	httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	httpCtx.send(null);

	$interval(function()
	{
		redraw();
	}, 100);
});
