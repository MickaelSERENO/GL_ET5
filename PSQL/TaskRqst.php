<?php
	require_once __DIR__.'/PSQLDatabase.php';
	
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

	class Project
	{
		public $id;
		public $startDate;
		public $endDate;
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
				$task                    = new Marker();
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
	}
?>
