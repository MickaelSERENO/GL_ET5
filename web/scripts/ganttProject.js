function Task(id, name)
{
	this.expend   = true;
	this.id       = id;
	this.name     = name;
	this.children = [];
}

Task.prototype.addChild = function(task)
{
	this.children.push(task);
};

Task.prototype.canReduce = function()
{
	return this.children.length > 0 && this.expend;
};

Task.prototype.canExpand = function()
{
	return this.children.length > 0 && !this.expend;
};

myApp.controller("ganttProjectCtrl", function($scope, $timeout)
{
	$scope.currentSorting = 0;
	$scope.sortTask		  = ["date", "nom"];

	$scope.tasks = [new Task(0, "task1"), new Task(1, "task2")];
	$scope.tasks[0].children = [new Task(2, "task1.1"), new Task(3, "task1.2")];
	$scope.tasks[1].children = [new Task(4, "task2.1"), new Task(5, "task2.2")];

	$scope.toggleExpendTask = function(task)
	{
		task.task.expend = !task.task.expend;
	};
/*
	$scope.expendAction = function(expend)
	{
		//Expend
		if(expend)
		{
		}
		//
		else
		{
		}
	};
*/
});
