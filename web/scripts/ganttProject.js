var scope;
var project;

var dateWidth   = 60;
var dateOffset  = 5;
var dateYOffset = 15;

var currentUnit = "week";

class Project
{
	constructor(cpy)
	{
		this.id        = cpy.id;
		this.startDate = new Date(cpy.startDate);
		this.endDate   = new Date(cpy.endDate);
		this.stats     = cpy.stats;
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

	reduceAll()
	{
		this.expand = false;
		for(var i=0; i < this.children.length; i++)
			this.children[i].reduceAll();
	}

	expandAll()
	{
		this.expand = true;
		for(var i=0; i < this.children.length; i++)
			this.children[i].expandAll();
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

    hideUnstarted(currentDate)
    {
        //Update the visibility component of this task
        var id       = this.internalID;
        var taskNode = document.getElementsByClassName('taskNode')[id];
        if(!this.hasStarted(currentDate))
            taskNode.style.visibility = "hidden";
        else
            taskNode.style.visibility = "visible";

        //Do it for each children
		for(var i = 0; i < this.children.length; i++)
            this.children[i].hideUnstarted(currentDate);
    }

    showAll()
    {
        var id       = this.internalID;
        var taskNode = document.getElementsByClassName('taskNode')[id];
        taskNode.style.visibility = "visible";
        //Do it for each children
		for(var i = 0; i < this.children.length; i++)
            this.children[i].showAll();
    }

    hasStarted(currentDate)
    {
        return currentDate >= this.startDate;
    }
}

class Task extends AbstractTask
{
	constructor(cpy)
	{
		super(cpy);
		this.advancement = cpy.advancement;
	}

	draw(fontSize, unit)
	{
        var task = document.getElementsByClassName('taskNode')[this.internalID];
        if(task.style.visibility == "hidden")
            return;

		var size = this.getTaskSize(unit);

		if(scope.selectingTask == this)
		{
			canvasCtx.fillStyle = "#00FFFF";
			drawRoundRect(size.xOffset-5, size.yOffset-2, size.width+10, fontSize+4, dateWidth/4);
			canvasCtx.fill();
		}

		canvasCtx.fillStyle   = "gray";
		drawRoundRect(size.xOffset, size.yOffset+2, size.width, fontSize-4, dateWidth/4);
		canvasCtx.fill();

		canvasCtx.fillStyle   = "#00FF00";
		drawRoundRect(size.xOffset, size.yOffset+2, size.width * this.advancement/100.0, fontSize-4, dateWidth/4);
		canvasCtx.fill();

		this.drawChildren(fontSize, unit);
		this.drawPredecessors(fontSize, unit);
	}

	drawChildren(fontSize, unit)
	{
		if(!this.expand)
			return 0;

		for(var i = 0; i < this.children.length; i++)
		{
			var id         = this.internalID;
			var parentNode = document.getElementsByClassName('taskNode')[id];
			var size       = this.children[i].getTaskSize(unit);

			//Draw the rect
			this.children[i].draw(fontSize, unit);

			//Draw the line
			canvasCtx.beginPath();
            canvasCtx.lineWidth = 3;
			canvasCtx.strokeStyle = "black";
			canvasCtx.moveTo(size.xOffset + size.width/2, parentNode.offsetTop + fontSize-2);
			canvasCtx.lineTo(size.xOffset + size.width/2, size.yOffset+2);
			canvasCtx.stroke();
		}
	}

	drawPredecessors(fontSize, unit)
	{
		var size      = this.getTaskSize(unit);
		var lineWidth = dateWidth / 2;

        canvasCtx.strokeStyle = "black";
        canvasCtx.lineWidth   = 3;

		if(unit == "week")
			lineWidth /= 7;

		for(var i = 0; i < this.predecessors.length; i++)
		{
            if(!this.predecessors[i].isShowned())
                return;

			var predSize    = this.predecessors[i].getTaskSize(unit);

            //The line
			canvasCtx.beginPath();
            canvasCtx.moveTo(predSize.xOffset + predSize.width,             predSize.yOffset + fontSize/2.0);
            canvasCtx.lineTo(predSize.xOffset + predSize.width + lineWidth, predSize.yOffset + fontSize/2.0);
            canvasCtx.lineTo(predSize.xOffset + predSize.width + lineWidth, size.yOffset     + fontSize/2.0);
            canvasCtx.lineTo(size.xOffset,                                  size.yOffset     + fontSize/2.0);
			canvasCtx.stroke();

            //The sticky arrow
            canvasCtx.beginPath();
            canvasCtx.moveTo(size.x,     size.yOffset + fontSize/2.0);
            canvasCtx.lineTo(size.x - 5, size.yOffset + fontSize/2.0 - 5);
            canvasCtx.moveTo(size.x,     size.yOffset + fontSize/2.0);
            canvasCtx.lineTo(size.x - 5, size.yOffset + fontSize/2.0 + 5);
			canvasCtx.stroke();
		}
	}

	getTaskInMousePos(x, y, fontSize, unit)
	{
		var size = this.getTaskSize(unit);

		if(x >= size.xOffset && x <= size.xOffset + size.width &&
		   y >= size.yOffset && y <= size.yOffset + fontSize-4)
			return this;
		return this.getTaskChildrenInMousePos(x, y, fontSize, unit);
	}

	getTaskChildrenInMousePos(x, y, fontSize, unit)
	{
		for(var i = 0; i < this.children.length; i++)
		{
			var r = this.children[i].getTaskInMousePos(x, y, fontSize, unit);
			if(r != null)
				return r;
		}
		return null;
	}

	getTaskSize(unit)
	{
		var id       = this.internalID;
		var taskNode = document.getElementsByClassName('taskNode')[id];
		var yOffset  = taskNode.offsetTop; 
		var xOffset  = 0;
		var width    = 0;


		if(unit == "day")
		{
			xOffset  = dateOffset + (dateWidth * getNbDay(this.startDate, project.startDate) + dateWidth/2.0);
			width    = dateWidth * (getNbDay(this.endDate, this.startDate) - 1);
		}

		else if(unit == "week")
		{
			var projectDate = new Date(project.startDate);
			projectDate.setDate(projectDate.getDate() - getMondayDiff(project.startDate));

			xOffset  = dateOffset + getNbDay(project.startDate, projectDate)*dateWidth/7 + 
				       (dateWidth * getNbDay(this.startDate, project.startDate) + dateWidth/2.0)/7;
			width    = dateWidth * (getNbDay(this.endDate, this.startDate) - 1)/7;
		}

		return {"yOffset": yOffset, "xOffset": xOffset, "width": width};
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

function getMondayDiff(date)
{
	var day  = date.getDay();
	var diff =  day - 1; // adjust when day is sunday
	return diff;
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

	drawDate(fontSize, currentUnit);
	drawTasks(fontSize, currentUnit);
}

//Draw all the date.
function drawDate(fontSize, unit)
{
	var currentDate = new Date(project.startDate);
	var nbDays      = getNbDayProject();

	canvasCtx.beginPath();
	canvasCtx.font        = "10pt Arial";
	canvasCtx.fillStyle   = "black";

	if(unit == "week")
	{
		currentDate.setDate(currentDate.getDate() - getMondayDiff(currentDate));
		nbDays /= 7;
	}

	for(var i=0; i < nbDays; i++)
	{
		var dateStr = ('00'+currentDate.getDate()).slice(-2) + "/" + ('00' + (currentDate.getMonth()+1)).slice(-2) + "/" + (currentDate.getFullYear()%100);
		canvasCtx.fillText(dateStr, dateOffset + i*dateWidth, dateYOffset);

		if(unit == "week")
			currentDate.setDate(currentDate.getDate() + 7);
		else if(unit == "day")
			currentDate.setDate(currentDate.getDate() + 1);
	}
	canvasCtx.fill();
}

//Draw tasks
function drawTasks(fontSize, diviser)
{
	for(var i=0; i < scope.tasks.length; i++)
		scope.tasks[i].draw(fontSize, diviser);
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
	$scope.currentScale   = 1;
	$scope.dispUnstarted  = true;
	$scope.editionMode    = false;
	$scope.sortTask       = ["Date", "Nom"];
	$scope.scale          = ["Jour", "Semaine"];

	$scope.tasks          = [];
	$scope.selectingTask  = null;

	$scope.actionDiv      = document.getElementById('actionDiv');

    $scope.editionTxt     = "Mode édition";
    $scope.unstartedTxt   = "En cours";

	//Init canvas
	canvas    = document.getElementById('ganttCanvas');
	canvasCtx = canvas.getContext('2d');

	//Function called when the gantt tab is opened
	$scope.$on('ganttOpened', function(event, param)
	{
	});

	//Toolbar functions
	$scope.closeProject = function()
	{
		//TODO
	};

    $scope.unstartedClick = function()
    {
        $scope.dispUnstarted = !$scope.dispUnstarted;
        $scope.unstartedTxt  = ($scope.dispUnstarted) ? "En cours" : "Toutes";

        if($scope.dispUnstarted)
            for(var i = 0; i < $scope.tasks.length; i++)
                $scope.tasks[i].showAll();
        else
        {
            var date = new Date();
            for(var i = 0; i < $scope.tasks.length; i++)
                $scope.tasks[i].hideUnstarted(date);
        }
    };

	$scope.expandTasks = function()
	{
		for(var i=0; i < $scope.tasks.length; i++)
			$scope.tasks[i].expandAll();
	};

	$scope.reduceTasks = function()
	{
		for(var i=0; i < $scope.tasks.length; i++)
			$scope.tasks[i].reduceAll();
	};

	$scope.onNotificationClick = function()
	{
		//TODO
	};

    $scope.onEditionClick = function()
    {
        $scope.editionMode = !$scope.editionMode;
        $scope.editionTxt  = ($scope.editionMode) ? "Quitter Edition" : "Mode Edition";
    };

	$scope.changeSorting = function(id)
	{
		$scope.currentSorting = id;
	};

	$scope.changeScale = function(id)
	{
		$scope.currentScale = id;
		switch(id)
		{
			case 0:
				currentUnit           = "day";
				break;
			case 1:
				currentUnit           = "week";
				break;
		}
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
			$scope.selectingTask = null;
			for(var i = 0; i < $scope.tasks.length; i++)
			{
				var r = $scope.tasks[i].getTaskInMousePos(event.offsetX, event.offsetY, getFontSize(), currentUnit);
				if(r != null)
				{
					$scope.selectingTask = r;
					//Set the position of the action div
					var size = r.getTaskSize(currentUnit);
					$scope.actionDiv.style.left = -$scope.actionDiv.offsetWidth/2 + size.xOffset + size.width / 2 + "px";
					$scope.actionDiv.style.top  = -$scope.actionDiv.offsetHeight  + size.yOffset -5 + "px";

					break;
				}
			}
		}
	};

	$scope.showActionDiv = function()
	{
		return $scope.selectingTask != null;
	};

	//The action buttons
	$scope.changeTaskAdv = function()
	{
	};

	$scope.changeTaskDate = function()
	{
	};

	$scope.changeTaskCollaborator = function()
	{
	};

	$scope.addSubTask = function()
	{
	};

	$scope.addPredecessorTask = function()
	{
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
