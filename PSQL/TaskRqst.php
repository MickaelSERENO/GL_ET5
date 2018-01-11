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

        public __construct($idM, $idC, $isC)
        {
            $idMother  = $idM;
            $idChild   = $idC;
            $isCounted = $isC;
        }
    }

    class FetchTask
    {
        public $idProject;
        public $tasks;
        public $successors;
        public $children;
    }

	class TaskRqst extends PSQLDatabase
	{
		public function getTasks($projectID)
		{
			$markers    = array();
			$tasks      = array();
            $fullTasks  = array();
            $children   = array();
            $successors = array();

			//Fetch markers
			$script = "SELECT id, name, description, startDate
					   FROM AbstractTask INNER JOIN Marker ON AbstractTask.id = Marker.id
					   WHERE projectID = $projectID
					   ORDER BY startDate;";

			$resultScript = pg_query($this->_conn, $script);
			for($row = pg_fetch_row($resultScript))
			{
				$marker              = new Marker();
				$marker->id          = $row[0];
				$marker->name        = $row[1];
				$marker->description = $row[2];
				$marker->startDate   = $row[3];
				$marker->endDate     = $row[3];
                $marker->isMarker    = true;

				array_push($markers, $marker);
			}

			//Fetch tasks
			$script = "SELECT id, name, description, startDate, 
					   endDate, initCharge, computedCharge, remaining, chargeConsumed, advancement, collaboratorEmail
					   FROM AbstractTask INNER JOIN Task ON AbstractTask.id = Task.id
					   WHERE projectID = $projectID
					   ORDER BY startDate;";

			$resultScript = pg_query($this->_conn, $script);
			for($row = pg_fetch_row($resultScript))
			{
				$task                    = new Marker();
				$task->id                = $row[0];
				$task->name              = $row[1];
				$task->description       = $row[2];
				$task->startDate         = $row[3];
				$task->endDate           = $row[4];
				$task->initCharge        = $row[5];
				$task->computedCharge    = $row[6];
				$task->remaining         = $row[7];
				$task->chargeConsumed    = $row[8];
				$task->advancement       = $row[9];
				$task->collaboratorEmail = $row[10];
                $task->isMarker          = false;

				array_push($tasks, $task);
			}

            //Merge markers and tasks
            $markerID = 0;
            $taskID   = 0;

            while($markerID < count($markers) && $taskID < count($tasks))
            {
                if($markerID >= count($markers))
                {
                    array_push($fullTasks, $tasks[$taskID]);
                    $taskID = $taskID + 1;
                }

                else if($taskID >= count($tasks))
                {
                    array_push($fullTasks, $makers[$markerID]);
                    $markerID = $markerID + 1;
                }

                else if($tasks[$taskID]->startDate < $markers[$markerID]->startDate)
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
                             WHERE idProject = $projectID;";

            $resultScript = pg_query($this->_conn, $script);
			for($row = pg_fetch_row($resultScript))
                array_push($successors, [$row[0], $row[1]]);
			
			//Fetch children
            $script = "SELECT id, idChild, counted FROM AbstractTask INNER JOIN TaskHierarchy ON AbstractTask.id = TaskHierarchy.idMother
                       WHERE idProject = $projectID;";

			$resultScript = pg_query($this->_conn, $script);
			for($row = pg_fetch_row($resultScript))
                array_push($children, new TaskHierarchy($row[0], $row[1], $row[2]));

            //Compile everything into an object and return it
            $result = new FetchTask();
            $result->idProject  = $idProject;
            $result->tasks      = $fullTasks;
            $result->children   = $children;
            $result->successors = $successors;

            return $result;
		}
	}
?>
