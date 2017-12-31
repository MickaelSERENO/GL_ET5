<?php

	class PSQLDatabase
	{
		protected $_conn=null;
		public function __construct()
		{
			$this->_conn = pg_pconnect("host=127.0.0.1 user=postgres dbname=projetgl");
		}
	}
?>
