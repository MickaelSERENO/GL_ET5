<?php
	require_once __DIR__.'/PSQLDatabase.php';
	require_once __DIR__.'/ProjectRqst.php';
	require_once __DIR__.'/TimerRqst.php';

	class IndeTask
	{
		public $project;
		public $task;
	}

	class AbstractTask
	{
		public $id;
		public $name;
		public $description;
		public $startDate;
		public $endDate;
        public $isMarker;
		public $stats;
		public $projectID;
	}

	class Marker extends AbstractTask
	{
	}

	class Task extends AbstractTask
	{
		public $initCharge;
		public $computedCharge;
		public $remaining;
		public $chargeConsumed;
		public $advancement;
		public $collaboratorEmail;
	}

    class TaskHierarchy
    {
        public $idMother;
        public $idChild;
        public $isCounted;

        public function __construct($idM, $idC, $isC)
        {
            $this->idMother  = (int)$idM;
            $this->idChild   = (int)$idC;
            $this->isCounted = (bool)$isC;
        }
    }

    class FetchTask
    {
        public $project;
        public $tasks;
        public $successors;
        public $children;
    }

	class TaskRqst extends PSQLDatabase
	{
		public function getTask($idTask)
		{
			//Fetch tasks
			$script = "SELECT AbstractTask.id, name, description, startDate, 
					   endDate, initCharge, computedCharge, remaining, chargeConsumed, advancement, collaboratorEmail, status, idProject
					   FROM AbstractTask INNER JOIN Task ON AbstractTask.id = Task.id
					   WHERE Task.id = $idTask;";

			$resultScript = pg_query($this->_conn, $script);
			if($row = pg_fetch_row($resultScript))
			{
				$task                    = new Task();
				$task->id                = (int)($row[0]);
				$task->name              = $row[1];
				$task->description       = $row[2];
				$task->startDate         = $row[3];
				$task->endDate           = $row[4];
				$task->initCharge        = (int)($row[5]);
				$task->computedCharge    = (int)($row[6]);
				$task->remaining         = (int)($row[7]);
				$task->chargeConsumed    = (int)($row[8]);
				$task->advancement       = (int)($row[9]);
				$task->collaboratorEmail = $row[10];
				$task->isMarker          = false;
				$task->stats             = $row[11];
				$task->idProject         = $row[12];

				return $task;
			}
			return null;
		}
		public function getTasks($idProject)
		{
			$project    = null;
			$markers    = array();
			$tasks      = array();
            $fullTasks  = array();
            $children   = array();
            $successors = array();

			//Fetch markers
			$script = "SELECT AbstractTask.id, name, description, startDate
					   FROM AbstractTask INNER JOIN Marker ON AbstractTask.id = Marker.id
					   WHERE idProject = $idProject
					   ORDER BY startDate;";

			$resultScript = pg_query($this->_conn, $script);
			while($row = pg_fetch_row($resultScript))
			{
				$marker              = new Marker();
				$marker->id          = (int)($row[0]);
				$marker->name        = $row[1];
				$marker->description = $row[2];
				$marker->startDate   = $row[3];
				$marker->endDate     = $row[3];
                $marker->isMarker    = true;

				array_push($markers, $marker);
			}

			//Fetch tasks
			$script = "SELECT AbstractTask.id, name, description, startDate, 
					   endDate, initCharge, computedCharge, remaining, chargeConsumed, advancement, collaboratorEmail, status
					   FROM AbstractTask INNER JOIN Task ON AbstractTask.id = Task.id
					   WHERE idProject = $idProject
					   ORDER BY startDate;";

			$resultScript = pg_query($this->_conn, $script);
			while($row = pg_fetch_row($resultScript))
			{
				$task                    = new Task();
				$task->id                = (int)($row[0]);
				$task->name              = $row[1];
				$task->description       = $row[2];
				$task->startDate         = $row[3];
				$task->endDate           = $row[4];
				$task->initCharge        = (int)($row[5]);
				$task->computedCharge    = (int)($row[6]);
				$task->remaining         = (int)($row[7]);
				$task->chargeConsumed    = (int)($row[8]);
				$task->advancement       = (int)($row[9]);
				$task->collaboratorEmail = $row[10];
				$task->isMarker          = false;
				$task->stats             = $row[11];

				array_push($tasks, $task);
			}

            //Merge markers and tasks
            $markerID = 0;
            $taskID   = 0;

            while($markerID < count($markers) && $taskID < count($tasks))
            {
                if($tasks[$taskID]->startDate < $markers[$markerID]->startDate)
                {
                    array_push($fullTasks, $tasks[$taskID]);
                    $taskID = $taskID + 1;
                }

                else
                {
                    array_push($fullTasks, $markers[$markerID]);
                    $markerID = $markerID + 1;
                }
            }

			while($markerID != count($markers) || $taskID != count($tasks))
			{
				if($markerID >= count($markers))
				{
					array_push($fullTasks, $tasks[$taskID]);
					$taskID = $taskID + 1;
				}

				else
				{
					array_push($fullTasks, $markers[$markerID]);
					$markerID = $markerID + 1;
				}
			}

			//Fetch successors
            $script       = "SELECT id, successorID FROM AbstractTask INNER JOIN TaskOrder ON AbstractTask.id = TaskOrder.predecessorID
                             WHERE idProject = $idProject;";

            $resultScript = pg_query($this->_conn, $script);
			while($row = pg_fetch_row($resultScript))
                array_push($successors, [(int)($row[0]), (int)($row[1])]);
			
			//Fetch children
            $script = "SELECT idMother, idChild, counted FROM AbstractTask INNER JOIN TaskHierarchy ON AbstractTask.id = TaskHierarchy.idMother
                       WHERE idProject = $idProject;";

			$resultScript = pg_query($this->_conn, $script);
			while($row = pg_fetch_row($resultScript))
                array_push($children, new TaskHierarchy($row[0], $row[1], $row[2] == "t"));

            //Fetch project information
			$script = "SELECT id, managerEmail, contactEmail, name, description, startDate, endDate, status
					   FROM Project WHERE Project.id = $idProject";

			$resultScript = pg_query($this->_conn, $script);
			$row = pg_fetch_row($resultScript);
			if($row != null)
			{
				$project               = new Project();
				$project->id           = (int)($row[0]);
				$project->managerEmail = $row[1];
				$project->contactEmail = $row[2];
				$project->name         = $row[3];
				$project->description  = $row[4];
				$project->startDate    = $row[5];
				$project->endDate      = $row[6];
				$project->status       = $row[7];
			}

            //Compile everything into an object and return it
            $result = new FetchTask();
            $result->project    = $project;
            $result->tasks      = $fullTasks;
            $result->children   = $children;
            $result->successors = $successors;

            return $result;
		}

		public function setCollaborator($idTask, $collEmail, $middleTimestamp)
		{
			$task         = $this->getTask($idTask);
			if($collEmail == $task->collaboratorEmail)
				return;

			$middleDate   = new DateTime();
			$middleDate->setTimestamp((int)($middleTimestamp));
			$middleFormat = $middleDate->format("Y-m-d");
			if($collEmail == $task->collaboratorEmail)
				return;

			$colName      = "";

			//New coll
			$scriptCol    = "SELECT name, surname FROM Contact WHERE email = '$collEmail';";
			$collEmail    = $collEmail == "" ? "NULL" : "'".$collEmail."'";
			$resultScript = pg_query($this->_conn, $scriptCol);
			$rowNew       = pg_fetch_row($resultScript);

			//Old coll
			$scriptCol    = "SELECT name, surname FROM Contact WHERE email = '$task->collaboratorEmail';";
			$resultScript = pg_query($this->_conn, $scriptCol);
			$rowOld       = pg_fetch_row($resultScript);

			$script       = "";

			if($task->computedCharge > 0 && $rowOld != null)
			{
				$desc1        = $task->description.". Parte 1 r??alis??e par $rowOld[0] $rowOld[1].";
				$name1        = $task->name . ". Partie 1.";
				$desc2        = $task->description.". Parte 2.";
				$name2        = $task->name . ". Partie 2.";

				//Add old task
				$script       = "
								 INSERT INTO AbstractTask(idProject, name, description, startDate) 
								 VALUES($task->idProject, '$name1', '$desc1', '$task->startDate')
								 RETURNING id;";
				$resultScript = pg_query($this->_conn, $script);
				$rowInsert    = pg_fetch_row($resultScript);
								 
				$script		  =	"INSERT INTO Task(id, endDate, initCharge, computedCharge, remaining, chargeConsumed, advancement, collaboratorEmail, status) 
								 VALUES($rowInsert[0], '$middleFormat', $task->chargeConsumed, $task->chargeConsumed, 0, $task->chargeConsumed, 100, '$task->collaboratorEmail', 'STARTED');
								 INSERT INTO TaskHierarchy VALUES($idTask, $rowInsert[0], false);";
				$resultScript = pg_query($this->_conn, $script);

				//Add new task
				$initCharge     = $task->initCharge     - $task->chargeConsumed;
				$computedCharge = $task->computedCharge - $task->chargeConsumed;
				$script       = "INSERT INTO AbstractTask(idProject, name, description, startDate) 
								 VALUES($task->idProject, '$name2', '$desc2', '$middleFormat')
								 RETURNING id;";
				$endTime      = $middleTimestamp + $task->remaining*24*3600;
				$endDate      = new DateTime();
				$endDate->setTimestamp((int)($endTime));
				$endFormat    = $endDate->format("Y-m-d");

				$resultScript = pg_query($this->_conn, $script);
				$rowInsert    = pg_fetch_row($resultScript);
				$script		  =	"INSERT INTO Task(id, endDate, initCharge, computedCharge, remaining, chargeConsumed, advancement, collaboratorEmail, status) 
								 VALUES($rowInsert[0], '$endFormat', $initCharge, $computedCharge, $computedCharge, 0, 0, $collEmail, 'STARTED');
								 INSERT INTO TaskHierarchy VALUES($idTask, $rowInsert[0], false);
								 ";
				$resultScript = pg_query($this->_conn, $script);

				//TODO notification changement of collaborators
			}
			else
			{
				$script = "UPDATE Task SET collaboratorEmail = $collEmail WHERE Task.id = $idTask;";
				$resultScript = pg_query($this->_conn, $script);
				//TODO assignement of a collaborator
			}


			return true;
		}

		public function isTaskDateValide($idTask, $startDate, $endDate)
		{
			$startDate->setTime(0,0,0);
			$endDate->setTime(0,0,0);

			//Get the project date
			$start = null;
			$end   = null;

			$script       = "SELECT Project.startDate, Project.endDate FROM Project, AbstractTask WHERE AbstractTask.id = $idTask AND AbstractTask.idProject = Project.id";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			if($row == null)
				return false;

			$start = DateTime::createFromFormat("Y-m-d H:i:s", $row[0] . " 00:00:00", new DateTimeZone("UTC"));
			$end   = DateTime::createFromFormat("Y-m-d H:i:s", $row[1] . " 00:00:00", new DateTimeZone("UTC"));

			//Get the minimum successor date
			$script       = "SELECT MIN(startDate) FROM AbstractTask, TaskOrder WHERE TaskOrder.predecessorID = $idTask AND AbstractTask.id = TaskOrder.successorID;";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			if($row[0] != "")
				$end   = DateTime::createFromFormat("Y-m-d H:i:s", $row[0] . " 00:00:00", new DateTimeZone("UTC"));

			//Get the maximum predecessor date
			$script = "SELECT MAX(endDate) FROM Task, TaskOrder WHERE TaskOrder.successorID = $idTask AND Task.id = TaskOrder.predecessorID;";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			if($row[0] != "")
				$start = DateTime::createFromFormat("Y-m-d H:i:s", $row[0] + " 00:00:00", new DateTimeZone("UTC"));

			//TODO notif
			return $startDate->getTimestamp() >= $start->getTimestamp() && $endDate->getTimestamp() <= $end->getTimestamp();
		}

		public function setTaskDate($idTask, $startDate, $endDate)
		{
			$startFormat  = $startDate->format("Y-m-d");
			$endFormat    = $endDate->format("Y-m-d");

			$script       = "UPDATE AbstractTask SET startDate = '$startFormat' WHERE id = $idTask;";
			$resultScript = pg_query($this->_conn, $script);

			$script       = "UPDATE Task SET endDate = '$endFormat' WHERE id = $idTask;";
			$resultScript = pg_query($this->_conn, $script);

			$this->updateProject($idTask);
		}

		public function canAccessTask($idTask, $email, $rank)
		{
			if($rank == 2) //Admin
				return true;

			else if($rank == 1)
			{
				$script       = "SELECT COUNT(*) FROM Project, AbstractTask, Task 
						   WHERE Project.id = AbstractTask.idProject AND AbstractTask.id = $idTask AND Task.id = $idTask AND Task.collaboratorEmail = '$email';";
				$resultScript = pg_query($this->_conn, $script);
				$row          = pg_fetch_row($resultScript);
				if($row != null)
					return true;
			}

			else if($rank == 0)
			{
				$script = "SELECT COUNT(*) FROM Task WHERE Task.id = $idTask AND Task.collaboratorEmail = '$email';";
				$resultScript = pg_query($this->_conn, $script);
				$row          = pg_fetch_row($resultScript);
				if($row != null)
					return true;
			}

			return false;
		}

		public function setTaskAdvancement($idTask, $email, $rank, $adv, $chargeConsumed, $remaining)
		{
			if(!$this->canAccessTask($idTask, $email, $rank))
				return false;

			$script = "UPDATE Task SET advancement = $adv, chargeConsumed = $chargeConsumed, remaining = $remaining WHERE id = $idTask;";
			$resultScript = pg_query($this->_conn, $script);

			$this->updateProject($idTask);

			//Send Notif

			return true;
		}

		//Check if we can make the $idTaskPred and $idTaskSucc in an order relationship
		public function checkSuccessor($idTaskPred, $idTaskSucc)
		{
			//Check date and project id
			$script = "(SELECT COUNT(*) FROM AbstractTask AS T1, AbstractTask AS T2, Marker
						WHERE T1.id = $idTaskPred AND T2.id = $idTaskSucc AND T1.idProject = T2.idProject AND
						T1.startDate <= T2.startDate AND T1.id = Marker.id);";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			if($row[0] == 0)
			{
				$script = "(SELECT COUNT(*) FROM AbstractTask AS T1, AbstractTask AS T2, Task
							WHERE T1.id = $idTaskPred AND T2.id = $idTaskSucc AND T1.idProject = T2.idProject AND
							Task.endDate <= T2.startDate AND T1.id = Task.id);";
				$resultScript = pg_query($this->_conn, $script);
				$row          = pg_fetch_row($resultScript);
				if($row[0] == 0)
				{
					return false;
				}
			}

			//Check if $idTaskPred is mother of $idTaskSucc or the opposite
			if($this->isMotherOf($idTaskPred, $idTaskSucc) || $this->isMotherOf($idTaskSucc, $idTaskPred))
			   return false;	

			return true;
		}

		//Tell of $idMother if mother of $idChild (even through hierarchy)
		public function isMotherOf($idMother, $idChild)
		{
			$script       = "SELECT COUNT(*) FROM TaskHierarchy WHERE idMother = $idMother AND idChild = $idChild;";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			if($row[0] == 1)
				return true;

			$script = "SELECT idChild FROM TaskHierarchy WHERE idMother = $idMother;";
			$resultScript = pg_query($this->_conn, $script);
			while($row = pg_fetch_row($resultScript))
				if($this->isMotherOf($row[0], $idChild))
					return true;

			return false;
		}

		public function constructorTaskTree($idProject)
		{
			$tasks       = array();
			$resultTasks = $this->getTasks($idProject);

			//Add some properties needed
			for($i = 0; $i < count($resultTasks->tasks); $i++)
			{
				$resultTasks->tasks[$i]->{"children"}     = array();
				$resultTasks->tasks[$i]->{"predecessors"} = array();
				$resultTasks->tasks[$i]->{"successors"}   = array();
				$resultTasks->tasks[$i]->{"mother"}       = null;
			}

			//Fetch all mothers
			for($i = 0; $i < count($resultTasks->tasks); $i++)
			{
				$isMother = true;
				for($j = 0; $j < count($resultTasks->children); $j++)
				{
					if($resultTasks->children[$j]->idChild == $resultTasks->tasks[$i]->id)
					{
						$isMother = false;
						break;
					}
				}

				if($isMother)
					array_push($tasks, $resultTasks->tasks[$i]);
			}

			//Construct children-tree
			for($i = 0; $i < count($resultTasks->children); $i++)
				for($j = 0; $j < count($resultTasks->tasks); $j++)
					if($resultTasks->children[$i]->idMother == $resultTasks->tasks[$j]->id)
						for($k = 0; $k < count($resultTasks->tasks); $k++)
							if($resultTasks->tasks[$k]->id == $resultTasks->children[$i]->idChild)
							{
								array_push($resultTasks->tasks[$j]->children, $resultTasks->tasks[$k]);
								$resultTasks->tasks[$k]->mother = $resultTasks->tasks[$j]; 
							}

			//Fill the successors
			for($i = 0; $i < count($resultTasks->successors); $i++)
				for($j = 0; $j < count($resultTasks->tasks); $j++)
					if($resultTasks->successors[$i][0] == $resultTasks->tasks[$j]->id)
						for($k = 0; $k < count($resultTasks->tasks); $k++)
							if($resultTasks->tasks[$k]->id == $resultTasks->successors[$i][1])
							{
								array_push($resultTasks->tasks[$j]->successors  , $resultTasks->tasks[$k]);
								array_push($resultTasks->tasks[$k]->predecessors, $resultTasks->tasks[$j]);
							}

			return $tasks;
		}

		//Tell if the child can be child of the mother given in parameters
		public function checkChild($idMother, $idChild)
		{
			//Fetch the project id
			$scriptMother       = "SELECT idProject FROM AbstractTask WHERE id = $idMother";
			$resultScriptMother = pg_query($this->_conn, $scriptMother);
			$rowMother          = pg_fetch_row($resultScriptMother);

			$scriptChild       = "SELECT idProject FROM AbstractTask WHERE id = $idChild";
			$resultScriptChild = pg_query($this->_conn, $scriptChild);
			$rowChild          = pg_fetch_row($resultScriptChild);

			//Check if the two tasks exist and are part of the same project
			if($rowMother == null || $rowChild == null || $rowMother[0] != $rowChild[0])
			{
				error_log("issue project id");
				return false;
			}

			//Fetch all the task of the project in a tree form
			$tasks       = $this->constructorTaskTree($rowMother[0]);

			//Find the mother and the child here
			$mother = null;
			$child  = null;
			for($i = 0; $i < count($tasks); $i++)
			{
				$v = $this->findTask($tasks[$i], $idMother);
				if($v != null)
				{
					$mother = $v;
					break;
				}
			}

			for($i = 0; $i < count($tasks); $i++)
			{
				$v = $this->findTask($tasks[$i], $idChild);
				if($v != null)
				{
					$child = $v;
					break;
				}
			}

			//Check if they are compatible
			if($mother == $child)
				return false;

			if(!$this->checkLevel($mother))
				return false;

			if($this->hierarchyRelationship($child, $mother))
			{
				if($mother->mother != $child->mother)
				{
					error_log("hierarchy relationship");
					return false;
				}
			}
			else if($child->mother != null)
			{
				error_log("issue mother != null. id : $idMother");
				return false;
			}

			$motherMother = $mother;
			while($motherMother != null)
			{
				if($this->orderRelationship($child, $motherMother))
					return false;
				$motherMother = $motherMother->mother;
			}

			if($this->datePredecessor($child, $mother))
				return false;

			return true;
		}

		private function findTask($mother, $id)
		{
			if($mother->id == $id)
				return $mother;

			for($i = 0; $i < count($mother->children); $i++)
			{
				$v = $this->findTask($mother->children[$i], $id);
				if($v != null)
					return $v;
			}
			return null;
		}

		private function orderSuccessors($currentTask, $comparison)
		{
			if($currentTask == $comparison)
				return true;

			for($i = 0; $i < count($currentTask->successors); $i++)
				if($this->orderSuccessors($currentTask->successors[$i], $comparison))
					return true;

			$mother = $comparison;
			while($mother != null)
			{
				for($j = 0; $j < count($currentTask->children); $j++)
					if($this->orderSuccessors($currentTask->children[$j], $mother) && $mother != $currentTask->children[$j])
						return true;
				$mother = $mother->mother;
			}
			return false;
		}

		private function checkLevel($origin)
		{
			$i = 0;
			$mother = $origin;
			while($mother != null)
			{
				$i++;
				$mother = $mother->mother;
			}
			return $i <= 2;
		}

		private function orderPredecessors($currentTask, $comparison)
		{
			if($currentTask == $comparison)
				return true;

			for($i = 0; $i < count($currentTask->predecessors); $i++)
				if($this->orderSuccessors($currentTask->predecessors[$i], $comparison))
					return true;

			$mother = $comparison;
			while($mother != null)
			{
				for($j = 0; $j < count($currentTask->children); $j++)
					if($this->orderPredecessors($currentTask->children[$j], $mother) && $mother != $currentTask->children[$j])
						return true;
				$mother = $mother->mother;
			}
			return false;
		}

		private function orderRelationship($currentTask, $comparison)
		{
			if($this->orderSuccessors($currentTask, $comparison) || $this->orderPredecessors($currentTask, $comparison))
				return true;
			return false;
		}

		private function hierarchyMother($currentTask, $origin)
		{
			if($currentTask == $origin)
				return true;

			$oldMother = $currentTask;
			$mother    = $currentTask->mother;
			while($mother != null)
			{
				for($i = 0; $i < count($mother->children); $i++)
					if($mother->children[$i] != $oldMother && $this->hierarchyChildren($mother->children[$i], $origin))
						return true;

				if($this->hierarchyMother($mother, $origin))
					return true;
				$oldMother = $mother;
				$mother    = $mother->mother;
			}

			return false;
		}

		private function hierarchyChildren($currentTask, $origin)
		{
			if($currentTask == $origin)
				return true;

			for($i = 0; $i < count($currentTask->children); $i++)
				if($this->hierarchyChildren($currentTask->children[$i], $origin))
					return true;
			return false;
		}

		private function hierarchyRelationship($currentTask, $origin)
		{
			if($this->hierarchyMother($currentTask, $origin) || $this->hierarchyChildren($currentTask, $origin))
				return true;
			return false;
		}

		private function datePredecessor($currentTask, $origin)
		{
			$mother = $currentTask;
			while($mother != null)
			{
				for($i = 0; $i < count($origin->predecessors); $i++)
				{
					$start = DateTime::createFromFormat("Y-m-d H:i:s", $currentTask->startDate            . " 00:00:00", new DateTimeZone("UTC"));
					$end   = DateTime::createFromFormat("Y-m-d H:i:s", $origin->predecessors[$i]->endDate . " 00:00:00", new DateTimeZone("UTC"));

					if($end->getTimestamp() < $start->getTimestamp() || $this->datePredecessor($mother, $origin->predecessors[$i]))
					{
						error_log("end : " . $end->getTimestamp() . " start : " . $start->getTimestamp());
					   return true;	
					}
				}

				$mother = $mother->mother;
			}
			return false;
		}

		public function addChild($idMother, $idChild, $isAdmin)
		{
			//Delete old child hierarchy and add a new one
			$script = "BEGIN;
					   DELETE FROM TaskHierarchy WHERE idChild = $idChild;
    				   INSERT INTO TaskHierarchy VALUES ($idMother, $idChild, true);
				       COMMIT;";
			$resultScript = pg_query($this->_conn, $script);

			$this->updateProject($idMother);

			//TODO Maybe send notification
		}

		public function addSuccessor($idPred, $idSucc, $isAdmin)
		{
			$script = "INSERT INTO TaskOrder VALUES ($idPred, $idSucc);";
			$resultScript = pg_query($this->_conn, $script);

			$this->updateProject($idPred);

			//TODO Maybe send notification
		}

		public function getTasksOfUser($email, $started)
		{
			$tasks	= array();
			$script	= "SELECT 
							abstracttask.id, 
							project.id
						FROM abstracttask 
							JOIN task ON abstracttask.id = task.id
							JOIN project ON project.id = abstracttask.idproject
						WHERE task.collaboratoremail = '$email'";
			if($started)
			{
				$script = $script . " AND task.status != 'NOT_STARTED'";
			}
			$script = $script . "ORDER BY project.id, task.enddate";
			$resultScript = pg_query($this->_conn, $script);
			$prReq = new ProjectRqst();
			while($row = pg_fetch_row($resultScript))
			{
				$indeTask			= new IndeTask();
				$indeTask->task		= $this->getTask((int)$row[0]);
				$indeTask->project	= $prReq->getInfoProject((int)$row[1]);

				array_push($tasks, $indeTask);
			}
			return $tasks;
		}

		public function containDuplicate($arr, $index = 1)
		{
			for($i = $index; $i < count($arr); $i++)
				for($j = $i+1; $j <  count($arr); $j++)
					if($arr[$i] == $arr[$j])
						return true;
			return false;
		}

		public function canAddMarker($idProject, $startDate, $predecessors)
		{
			$startTime    = new DateTime();
			$startTime->setTimestamp($startDate);
			$startFormat  = $startTime->format("Y-m-d");

			//Test the date
			$script       = "SELECT COUNT(*) FROM Project WHERE startDate <= '$startFormat' AND endDate >= '$startFormat';";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);

			if($row[0] == 0)
				return false;

			if($this->containDuplicate($predecessors))
				return false;

			for($i = 0; $i < count($predecessors); $i++)
			{
				$idPred = $predecessors[$i];
				$script = "SELECT COUNT(*) FROM (
								SELECT T1.* FROM AbstractTask AS T1, Marker WHERE T1.id = $idPred AND T1.startDate <= '$startFormat' AND T1.ID = Marker.ID UNION ALL
								SELECT T1.* FROM AbstractTask AS T1, Task WHERE T1.id = $idPred AND T1.id = Task.id AND T1.endDate <= '$startFormat')";
				$resultScript = pg_query($this->_conn, $script);
				$row          = pg_fetch_row($resultScript);

				if($row[0] == 0)
					return false;
			}
			return true;
		}

		public function addMarker($idProject, $name, $startDate, $description, $predecessors, $isAdmin)
		{
			$startTime   = new DateTime();
			$startTime->setTimestamp($startDate);
			$startFormat = $startTime->format("Y-m-d");

			$script = "INSERT INTO AbstractTask (idProject, name, description, startDate) VALUES ($idProject, '$name', '$description', '$startFormat')
					   RETURNING id;";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			$id           = $row[0];

            $script = "INSERT INTO Marker VALUES ($id);";
            foreach($predecessors as $pred)
                $script = $script . "INSERT INTO TaskOrder VALUES($pred, $id);";
			$resultScript = pg_query($this->_conn, $script);

			$timerRqst = new TimerRqst();
			$timerRqst->updateProject($idProject);

			//TODO Maybe send a notification
		}

		public function canAddTask($idProject, $collEmail, $initCharge, $mother, $startDate, $endDate, $predecessors, $children)
		{
			return true;
		}

		public function addTask($idProject, $name, $collEmail, $initCharge, $mother, $startDate, $endDate, $description, $predecessors, $children, $isAdmin)
		{
			$name        = str_replace("'", "''", $name);
			$description = str_replace("'", "''", $description);
			$startTime   = new DateTime();
			$startTime->setTimestamp($startDate);
			$startFormat = $startTime->format("Y-m-d");

			$endTime     = new DateTime();
			$endTime->setTimestamp($endDate);
			$endFormat   = $endTime->format("Y-m-d");

			$script = "INSERT INTO AbstractTask (idProject, name, description, startDate) VALUES ($idProject, '$name', '$description', '$startFormat')
					   RETURNING id;";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			$id           = $row[0];

			$collEmail    = ($collEmail == 'NULL') ? 'NULL' : "'$collEmail'";

            $script = "INSERT INTO Task VALUES ($id, '$endFormat', $initCharge, $initCharge, $initCharge, 0, 0, $collEmail, 'NOT_STARTED');";
            foreach($predecessors as $pred)
                $script = $script . "INSERT INTO TaskOrder VALUES($pred, $id);";

            foreach($children as $child)
                $script = $script . "INSERT INTO TaskHierarchy VALUES($id, $child, TRUE);";

			if($mother != "NULL")
				$script = $script . "INSERT INTO TaskHierarchy VALUES($mother, $id, TRUE);";

			error_log($script);
			$resultScript = pg_query($this->_conn, $script);

			$timerRqst = new TimerRqst();
			$timerRqst->updateProject($idProject);

			//TODO Maybe send a notification
		}

		public function updateProject($idTask)
		{
			$script       = "SELECT idProject FROM AbstractTask WHERE id = $idTask;";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);

			$timerRqst = new TimerRqst();
			$timerRqst->updateProject($row[0]);

		}

		public function deleteTask($idTask)
		{
			//DELETE PREDECESSORS, CHILDREN and Task
			$script = "DELETE FROM TaskOrder     WHERE predecessorID = $idTask OR successorID = $idTask;
					   DELETE FROM TaskHierarchy WHERE idMother      = $idTask OR idChild     = $idTask;
					   DELETE FROM Marker        WHERE id            = $idTask;
					   DELETE FROM Task          WHERE id            = $idTask;
					   DELETE FROM AbstractTask  WHERE id            = $idTask;";
			$resultScript = pg_query($this->_conn, $script);
			//TODO maybe send notification
		}

		public function setTaskInitCharge($idTask, $initCharge)
		{
			$script = "UPDATE Task SET initCharge=$initCharge WHERE id=$idTask;";
			$resultScript = pg_query($this->_conn, $script);
		}

		public function setTaskCharge($idTask, $chargeConsumed, $remaining)
		{
			$computedCharge = $remaining + $chargeConsumed;
			$advancement    = (int)((1.0*$chargeConsumed)/($computedCharge)*100);

			$script         = "UPDATE Task SET advancement=$advancement, remaining=$remaining, chargeConsumed=$chargeConsumed, computedCharge=$computedCharge WHERE id=$idTask;";
			$resultScript = pg_query($this->_conn, $script);
		}

		public function updateMarker($idTask, $name, $startDate, $description, $predecessors)
		{
			$name        = str_replace("'", "''", $name);
			$description = str_replace("'", "''", $description);
			$startTime   = new DateTime();
			$startTime->setTimestamp($startDate);
			$startFormat = $startTime->format("Y-m-d");

			//Delete predecessors for this task
			$script = "BEGIN; DELETE FROM TaskOrder WHERE successorID = $idTask;";

			//Insert new predecessors
			for($i=0; $i < count($predecessors); $i++)
			{
				$pred = $predecessors[$i];
				$script = $script. "INSERT INTO TaskOrder(predecessorID, successorID) VALUES ($pred, $idTask);";
			}

			//Update the other value
			$script = $script . "UPDATE AbstractTask SET name='$name', description='$description', startDate='$startFormat' WHERE id=$idTask; COMMIT;";
			$resultScript = pg_query($this->_conn, $script);
		}

		public function updateTask($idTask, $name, $startDate, $endDate, $collEmail, $description, $idMother, $predecessors, $children)
		{
			$name        = str_replace("'", "''", $name);
			$description = str_replace("'", "''", $description);
			$startTime   = new DateTime();
			$startTime->setTimestamp($startDate);
			$startFormat = $startTime->format("Y-m-d");

			$endTime   = new DateTime();
			$endTime->setTimestamp($endDate);
			$endFormat = $endTime->format("Y-m-d");

			//Delete predecessors for this task
			$script = "BEGIN; DELETE FROM TaskOrder WHERE successorID = $idTask;";

			//Delete task hierarchy
			$script = $script . "DELETE FROM TaskHierarchy WHERE counted = TRUE AND (idMother = $idTask OR idChild = $idTask);";

			//Insert new predecessors
			for($i=0; $i < count($predecessors); $i++)
			{
				$pred = $predecessors[$i];
				$script = $script. "INSERT INTO TaskOrder(predecessorID, successorID) VALUES ($pred, $idTask);";
			}

			//Insert mother
			if($idMother != -1)
				$script = $script . "INSERT INTO TaskHierarchy(idMother, idChild, counted) VALUES ($idMother, $idTask, true);";

			//Insert new children
			for($i=0; $i < count($children); $i++)
			{
				$child = $children[$i];
				$script = $script . "INSERT INTO TaskHierarchy(idMother, idChild, counted) VALUES ($idTask, $child, true);";
			}

			//Update the other value
			if($collEmail != "NULL")
				$collEmail = "'".$collEmail."'";
			$script = $script . "UPDATE AbstractTask SET name='$name', description='$description', startDate='$startFormat' WHERE id=$idTask; 
								 UPDATE Task SET endDate='$endFormat' WHERE id=$idTask;
							     COMMIT;";
			error_log($script);
			$resultScript = pg_query($this->_conn, $script);
		}
	}
?>
