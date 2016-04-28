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

    public function hardware()
    {

        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->title = __("Hardware");
        $this->ariane = " > " .  $this->title;

        $sql = "SELECT c.libelle as client,d.libelle as environment,a.*, b.version, b.is_available 
            FROM mysql_server a 
                 INNER JOIN client c on c.id = a.id_client 
                 INNER JOIN environment d on d.id = a.id_client 
         LEFT JOIN mysql_replication_stats b ON a.id = b.id_mysql_server
         order by `name`;";

        $data['servers'] = $db->sql_fetch_yield($sql);

        $this->set('data', $data);

    }

    public function before($param)
    {
        $this->layout_name = 'pmacontrol';
    }

    public function listing($param)
    {
	
	$db = $this->di['db']->sql(DB_DEFAULT);

	$sql = "SELECT * FROM daemon_main WHERE id=1";
	$res = $db->sql_query($sql);

	$ob = $db->sql_fetch_object($res);

	$data['pid'] = $ob->pid;
	$data['date'] = $ob->date;
	$data['log_file'] = $ob->log_file;
        
        $sql  = "SELECT * from client order by libelle";
        $res = $db->sql_query($sql);
        
        
        $data['client'] = array();
        while ($ob = $db->sql_fetch_object($res))
        {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->libelle;
            
            $data['client'][] = $tmp;
        }
        
        $sql  = "SELECT * from environment order by libelle";
        $res = $db->sql_query($sql);
        
        
        $data['environment'] = array();
        while ($ob = $db->sql_fetch_object($res))
        {
            $tmp = [];
            $tmp['id'] = $ob->id;
            $tmp['libelle'] = $ob->libelle;
            
            $data['environment'][] = $tmp;
        }
        

	$data['menu']['main']['name'] = __('Servers');
	$data['menu']['main']['icone'] = '<span class="glyphicon glyphicon-th-large" style="font-size:12px"></span>';
	$data['menu']['main']['path'] = LINK.__CLASS__.'/'.__FUNCTION__.'/main';

        $data['menu']['hardware']['name'] = __('Hardware');
	$data['menu']['hardware']['icone'] = '<span class="glyphicon glyphicon-hdd" style="font-size:12px"></span>';
	$data['menu']['hardware']['path'] = LINK.__CLASS__.'/'.__FUNCTION__.'/hardware';
        
        
	$data['menu']['database']['name'] = __('Databases');
	$data['menu']['database']['icone'] = '<i class="fa fa-database fa-lg" style="font-size:14px"></i>';
	$data['menu']['database']['path'] =  LINK.__CLASS__.'/'.__FUNCTION__.'/database';

	$data['menu']['statistics']['name'] = __('Statistics');
	$data['menu']['statistics']['icone'] = '<span class="glyphicon glyphicon-signal" style="font-size:12px"></span>';
	$data['menu']['statistics']['path'] =  LINK.__CLASS__.'/'.__FUNCTION__.'/statistics';

	$data['menu']['memory']['name'] = __('Memory');
	$data['menu']['memory']['icone'] = '<span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span>';
	$data['menu']['memory']['path'] =  LINK.__CLASS__.'/'.__FUNCTION__.'/memory';

	$data['menu']['index']['name'] = __('Index');
	$data['menu']['index']['icone'] = '<span class="glyphicon glyphicon-th-list" style="font-size:12px"></span>';
	$data['menu']['index']['path'] =  LINK.__CLASS__.'/'.__FUNCTION__.'/index';
	
	$data['menu']['logs']['name'] = __('Logs');
	$data['menu']['logs']['icone'] = '<span class="glyphicon glyphicon-list-alt" style="font-size:12px"></span>';
	$data['menu']['logs']['path'] =  LINK.__CLASS__.'/'.__FUNCTION__.'/index';


	if (!empty($param[0]))
	{
		if (in_array($param[0], array("main","database","statistics","logs","memory","index", "hardware")))
		{
			$_GET['path'] = LINK.__CLASS__.'/'.__FUNCTION__.'/'.$param[0];
		}
	}	

	if (empty($_GET['path']) && empty($param[0]))
	{
		$_GET['path'] = $data['menu']['main']['path'];
		$param[0] = 'main';
	}

	if (empty($_GET['path']))
	{
		$_GET['path'] = 'main';
	}


	$this->title = __("Dashboard");
	$this->ariane = ' > <a href⁼"">'.$this->title.'</a> > '.$data['menu'][$param[0]]['icone'].' '.$data['menu'][$param[0]]['name'];


        $this->set('data', $data);
    }


	public function main()
	{
		$db = $this->di['db']->sql(DB_DEFAULT);

        	$this->title = __("Dashboard");
	        $this->ariane = " > " .  $this->title;

        	$sql = "SELECT a.*, b.version, b.is_available,c.libelle as client,d.libelle as environment FROM mysql_server a 
                     INNER JOIN client c on c.id = a.id_client 
                 INNER JOIN environment d on d.id = a.id_environment
                 LEFT JOIN mysql_replication_stats b ON a.id = b.id_mysql_server
                 order by `name`;";

	        $data['servers'] = $db->sql_fetch_yield($sql);

        	$this->set('data', $data);
	}


	public function database()
	{
		
		$db = $this->di['db']->sql(DB_DEFAULT);
		
		$sql = "SELECT a.id,a.name,a.ip,a.port,a.error,
			GROUP_CONCAT('',b.name) as dbs,
			GROUP_CONCAT('',b.id) as id_db,
			GROUP_CONCAT('',b.data_length) as data_length,
			GROUP_CONCAT('',b.data_free) as data_free,
			GROUP_CONCAT('',b.index_length) as index_length,
			GROUP_CONCAT('',b.collation_name) as collation_name,
			GROUP_CONCAT('',b.character_set_name) as character_set_name,
			GROUP_CONCAT('',b.binlog_do_db) as binlog_do_db,
			GROUP_CONCAT('',b.tables) as tables,
			GROUP_CONCAT('',b.rows) as rows
			FROM mysql_server a
			INNER JOIN mysql_database b ON b.id_mysql_server = a.id
			GROUP BY a.id";	

		$data['servers'] = $db->sql_fetch_yield($sql);
		$this->set('data',$data);
	}


	public function statistics()
	{

		$db = $this->di['db']->sql(DB_DEFAULT);


                /*
		$db->sql_query("DROP TABLE IF EXISTS `temp`");
		$sql ="	
create table temp
as select a.id_mysql_server as id_mysql_server,max(a.date) as date from mysql_status_value_int a where a.id_mysql_status_name = 259 group by a.id_mysql_server;";
		$db->sql_query($sql);
		sleep("1");
                */

		$sql ='
select b.value as "select", c.value as "update", f.value as "insert", g.value as "delete", j.ip, j.port, j.id, j.name, k.value as "connected"
FROM mysql_status_max_date a
INNER JOIN mysql_status_value_int b ON b.id_mysql_server = a.id_mysql_server and a.date = b.date
INNER JOIN mysql_status_value_int c ON c.id_mysql_server = a.id_mysql_server and a.date = c.date
INNER JOIN mysql_status_name d ON d.id = b.id_mysql_status_name
INNER JOIN mysql_status_name e ON e.id = c.id_mysql_status_name
INNER JOIN mysql_status_value_int f ON f.id_mysql_server = a.id_mysql_server and a.date = f.date
INNER JOIN mysql_status_value_int g ON g.id_mysql_server = a.id_mysql_server and a.date = g.date
INNER JOIN mysql_status_name h ON h.id = f.id_mysql_status_name
INNER JOIN mysql_status_name i ON i.id = g.id_mysql_status_name
INNER JOIN mysql_server j ON j.id = a.id_mysql_server
INNER JOIN mysql_status_value_int k ON k.id_mysql_server = a.id_mysql_server and a.date = k.date
INNER JOIN mysql_status_name l ON l.id = k.id_mysql_status_name

WHERE d.name = "Com_select" and e.name = "Com_update" 
and h.name = "Com_insert" 
and i.name = "Com_delete"
and l.name = "Threads_connected"
;';

		$res = $db->sql_query($sql);

		$data['servers'] = $db->sql_fetch_yield($sql);

		//$db->sql_query("DROP TABLE IF EXISTS `temp`");
		
		$this->set('data',$data);
	}



	public function logs()
	{
		//used with recursive
	}


	public function memory()
	{
	$this->layout_name = 'pmacontrol';
        $this->title = __("Memory");
        $this->ariane = " > " . __("Tools Box") . " > " . $this->title;

        $default = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server a
            INNER JOIN `mysql_replication_stats` b ON a.id = b.id_mysql_server 
            WHERE is_available = 1 
            order by a.`name`";
        $res50 = $default->sql_query($sql);

        while ($ob50 = $default->sql_fetch_object($res50)) {
            $db = $this->di['db']->sql($ob50->name);
            $data['variables'][$ob50->name] = $db->getVariables();
            $data['status'][$ob50->name] = $db->getStatus();
            $data['memory'][$ob50->name] = $ob50->memory_kb;
        }
        $this->set('data', $data);

	}

	
	public function index()
	{
	$this->layout_name = 'pmacontrol';

        $this->title = __("Index usage");
        $this->ariane = " > " . __("Tools Box") . " > " . $this->title;

        $default = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT * FROM mysql_server a
            INNER JOIN `mysql_replication_stats` b ON a.id = b.id_mysql_server
            WHERE is_available = 1
            order by `name`";
        $res50 = $default->sql_query($sql);

        $data = [];
        while ($ob50 = $default->sql_fetch_object($res50)) {

            $db = $this->di['db']->sql($ob50->name);
            $data['status'][$ob50->name] = $db->getStatus();

        }


        $this->set('data', $data);
	}
}
