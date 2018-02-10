var scope;
var project;
var currentUnit = "week";

var dateWidth   = 60;
var dateOffset  = 5;
var dateYOffset = 15;
var markerWidth = 15;

var SELECT_TASK_COLOR = "#0858CC";

//The end user class
class EndUser
{
	constructor(cpy)
	{
		this.name    = cpy.name;
		this.surname = cpy.surname;
		this.email   = cpy.email;
	}
}

//The class project
class Project
{
	//Constructor. cpy : the json result from AJAX request
	constructor(cpy)
	{
		this.id        = cpy.id;
		this.startDate = new Date(cpy.startDate);
		this.endDate   = new Date(cpy.endDate);
		this.stats     = cpy.status;
	}
}

//Task class
class AbstractTask
{
	//Constructor. cpy : the json result from AJAX request
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

	//Add a child
	addChild(task)
	{
		this.children.push(task);
	}

	//Tell if this task can draw a reduce symbol or not
	canReduce()
	{
		return this.children.length > 0 && this.expand;
	}

	//Tell if this task can draw an expand symbol or not
	canExpand()
	{
		return this.children.length > 0 && !this.expand;
	}

	//Reduce the task and its children
	reduceAll()
	{
		this.expand = false;
		for(var i=0; i < this.children.length; i++)
			this.children[i].reduceAll();
	}

	//Expand the task and its children
	expandAll()
	{
		this.expand = true;
		for(var i=0; i < this.children.length; i++)
			this.children[i].expandAll();
	}

	//Tell if this task is currently shown on the screen or not
	isShown()
	{
		if(this.mother != null)
			return (this.mother.expand && this.mother.isShown());
		return true;
	}

	//Compute the internal id (the row ID) following that the task may has children (compute also their ID)
	computeInternalId(id)
	{
		this.internalID = id;
		return 1 + this.computeChildrenInternalId(id+1);
	}

	//compyte the children ID and their children
	computeChildrenInternalId(id)
	{
		var nbChildren = 0;
		for(var i = 0; i < this.children.length; i++)
			nbChildren = nbChildren + this.children[i].computeInternalId(nbChildren+id);
		return nbChildren;
	}

	//Abstract function the know which task is under (x, y) coordinate
	getTaskInMousePos(x, y, fontSize)
	{
	}

	//Hide unstarted task (and recursively unstarted children)
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

	//Show the task and its children
    showAll()
    {
        var id       = this.internalID;
        var taskNode = document.getElementsByClassName('taskNode')[id];
        taskNode.style.visibility = "visible";
        //Do it for each children
		for(var i = 0; i < this.children.length; i++)
            this.children[i].showAll();
    }

	//Tell if the task has started or not
    hasStarted(currentDate)
    {
        return currentDate >= this.startDate;
    }

	updateParentTime()
	{
		if(this.mother)
		{
			var start = new Date(this.startDate);
			var end   = new Date(this.endDate);
			for(var i=0; i < this.mother.children.length; i++)
			{
				if(start.getTime() > this.mother.children[i].startTime.getTime())
					start = this.mother.children[i].startTime;
				if(end.getTime() < this.mother.children[i].endTime.getTime())
					end = this.mother.children[i].endTime;
			}

			this.startDate = start;
			this.endDate   = end;

			this.mother.updateParentTime();
		}
	}

	sortByDate(asc)
	{
		var tasks = [];
		for(var i =0; i < this.children.length; i++)
		{
			var found = false;
			for(var j = 0; j < i; j++)
			{
				if(asc)
				{
					if(tasks[j].startDate.getTime() > this.children[i].startDate.getTime())
					{
						found = true;
						tasks.splice(j, 0, this.children[i]);
						break;
					}
				}

				else
				{
					if(tasks[j].startDate.getTime() < this.children[i].startDate.getTime())
					{
						found = true;
						tasks.splice(j, 0, this.children[i]);
						break;
					}
				}
			}
			if(!found)
				tasks.push(this.children[i]);
			this.children[i].sortByDate(asc);
		}
		this.children = tasks;
	}

	sortByName(asc)
	{
		var tasks = [];
		for(var i =0; i < this.children.length; i++)
		{
			var found = false;
			for(var j = 0; j < i; j++)
			{
				if(asc)
				{
					if(tasks[j].name > this.children[i].name)
					{
						found = true;
						tasks.splice(j, 0, this.children[i]);
						break;
					}
				}
				else
				{
					if(tasks[j].name < this.children[i].name)
					{
						found = true;
						tasks.splice(j, 0, this.children[i]);
						break;
					}
				}
			}
			if(!found)
				tasks.push(this.children[i]);
			this.children[i].sortByName(asc);
		}
		this.children = tasks;
	}

