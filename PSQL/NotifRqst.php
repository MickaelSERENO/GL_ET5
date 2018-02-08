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
		
	}

	class NotifRqst extends PSQLDatabase
	{
		public function getNotifs($emailReceiver, $unread)
		{
			$notifs = array();
			//Fetch notifs
			$script = "SELECT notification.id, thedate, title, message, read, emailsender
					   FROM notification, Sender
					   WHERE 
						Notification.id = Sender.idnotification AND emailReceiver = '$emailReceiver'";
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
				$notif->send	 = $row[5];
				

				array_push($notifs, $notif);
			}

          	  return $notifs;
		}
	}
?>
