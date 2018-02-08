<?php
	require_once __DIR__.'/PSQLDatabase.php';
	require_once __DIR__.'/ProjectRqst.php';
	
	class AbstractTask
	{
		public $id;
		public $name;
		public $description;
		public $startDate;
		public $endDate;
        public $isMarker;
		public $stats;
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
                    array_push($fullTasks, $makers[$markerID]);
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
					array_push($fullTasks, $makers[$markerID]);
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
                array_push($children, new TaskHierarchy($row[0], $row[1], $row[2]));

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

			$middleDate   = new DateTime();
			$middleDate->setTimestamp((int)($middleTimestamp));
			$middleFormat = $middleDate->format("Y-m-d");
			if($collEmail == $task->collaboratorEmail)
				return;

			$colName      = "";
			$scriptCol    = "SELECT name, surname FROM Contact WHERE email = '$collEmail';";

			$collEmail    = $collEmail == "" ? "null" : "'".$collEmail."'";
			$resultScript = pg_query($this->_conn, $scriptCol);
			$row          = pg_fetch_row($resultScript);

			$script       = "";
			if($row != null)
			{
				$desc1        = $task->description.". Parte 1 réalisée par $colName.";
				$name1        = $task->name . ". Partie 1.";
				$desc2        = $task->description.". Parte 2.";
				$name2        = $task->name . ". Partie 2.";


				$colName = $row[1] . " " . $row[0];

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
				$resultScript = pg_query($this->_conn, $script);
				$rowInsert    = pg_fetch_row($resultScript);
				$script		  =	"INSERT INTO Task(id, endDate, initCharge, computedCharge, remaining, chargeConsumed, advancement, collaboratorEmail, status) 
								 VALUES($rowInsert[0], '$task->endDate', $initCharge, $computedCharge, $computedCharge, 0, 0, $collEmail, 'STARTED');
								 INSERT INTO TaskHierarchy VALUES($idTask, $rowInsert[0], false);
								 ";
				$resultScript = pg_query($this->_conn, $script);
			}
			else
			{
				$script = "UPDATE Task SET collaboratorEmail = $collEmail WHERE Task.id = $idTask;";
				$resultScript = pg_query($this->_conn, $script);
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

			//Send Notif

			return true;
		}
	}
?>