	//Draw the predecessors's arrow
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
            if(!this.predecessors[i].isShown())
                return;

			var predSize    = this.predecessors[i].getTaskSize(unit);

            //The line
			canvasCtx.beginPath();
            canvasCtx.moveTo(predSize.xOffset + predSize.width,             predSize.yOffset + fontSize/2.0);
            canvasCtx.lineTo(predSize.xOffset + predSize.width + lineWidth, predSize.yOffset + fontSize/2.0);
            canvasCtx.lineTo(predSize.xOffset + predSize.width + lineWidth, size.yOffset     + fontSize/2.0);
            canvasCtx.lineTo(size.xOffset,                                  size.yOffset     + fontSize/2.0);
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
		return {"yOffset": 0, "xOffset": 0, "width": 0};
	}
}

class Task extends AbstractTask
{
	//Contructor. cpy : json object from AJAX request
	constructor(cpy)
	{
		super(cpy);
		this.advancement       = cpy.advancement;
		this.collaboratorEmail = cpy.collaboratorEmail;
		this.initCharge        = cpy.initCharge;
		this.computedCharge    = cpy.computedCharge;
		this.chargeConsumed    = cpy.chargeConsumed;
		this.remaining         = cpy.remaining;
	}

	minDate()
	{
		var minDate = project.startDate;
		for(var i=0; i < this.predecessors.length; i++)
			if(this.predecessors[i].endDate.getTime() > minDate.getTime())
				minDate = this.predecessors[i].endDate;
		return minDate;
	}


	maxDate()
	{
		var maxDate = project.endDate;
		for(var i=0; i < this.successors.length; i++)
			if(this.successors[i].startDate.getTime() < maxDate.getTime())
				maxDate = this.successors[i].startDate;
		return maxDate;
	}

