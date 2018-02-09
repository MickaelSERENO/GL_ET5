<?php
	require_once __DIR__.'/../PSQL/TimerRqst.php';

	$timerRqst = new TimerRqst();

	//Create TimerRqst
	$w = new EvTimer(3600, 1, function ($w, $revents) 
	{
		$timerRqst->updateProjects();
	});
	Ev::run();
?>
