<?php
	require_once __DIR__.'/PSQLDatabase.php';
	
	class Notif
	{
		public $id;
		public $theDate;
		public $title;
       	public $message;
		public $read;
		public $sender;
		public $senderLastName;
		public $senderFirstName;
		public $receiver;
		public $projectName;
		public $projectID;
	}

	class NotifRqst extends PSQLDatabase
	{
		public function getNotifs($emailReceiver, $unread)
		{
			$notifs = array();
			//Fetch notifs
			$script = 
				"SELECT  
					Notification.id
				FROM Notification
					JOIN Sender ON Notification.id = Sender.idNotification
				WHERE Sender.emailReceiver = '$emailReceiver'";
			if($unread)
			{
				$script = $script." AND NOT read";
			}
			$script = $script." ORDER BY theDate DESC;";				
			 
			$resultScript = pg_query($this->_conn, $script);
			while($row = pg_fetch_row($resultScript))
			{
				$notif = $this->getNotifByID((int)($row[0]));

				array_push($notifs, $notif);
			}
           	return $notifs;
		}

		public function getNotifByID($idNotif)
		{
			$script = 
				"SELECT  
					Notification.id, 
					Notification.thedate, 
					Notification.title, 
					Notification.message, 
					Notification.read, 
					Sender.emailsender,
					Contact.surname,
					Contact.name,
					Sender.emailReceiver,
					Project.name,
					Project.id
				FROM Notification
					JOIN Sender ON Notification.id = Sender.idNotification
					JOIN Contact ON Contact.email = Sender.emailSender
					LEFT OUTER JOIN ProjectNotification ON Notification.id = ProjectNotification.notificationID
					LEFT OUTER JOIN Project ON ProjectNotification.projectID = Project.id
				WHERE notification.id = '$idNotif'";
					   
			$resultScript = pg_query($this->_conn, $script);
			$row = pg_fetch_row($resultScript);
			$notif			= new Notif();
			$notif->id      = (int)($row[0]);
			$notif->theDate	= $row[1];
			$notif->title	= $row[2];
			$notif->message	= $row[3];
			if($row[4]=='f')
			{
				$notif->read = false;
			}
			else
			{
				$notif->read = true;
			}
			$notif->sender			= $row[5];
			$notif->senderLastName	= $row[6];
			$notif->senderFirstName	= $row[7];
			$notif->receiver		= $row[8];
			$notif->projectName		= $row[9];
			$notif->projectID		= $row[10];

			return $notif;
		}

		public function readNotif($idNotif)
		{	
			$script = "UPDATE notification 
				SET read = true 
				WHERE id = $idNotif;";
			$resultScript = pg_query($this->_conn, $script);	
		}
	}
?>
