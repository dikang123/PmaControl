<?php

use \Glial\Synapse\Controller;
//use \Glial\Cli\Color;
use \Glial\Cli\Table;
use \Glial\Sgbd\Sgbd;
use \Glial\Net\Ssh;
use \Glial\Security\Crypt\Crypt;

class Common extends Controller
{

    //dba_source

	public function index()
	{
		$db = $this->di['db']->sql(DB_DEFAULT);

		$sql = "SELECT * FROM mysql_database";

	}


	/*
	@author: Aurélien LEQUOY
	Obtenir la liste dans un select des server MySQL operationels
	*/


	public function getSelectServerAvailable()
	{
		$db = $this->di['db']->sql(DB_DEFAULT);

		$sql = "SELECT * FROM mysql_server where error='' ORDER by name";

		$res = $db->sql_query($sql);

		$data['list_servers'] = array();
		while ($ob = $db->sql_fetch_object($res))
		{
			$tmp = [];
			$tmp['id'] = $ob->id;
			$tmp['libelle'] = $ob->name." (".$ob->ip.")";

			$data['list_server'][] = $tmp;
		
		}


		$this->set('data', $data);

	}

}