	//Draw the task and its children.
	draw(fontSize, unit)
	{
        var task = document.getElementsByClassName('taskNode')[this.internalID];
        if(task.style.visibility == "hidden")
            return;

		var size = this.getTaskSize(unit);

		if(scope.taskSelected == this || scope.secondTaskSelected == this)
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

	//Recursive function used to draw the children recursively
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

class Marker extends AbstractTask
{
	//Draw to marker
	draw(fontSize, unit)
	{
        var task = document.getElementsByClassName('taskNode')[this.internalID];
        if(task.style.visibility == "hidden")
            return;

		var size = this.getTaskSize(unit);
		if(scope.taskSelected == this)
		{
			canvasCtx.fillStyle = "#00FFFF";
			canvasCtx.beginPath();
			canvasCtx.moveTo(size.xOffset + size.width/2.0, size.yOffset - 5);
			canvasCtx.lineTo(size.xOffset - 5,              size.yOffset + fontSize/2.0 );
			canvasCtx.lineTo(size.xOffset + size.width/2.0, size.yOffset + fontSize + 5);
			canvasCtx.lineTo(size.xOffset + size.width + 5, size.yOffset + fontSize/2.0);
			canvasCtx.lineTo(size.xOffset + size.width/2.0, size.yOffset - 5);
			canvasCtx.fill();
		}

		canvasCtx.fillStyle   = "black";
		canvasCtx.beginPath();
		canvasCtx.moveTo(size.xOffset + size.width/2.0, size.yOffset);
		canvasCtx.lineTo(size.xOffset,                  size.yOffset + fontSize/2.0);
		canvasCtx.lineTo(size.xOffset + size.width/2.0, size.yOffset + fontSize);
		canvasCtx.lineTo(size.xOffset + size.width,     size.yOffset + fontSize/2.0);
		canvasCtx.lineTo(size.xOffset + size.width/2.0, size.yOffset);
		canvasCtx.fill();

		this.drawPredecessors(fontSize, unit);
	}

	getTaskSize(unit)
	{
		var id       = this.internalID;
		var taskNode = document.getElementsByClassName('taskNode')[id];
		var yOffset  = taskNode.offsetTop; 
		var xOffset  = 0;
		var width    = markerWidth;


		if(unit == "day")
		{
			xOffset  = dateOffset + (dateWidth * getNbDay(this.startDate, project.startDate) + dateWidth/2.0) - width/2;
		}

		else if(unit == "week")
		{
			var projectDate = new Date(project.startDate);
			projectDate.setDate(projectDate.getDate() - getMondayDiff(project.startDate));

			xOffset  = dateOffset + getNbDay(project.startDate, projectDate)*dateWidth/7 + 
				       (dateWidth * getNbDay(this.startDate, project.startDate) + dateWidth/2.0)/7 - width/2;
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
	if(currentUnit == "day")
		canvas.width = getNbDayProject() * dateWidth + 50;
	else if(currentUnit == "week")
		canvas.width = getNbDayProject() * dateWidth / 7.0 + 100;


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

	for(var i=0; i < scope.tasks.length; i++)
		currentID = currentID + scope.tasks[i].computeInternalId(currentID);
}

myApp.controller("ganttProjectCtrl", function($scope, $uibModal, $timeout, $interval)
{
	scope = $scope;

	//Variables
	$scope.currentSorting     = 0;
	$scope.asc                = true;
	$scope.currentScale       = 1;
	$scope.dispUnstarted      = true;
	$scope.editionMode        = false;
	$scope.sortTask           = ["Date", "Nom"];
	$scope.scale              = ["Jour", "Semaine"];

	$scope.tasks              = [];
	$scope.taskSelected       = null;
	$scope.secondTaskSelected = null;

	$scope.actionDiv          = document.getElementById('actionDiv');

	$scope.editionTxt         = "Mode édition";
	$scope.unstartedTxt       = "En cours";

	$scope.closeStatus        = 0;
	$scope.closeTxt           = "Clôturer";

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
		var httpCtx = new XMLHttpRequest();

		//Close
		if($scope.closeStatus == 0)
		{
			httpCtx.onreadystatechange = function()
			{
				if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
				{
					if(httpCtx.responseText != '1')
						alert("An unknown error occured");
					else
					{
						$scope.$apply(function()
						{
							$scope.closeStatus = 1;
							$scope.closeTxt    = "Ré-Ouvrir";
						});
					}
				}
			}
			httpCtx.open('GET', "/AJAX/closeProject.php?projectID="+projectID+'&requestID=0', true);
		}

		//Open
		else
		{
			httpCtx.onreadystatechange = function()
			{
				if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
				{
					if(httpCtx.responseText != '1')
						alert("An unknown error occured");
					else
					{
						$scope.$apply(function()
						{
							$scope.closeStatus = 0;
							$scope.closeTxt    = "Clôturer";
						});
					}
				}
			}
			httpCtx.open('GET', "/AJAX/closeProject.php?projectID="+projectID+'&requestID=1', true);
		}

		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
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

	$scope.changeAsc     = function()
	{
		$scope.asc = !$scope.asc;
		$scope.changeSorting($scope.currentSorting);
	}

	$scope.changeSorting = function(id)
	{
		$scope.currentSorting = id;
		switch(id)
		{
			case 0: //By date
				var tasks = [];
				for(var i =0; i < $scope.tasks.length; i++)
				{
					var found = false;
					for(var j = 0; j < i; j++)
					{
						if($scope.asc)
						{
							if(tasks[j].startDate.getTime() > $scope.tasks[i].startDate.getTime())
							{
								found = true;
								tasks.splice(j, 0, $scope.tasks[i]);
								break;
							}
						}
						else
						{
							if(tasks[j].startDate.getTime() < $scope.tasks[i].startDate.getTime())
							{
								found = true;
								tasks.splice(j, 0, $scope.tasks[i]);
								break;
							}
						}
					}
					if(!found)
						tasks.push($scope.tasks[i]);
					$scope.tasks[i].sortByDate($scope.asc);
				}
				$scope.tasks = tasks;
				
				break;
			case 1: //By name
				var tasks = [];
				for(var i =0; i < $scope.tasks.length; i++)
				{
					var found = false;
					for(var j = 0; j < i; j++)
					{
						if($scope.asc)
						{
							if(tasks[j].name > $scope.tasks[i].name)
							{
								found = true;
								tasks.splice(j, 0, $scope.tasks[i]);
								break;
							}
						}
						else
						{
							if(tasks[j].name < $scope.tasks[i].name)
							{
								found = true;
								tasks.splice(j, 0, $scope.tasks[i]);
								break;
							}
						}
					}
					if(!found)
						tasks.push($scope.tasks[i]);
					$scope.tasks[i].sortByName($scope.asc);
				}
				$scope.tasks = tasks;
				break;
		}
		computeInternalId();
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

	$scope.projectClosed = function()
	{
		return project == null || $scope.closeStatus == 1;
	};

	//Function for tasks tree view
	$scope.toggleExpandTask = function(task)
	{
		task.task.expand = !task.task.expand;
	};

	//Function called when the canvas is clicked
    $scope.onSelectTask     = function(r)
    {
        //Reset selecting task
        if($scope.taskSelected != null)
            document.getElementsByClassName('taskNode')[$scope.taskSelected.internalID].getElementsByClassName('taskBackground')[0].style.backgroundColor = "transparent";
        if($scope.secondTaskSelected != null)
            document.getElementsByClassName('taskNode')[$scope.secondTaskSelected.internalID].getElementsByClassName('taskBackground')[0].style.backgroundColor = "transparent";

        $scope.taskSelected       = r;
        $scope.secondTaskSelected = null;

        if(r != null)
        {
            document.getElementsByClassName('taskNode')[$scope.taskSelected.internalID].getElementsByClassName('taskBackground')[0].style.backgroundColor = SELECT_TASK_COLOR;
            $scope.positionActionDiv();
        }
    };

    $scope.selectTask = function(r, event)
    {
        if(event.ctrlKey)
        {
            if($scope.taskSelected == null)
                $scope.onSelectTask(r);
            else if(r != null)
            {
                if($scope.secondTaskSelected != null)
                    document.getElementsByClassName('taskNode')[$scope.secondTaskSelected.internalID].getElementsByClassName('taskBackground')[0].style.backgroundColor = "transparent";
                $scope.secondTaskSelected = r;
                document.getElementsByClassName('taskNode')[$scope.secondTaskSelected.internalID].getElementsByClassName('taskBackground')[0].style.backgroundColor = SELECT_TASK_COLOR;
            }
        }

        else
        {
            $scope.onSelectTask(r);
        }
        event.preventDefault();
        event.stopPropagation();
    };

	$scope.canvasClick = function(event)
	{
		//Left click
		if(event.button == 0)
		{
            var r = null;
            for(var i = 0; i < $scope.tasks.length; i++)
            {
                r = $scope.tasks[i].getTaskInMousePos(event.offsetX, event.offsetY, getFontSize(), currentUnit);
                if(r != null)
                    break;
            }
            $scope.selectTask(r, event);
		}
	};

	$scope.showActionDiv = function()
	{
		return $scope.taskSelected != null && $scope.taskSelected instanceof(Task) && (rank == 2 || rank == 1 || (rank == 0 && email == $scope.taskSelected.collaboratorEmail)) && $scope.taskSelected.isShown();
	};

	$scope.updateTask = function()
	{
		//Load tasks
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				$scope.$apply(function()
				{
					var tasks = JSON.parse(httpCtx.responseText);

					//Handle project
					project   = new Project(tasks.project);
					if(project.stats == "CLOSED_INVISIBLE" || project.stats == "CLOSED_VISIBLE")
					{
						$scope.closeStatus = 1;
						$scope.closeTxt    = "Ré-ouvrir";
					}

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

						var currentTask = null;
						if(tasks.tasks[i].isMarker)
							currentTask = new Marker(tasks.tasks[i]);
						else
							currentTask = new Task(tasks.tasks[i]);
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
					$scope.changeSorting(0);
				});
			}
		}
		httpCtx.open('GET', "/AJAX/fetchTasks.php?projectID="+projectID, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	}
	$scope.updateTask();

	//Modals
	//open task advancement
	$scope.openTaskAdv = function()
	{
		$scope.opts = 
		{
			backdrop : true,
			backdropClick : true,
			dialogFade : false,
			keyboard : true,
			templateUrl : "modalAdv.html",
			controller : "AdvModal",
			controllerAs : "$ctrl",
			resolve : {task    : function() {return $scope.taskSelected;}
					  }
		};

		var selected = $scope.taskSelected;
		var modalInstance = $uibModal.open($scope.opts);
		modalInstance.result.then(
			function(taskCpy) //ok
			{
				selected.advancement    = taskCpy.advancement;
				selected.remaining      = taskCpy.remaining;
				selected.chargeConsumed = taskCpy.chargeConsumed;
			}, 
			function() //cancel
			{
			});
	};

	//The collaborator modal
	$scope.openCollModal = function()
	{
		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				if(httpCtx.responseText != '-1')
				{
					//Parse the json result (fetch collaborators)
					var jsonColls = JSON.parse(httpCtx.responseText);
					var colls     = new Array();
					for(var i=0; i < jsonColls.length; i++)
					{
						var endUser = new EndUser(jsonColls[i]);
						colls.push(endUser);
					}

					$scope.opts = 
					{
						backdrop : true,
						backdropClick : true,
						dialogFade : false,
						keyboard : true,
						templateUrl : "modalColl.html",
						controller : "CollaboratorModal",
						controllerAs : "$ctrl",
						resolve : {colls   : function() {return colls;},
								   project : function() {return project;},
								   task    : function() {return $scope.taskSelected;}
								  }
					};

					var modalInstance = $uibModal.open($scope.opts);
					modalInstance.result.then(
						function() //ok
						{
							$scope.updateTask();
						}, 
						function() //cancel
						{
						});
				}
			}
		}
		httpCtx.open('GET', "/AJAX/projectColls.php?projectID="+projectID+"&requestID=0", true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
	};

	//The date modal
	$scope.openDateModal = function()
	{
		var minDate = $scope.taskSelected.minDate();
		var maxDate = $scope.taskSelected.maxDate();

		$scope.opts = 
		{
			backdrop : true,
			backdropClick : true,
			dialogFade : false,
			keyboard : true,
			templateUrl : "modalDate.html",
			controller : "DateModal",
			controllerAs : "$ctrl",
			resolve : {task    : function() {return $scope.taskSelected;},
					   project : function() {return project;},
					   minDate : function() {return minDate;},
					   maxDate : function() {return maxDate;}
					  }
		};

		var modalInstance = $uibModal.open($scope.opts);
		var selected = $scope.taskSelected;
		modalInstance.result.then(
			function(result) //ok
			{
				selected.startDate = new Date(result.startTime);
				selected.endDate   = new Date(result.endTime);
				selected.updateParentTime();
			}, 
			function() //cancel
			{
			});
	};

	$scope.openSuccessor = function()
	{
        if($scope.secondTaskSelected != null)
        {
			var second = $scope.secondTaskSelected;
			var first  = $scope.taskSelected;
            var httpCtx = new XMLHttpRequest();
            httpCtx.onreadystatechange = function()
            {
                //Check for errors
                if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
                {
                    if(httpCtx.responseText != '-1')
                    {
                        second.predecessors.push(first);
                        first.successors.push(second);
                    }
                }
            }
            httpCtx.open('GET', "/AJAX/predecessorTask.php?projectID="+projectID+"&requestID=0&idPred=" + $scope.taskSelected.id + "&idSucc=" + $scope.secondTaskSelected.id, true);
            httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            httpCtx.send(null);
        }
        else
        {
			var selected = $scope.taskSelected;
            $scope.opts = 
            {
                backdrop : true,
                backdropClick : true,
                dialogFade : false,
                keyboard : true,
                templateUrl : "modalSuccessor.html",
                controller : "SuccessorModal",
                controllerAs : "$ctrl",
                resolve : {tasks : function() {return $scope.tasks;},
                           task  : function() {return $scope.taskSelected;}
                          }
            };

            var modalInstance = $uibModal.open($scope.opts);
            modalInstance.result.then(
                function(pred) //ok
                {
                    selected.predecessors.push(pred);
                    pred.successors.push(selected);
                },
                function() //cancel
                {
                });
        }
	};


	$scope.openTask = function(task)
	{
		if(task == null)
			return;

		$scope.opts = 
		{
			backdrop : true,
			backdropClick : true,
			dialogFade : false,
			keyboard : true,
			templateUrl : "modalTask.html",
			controller : "TaskModal",
			controllerAs : "$ctrl",
			resolve : {task    : function() {return task;}
					  }
		};

		var modalInstance = $uibModal.open($scope.opts);
		modalInstance.result.then(
			function()
			{
			},
			function() //cancel
			{
			});
	};

	$scope.openCanvasTaskModal = function(event)
	{
		$scope.canvasClick(event);
		$scope.openTask($scope.taskSelected);
	}

	$scope.positionActionDiv = function()
	{
		//Set the position of the action div
		if($scope.taskSelected != null)
		{
			var size = $scope.taskSelected.getTaskSize(currentUnit);
			$scope.actionDiv.style.left = -$scope.actionDiv.offsetWidth/2 + size.xOffset + size.width / 2 + "px";
			$scope.actionDiv.style.top  = -$scope.actionDiv.offsetHeight  + size.yOffset -5 + "px";
		}
	}

	$interval(function()
	{
		$scope.positionActionDiv();
		redraw();
	}, 100);
});
