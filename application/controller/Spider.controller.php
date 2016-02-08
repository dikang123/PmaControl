<?php

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sgbd;
use \Glial\Net\Ssh;

use \Glial\Security\Crypt\Crypt;

class Server extends Controller
{

    //dba_source

	public function index()
	{
		$db = $this->di['db']->sql(DB_DEFAULT);

		$sql = "SELECT * FROM mysql_database";

	}

}
