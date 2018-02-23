<?php
	require_once __DIR__.'/PSQLDatabase.php';
	
	class Notif
	{
		public $id;
		public $theDate;
		public $title;
       	public $message;
		public $read;
		public $send;
		public $projectName;
		
	}

	class NotifRqst extends PSQLDatabase
	{
		public function getNotifs($emailReceiver, $unread)
		{
			$notifs = array();
			//Fetch notifs
			$script = "SELECT  
					notification.id, 
					notification.thedate, 
					notification.title, 
					notification.message, 
					notification.read, 
					Sender.emailsender,
					project.name
				FROM notification
					JOIN Sender ON Notification.id = Sender.idnotification
					JOIN projectnotification ON Notification.id = projectnotification.notificationID
					JOIN project ON projectnotification.projectID = project.id
				WHERE emailReceiver = '$emailReceiver'";
			if($unread)
			{
				$script = $script." AND NOT read";
			}
			$script = $script." ORDER BY theDate;";				
					   
			 
			$resultScript = pg_query($this->_conn, $script);
			while($row = pg_fetch_row($resultScript))
			{
				$notif			= new Notif();
				$notif->id      = (int)($row[0]);
				$notif->theDate	= $row[1];
				$notif->title	= $row[2];
				$notif->message	= $row[3];
				if($row[4]=='f')
				{
					$notif->read	= false;
				}
				else
				{
					$notif->read	= true;
				}
				$notif->send	= $row[5];
				$notif->projectName = $row[6];

				array_push($notifs, $notif);
			}
            return $notifs;
		}
	}
?>
