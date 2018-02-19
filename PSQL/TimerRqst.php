<?php
	require_once __DIR__.'/../PSQL/PSQLDatabase.php';
	require_once __DIR__.'/../PSQL/TaskRqst.php';

	class TimerRqst extends PSQLDatabase
	{
		public function updateProjects()
		{
			//Get all projects
			$script = "SELECT id FROM Project WHERE status != 'CLOSED_VISIBLE' AND status != 'CLOSED_INVISIBLE';";
			$resultScript = pg_query($this->_conn, $script);

			//Update every single project one by one
			while($row = pg_fetch_row($resultScript))
				$this->updateProject($row[0]);
		}

		//Make sure the project is not closed (we do not check that here)
		public function updateProject($id)
		{
			//Fetch the tree tasks with this id
			$taskRqst = new TaskRqst();
			$treeTask = $taskRqst->constructorTaskTree($id);

			//Go through all the task
			foreach($treeTask as $task)
				$this->checkTaskStatus($task);

			//Get project end date
			$script       = "SELECT endDate FROM Project WHERE id = $id";
			$resultScript = pg_query($this->_conn, $script);
			$row          = pg_fetch_row($resultScript);
			$end          = DateTime::createFromFormat("Y-m-d H:i:s", $row[0] . " 00:00:00", new DateTimeZone("UTC"));

			if($this->projectIsLate($treeTask, $end))
				$script = "UPDATE Project SET isLate = TRUE  WHERE id=$id;";
			else
				$script = "UPDATE Project SET isLate = FALSE WHERE id=$id;";
			$resultScript = pg_query($this->_conn, $script);
		}

		public function projectIsLate($treeTask, $end)
		{
			foreach($treeTask as $task)
			{
				//Need to start from task with no predecessors
				if(count($task->predecessors) == 0)
					if($this->getEndSuccessor($task, $end, 0))
						return true;
			}
			return false;
		}

		public function getEndSuccessor($task, $endProject, $actualLate)
		{
			$today = DateTime::createFromFormat('!Y-m-d', date('Y-m-d'));
			$start = DateTime::createFromFormat("Y-m-d H:i:s", $task->startDate . " 00:00:00", new DateTimeZone("UTC"));
			$end   = DateTime::createFromFormat("Y-m-d H:i:s", $task->endDate . " 00:00:00", new DateTimeZone("UTC"));

			//Check if from today we have issue
			if(!$task->isMarker)
			{
				if($today >= $start)
				{
					if(($end->getTimestamp() - $today->getTimestamp())/(24*3600) < $task->remaining)
						$actualLate += $task->remaining*24*3600 - ($end->getTimestamp() - $today->getTimestamp());
				}

				else
				{
					if(($end->getTimestamp() - $start->getTimestamp())/(24*3600) < $task->remaining)
						$actualLate += $task->remaining*24*3600 - ($end->getTimestamp() - $start->getTimestamp());
				}
			}

			//If this task is after the project date
			if($end->getTimestamp() + $actualLate > $endProject->getTimestamp())
				return true;

			//Check for every successors
			else
				foreach($task->successors as $succ)
					if($this->getEndSuccessor($succ, $endProject, $actualLate))
						return true;
			return false;
		}

		public function checkTaskStatus($task)
		{
			$newStatus    = "";

			//Get today day
			$today = DateTime::createFromFormat('!Y-m-d', date('Y-m-d'));
			$start = DateTime::createFromFormat("Y-m-d H:i:s", $task->startDate . " 00:00:00", new DateTimeZone("UTC"));
			$end   = DateTime::createFromFormat("Y-m-d H:i:s", $task->endDate . " 00:00:00", new DateTimeZone("UTC"));

			//Check for predecessor
			foreach($task->predecessors as $pred)
			{
				$this->checkTaskStatus($pred);
				if($pred->stats == 'LATE_STARTED' || $pred->stats == 'LATE_UNSTARTED')
				{
					if($today >= $start)
						$newStatus = 'LATE_STARTED';
					else
						$newStatus = 'LATE_UNSTARTED';
				}
			}

			//Check for children
			foreach($task->children as $subTask)
			{
				$this->checkTaskStatus($subTask);
				if($subTask->stats == 'LATE_STARTED' || $subTask->stats == 'LATE_UNSTARTED')
				{
					if($today >= $start)
						$newStatus = 'LATE_STARTED';
					else
						$newStatus = 'LATE_UNSTARTED';
				}
			}

			if($task->isMarker)
				return;

			//If the status has not changed with predecessors or children, check by time
			if($newStatus == "")
			{
				if($today >= $start)
				{
					if(($end->getTimestamp() - $today->getTimestamp())/(24*3600) >= $task->remaining || $task->remaining == 0)
						$newStatus = 'STARTED';
					else
						$newStatus = 'LATE_STARTED';
				}
				else
				{
					if(($end->getTimestamp() - $start->getTimestamp())/(24*3600) >= $task->remaining || $task->remaining == 0)
						$newStatus = 'NOT_STARTED';
					else
						$newStatus = 'LATE_UNSTARTED';
				}
			}

			if($newStatus != $task->stats)
			{
				$taskID = $task->id;
				//Update task
				$script = "UPDATE Task SET status = '$newStatus' WHERE id = $taskID;"; 
				$resultScript = pg_query($this->_conn, $script);
			}
			$task->stats = $newStatus;
		}
	}
?>
