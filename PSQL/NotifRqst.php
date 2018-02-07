<?php
	require_once __DIR__.'/PSQLDatabase.php';
	
	class Notif
	{
		public $id;
		public $theDate;
		public $title;
        	public $message;
		public $read;
	}

	class NotifRqst extends PSQLDatabase
	{
		public function getNotifs($emailReceiver, $unread)
		{
			$notifs = array();
			//Fetch notifs
			$script = "SELECT notification.id, thedate, title, message, read
					   FROM notification INNER JOIN Sender ON Notification.id = Sender.idnotification
					   WHERE emailReceiver = '$emailReceiver'";
			if($unread)
			{
				$script = $script." AND NOT read";
			}
			$script = $script." ORDER BY theDate;";				
					   
			 
			$resultScript = pg_query($this->_conn, $script);
			while($row = pg_fetch_row($resultScript))
			{
				$notif		 = new Notif();
				$notif->id       = (int)($row[0]);
				$notif->theDate	 = $row[1];
				$notif->title	 = $row[2];
				$notif->message	 = $row[3];
				$notif->read	 = (boolean)($row[4]);

				array_push($notifs, $notif);
			}

          	  return $notifs;
		}
	}
?>
