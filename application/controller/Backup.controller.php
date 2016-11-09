<?php

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sql\Mysql\MasterSlave;
use \Glial\Cli\Color;
use \Glial\Sgbd\Sgbd;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Cli\Ssh;
use \Glial\Cli\Crontab;
use \phpseclib\Net\SSH2;

class Backup extends Controller
{
    const BACKUP_DIR = "/data/backup";

    var $backup_dir = self::BACKUP_DIR;
    var $time_backup_start;
    var $time_backup_end;
    var $time_gzip;
    var $time_transfert;
    var $md5_file;
    var $md5_gz;
    var $md5_transfered;
    var $master_data;
    var $slave_data;
    var $size_file;
    var $size_gz;
    var $size_transfered;

    use \Glial\Neuron\PmaCli\PmaCliBackup;

//use \Glial\Neuron\Controller\PmaCliBackup;
    function before($param)
    {
        if (!IS_CLI) {
            $this->layout_name = 'pmacontrol';
        }
    }

    public function sendKeyPub()
    {
        foreach ($this->db as $key => $db) {

            $server_config = $db->getParams();

            shell_exec("ssh-copy-id -i ~/.ssh/id_dsa.pub alequoy@".$server_config['hostname']."");
        }
    }

    public function checkDirectory($dir)
    {

        if (!(file_exists($dir) && is_dir($dir))) {
            passthru("mkdir -p ".$dir, $exit);
            if ($exit !== 0) {
                throw new \Exception(
                "GLI-017 : Impossible to create the directory : ".$dir, $exit);
            }
        }

        return true;
    }

    public function cmd($cmd)
    {

        passthru($cmd, $exit);

        if ($exit !== 0) {

            $cmd = preg_replace("/-p[^ ]*/", "-p", $cmd);
            throw new \Exception("GLI-018 : CMD FAIL ! : ".$cmd.PHP_EOL, $exit);
        }
    }

    public function compress_dump()
    {

        $this->view = false;

        $sql = "SELECT file_name FROM mysql_dump where is_gziped=0";

        $db = $this->di['db']->sql(DB_DEFAULT);

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            try {
                $cmd = "gzip ".$ob->file_name;
                echo $cmd."\n";
                $this->cmd($cmd);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        $sql = "UPDATE mysql_dump SET is_gziped=1 where is_gziped=0";
        $db->sql_query($sql);
    }

    public function backupUser()
    {


        $this->view = false;

        foreach ($this->di['db']->getAll() as $db) {

            $server_on = true;

            try {

                $dblink = $this->di['db']->sql($db);


                echo str_repeat("#", 80)."\n";
                echo '#'.$dblink->host."-".$dblink->port."\n";
                echo str_repeat("#", 80)."\n";


                $path = "/data/backup/user/".$dblink->host."-".$dblink->port;
                $this->cmd("mkdir -p ".$path);


                $handle = fopen($path."/".date("Y-m-d_H-i-s").".sql", "w");

                if ($handle) {

                    $sql = 'SELECT user,host as hostname FROM mysql.user order by user, host';
                    $res = $dblink->sql_query($sql);

                    while ($ob = $dblink->sql_fetch_object($res)) {
                        $sql  = "show grants for '".$ob->user."'@'".$ob->hostname."'";
                        $res2 = $dblink->sql_query($sql);

                        while ($tab = $dblink->sql_fetch_array($res2, MYSQLI_NUM)) {

                            fwrite($handle, $tab[0].";\n");
                        }
                    }

                    fclose($handle);
                }
            } catch (\Exception $ex) {

                echo 'Exception found : ', $ex->getMessage(), "\n";
            }
        }
    }

    public function listing()
    {
        $this->layout_name = 'pmacontrol';

        $this->title  = __("Backup's list");
        $this->ariane = " > ".__("Backup management")." > ".$this->title;


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT *, a.id as id_dump FROM `mysql_dump` a
        INNER JOIN mysql_server b ON a.id_mysql_server = b.id
        WHERE date_start >= NOW() - INTERVAL 12 DAY 
        order by  date_start DESC,b.name,`database` asc	";

        $data['dump'] = $db->sql_fetch_yield($sql);


        $this->set('data', $data);
    }

    public function getDump($param)
    {

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM `mysql_dump` WHERE id=".$db->sql_real_escape_string($param[0])."";
        $res = $db->sql_query($sql);

        if ($db->sql_num_rows($res) == 1) {

            $ob = $db->sql_fetch_object($res);

            $filename = true;
            if (file_exists($ob->file_name)) {
                $filename = $ob->file_name;
            } elseif (file_exists($ob->file_name.".gz")) {
                $filename = $ob->file_name.".gz";
            }

            if ($filename) {

                $this->layout_name = false;
                $this->view        = false;

                $pathinfo = pathinfo($filename);
                header("Cache-Control: no-cache, must-revalidate");
                header("Cache-Control: post-check=0,pre-check=0");
                header("Cache-Control: max-age=0");
                header("Pragma: no-cache");
                header("Expires: 0");
                header("Content-Type: application/force-download");
                header('Content-Disposition: attachment; filename="'.$pathinfo['basename'].'"');
                header("Content-Length: ".shell_exec("stat -c %s ".$filename));


                $handle = fopen($filename, "r");
                if ($handle) {
                    while (($buffer = fgets($handle)) !== false) {
                        echo $buffer;
                    }

                    fclose($handle);
                }
            }
        }


//fail
    }

    public function storageArea($param)
    {
        $this->title  = __("Storage area");
        $this->ariane = " > ".__("Backup management")." > ".$this->title;
        $db           = $this->di['db']->sql(DB_DEFAULT);

        if (empty($param[0])) {
            $data['menu'] = "listStorage";
        } else {
            $data['menu'] = $param[0];
        }

        $sql = "SELECT count(1) as cpt from backup_storage_area";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $data['cpt'] = $ob->cpt;
        }
        $this->set('data', $data);
    }

    public function add()
    {

//df -Ph . | tail -1 | awk '{print $2}' => to know space

        $db = $this->di['db']->sql(DB_DEFAULT);
        if ($_SERVER['REQUEST_METHOD'] == "POST") {

            Crypt::$key = CRYPT_KEY;

            $storage_area['backup_storage_area'] = $_POST['backup_storage_area'];


            

            if (!Ssh::testAccount($storage_area['backup_storage_area']['ip'], $storage_area['backup_storage_area']['port'],
                    $storage_area['backup_storage_area']['ssh_login'], $storage_area['backup_storage_area']['ssh_password'])) {


                foreach ($_POST['backup_storage_area'] as $var => $val) {
                    $ret[] = "backup_storage_area:".$var.":".urlencode(html_entity_decode($val));
                }

                $param = implode("/", $ret);

                $title = I18n::getTranslation(__("Failed to connect on ssh/scp/sFtp"));
                $msg   = I18n::getTranslation(__("Please check your hostname and you credentials !"));

                set_flash("error", $title, $msg);

                header("location: ".LINK."backup/storageArea/add/".$param);
                exit;
            }

            $storage_area['backup_storage_area']['ssh_login']    = Crypt::encrypt($storage_area['backup_storage_area']['ssh_login']);
            $storage_area['backup_storage_area']['ssh_password'] = Crypt::encrypt($storage_area['backup_storage_area']['ssh_password']);

            if (!$id_storage_area = $db->sql_save($storage_area)) {


                $error             = $db->sql_error();
                $_SESSION['ERROR'] = $error;

                $title = I18n::getTranslation(__("Fail to add this storage area"));
                $msg   = I18n::getTranslation(__("One or more problem came when you try to add this storage, please verify your informations"));

                set_flash("error", $title, $msg);

                foreach ($_POST['backup_storage_area'] as $var => $val) {
                    $ret[] = "backup_storage_area:".$var.":".urlencode(html_entity_decode($val));
                }

                $param = implode("/", $ret);

                header("location: ".LINK."backup/storageArea/add/".$param);
                exit;
            } else {


                $this->getStorageSpace(array($id_storage_area));

                $title = I18n::getTranslation(__("Successfull"));
                $msg   = I18n::getTranslation(__("You storage area has been successfull added !"));

                set_flash("success", $title, $msg);
            }
        }


        $this->di['js']->addJavascript(array("http://www.estrildidae.net/js/jquery.1.3.2.js", "jquery.autocomplete.min.js"));
//$this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.autocomplete.min.js"));

        $this->di['js']->code_javascript('$("#backup_storage_area-id_geolocalisation_city-auto").autocomplete("'.LINK.'user/city/ajax>yes", {
		extraParams: {
			country: function() {return $("#backup_storage_area-id_geolocalisation_country").val();}
		},
        mustMatch: true,
        autoFill: true,
        max: 100,
        scrollHeight: 302,
        delay:0
		});
		$("#backup_storage_area-id_geolocalisation_city-auto").result(function(event, data, formatted) {
			if (data)
				$("#backup_storage_area-id_geolocalisation_city").val(data[1]);
		});
		$("#backup_storage_area-id_geolocalisation_country").change( function() 
		{
			$("#backup_storage_area-id_geolocalisation_city-auto").val("");
			$("#backup_storage_area-id_geolocalisation_city").val("");
		} );

		');

        $sql                                   = "SELECT id, libelle from geolocalisation_country where libelle != '' order by libelle asc";
        $res                                   = $db->sql_query($sql);
        $this->data['geolocalisation_country'] = $db->sql_to_array($res);

        $this->set('data', $this->data);
    }

    public function listStorage()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT *,c.libelle as city, a.libelle as name,a.id as id_backup_storage_area
        FROM backup_storage_area a
        INNER JOIN geolocalisation_country b ON a.id_geolocalisation_country = b.id
        INNER JOIN geolocalisation_city c ON c.id = a.id_geolocalisation_city
        ORDER BY a.libelle";

        $data['storage'] = $db->sql_fetch_yield($sql);

        $sql = "SELECT * FROM backup_storage_space b  
        JOIN (select max(id) as id from backup_storage_space a group by id_backup_storage_area) a ON a.id = b.id";

        $res = $db->sql_query($sql);

        while ($tab = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $data['space'][$tab['id_backup_storage_area']] = $tab;
        }

        $this->set('data', $data);
    }

    public function getStorageSpace($param)
    {
        $this->layout_name = false;
        $this->view        = false;


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM backup_storage_area";

        if (!empty($param[0])) {
            $sql .= " WHERE id = '".$param[0]."'";
        }

        $storages = $db->sql_fetch_yield($sql);

        Crypt::$key = CRYPT_KEY;

        foreach ($storages as $storage) {

            $login    = Crypt::decrypt($storage['ssh_login']);
            $password = Crypt::decrypt($storage['ssh_password']);
            $ssh      = new Ssh($storage['ip'], $storage['port'], $login, $password);
            $ssh->connect();

            /*
             * df -k . => get file systeme for current directory
             * tail -n +2 => remove the first line
             * sed ':a;N;$!ba;s/\n/ /g' => remove \n (in case of the name of partition is really big and need to be on 2 lines)
             * sed \"s/\ +/ /g\" => remove + in some case
             * awk '{print $2 \" \" $3 \" \" $4 \" \" $5}' => split result by space
             */

            $cmd       = 'cd '.$storage['path'].' && df -k . | tail -n +2 | sed ":a;N;$!ba;s/\n/ /g" | sed "s/\ +/ /g"';
            $resultats = $ssh->exec($cmd);
            $resultats = preg_replace('`([ ]{2,})`', ' ', $resultats);
            $results   = explode(' ', trim($resultats));


            $cmd2           = "cd ".$storage['path']." && du -s . | awk '{print $1}'";
            $used_by_backup = $ssh->exec($cmd2);

            $data                                                   = [];
            $data['backup_storage_space']['id_backup_storage_area'] = $storage['id'];
            $data['backup_storage_space']['date']                   = date('Y-m-d H:i:s');
            $data['backup_storage_space']['size']                   = $results['1'];
            $data['backup_storage_space']['used']                   = $results['2'];
            $data['backup_storage_space']['available']              = $results['3'];
            $data['backup_storage_space']['percent']                = substr(trim($results['4']), 0, -1);
            $data['backup_storage_space']['backup']                 = trim($used_by_backup);

            if (!$db->sql_save($data)) {


                debug($cmd."\n");
                debug($resultats);
                debug($results);
                debug($data);
                debug($db->sql_error());
                echo "\n";
            }
        }
    }

    public function settings()
    {

        $this->title  = __("Schedules");
        $this->ariane = ' > <a href="#"><span class="glyphicon glyphicon-floppy-disk" style="font-size:12px"></span> '.__("Backup management").'</a> > <span class="glyphicon glyphicon-cog" style="font-size:12px"></span> '.$this->title;



        $db = $this->di['db']->sql(DB_DEFAULT);

        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            foreach ($_POST['backup_database']as $key => $elem) {

                try {
                    $db->sql_query('SET AUTOCOMMIT=0;');
                    $db->sql_query('START TRANSACTION;');

                    $crontab            = [];
                    $crontab['crontab'] = $_POST['crontab'][$key];

                    $id_crontab = $db->sql_save($crontab);
                    if (!$id_crontab) {
                        debug($crontab);
                        debug($db->sql_error());
                        throw new Exception("PMACTRL-052 : impossible to save crontab");
                    }

                    $backup_database                                  = [];
                    $backup_database['backup_database']               = $elem;
                    $backup_database['backup_database']['id_crontab'] = $id_crontab;
                    $backup_database['backup_database']['is_active']  = 1;

                    if (!$id_backup_database = $db->sql_save($backup_database)) {
                        debug($backup_database);
                        debug($db->sql_error());
                        throw new Exception("PMACTRL-053 : impossible to shedule this backup");
                    }

                    $cmd = "php ".GLIAL_INDEX." crontab monitor backup saveDb ".$id_backup_database;

                    Crontab::insert($crontab['crontab']['minutes'], $crontab['crontab']['hours'], $crontab['crontab']['day_of_month'],
                        $crontab['crontab']['month'], $crontab['crontab']['day_of_week'], $cmd, "Backup database with PmaControl", $id_crontab);

                    $crontab                       = [];
                    $crontab['crontab']['id']      = $id_crontab;
                    $crontab['crontab']['command'] = $cmd;

                    if (!$db->sql_save($crontab)) {
                        debug($crontab);
                        debug($db->sql_error());
                        throw new Exception("PMACTRL-054 : impossible to set command into crontab");
                    }


                    $db->sql_query('COMMIT;');
                } catch (\Exception $ex) {

                    Crontab::delete($id_crontab);
                    $db->sql_query('ROLLBACK;');
                }
            }
        }


        $sql = "SELECT *, c.name as server_name, b.libelle as nas, e.libelle as backup_type,a.id as id_backup_database
            FROM backup_database a
            INNER JOIN backup_storage_area b ON a.id_backup_storage_area = b.id
            INNER JOIN mysql_server c ON c.id = a.id_mysql_server
            INNER JOIN mysql_database d ON d.id = a.id_mysql_database
            INNER JOIN backup_type e ON e.id = a.id_backup_type
            INNER JOIN crontab f on f.id = a.id_crontab
            ORDER BY c.name, d.name";


        $data['backup_list'] = $db->sql_fetch_yield($sql);

        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.browser.min.js", "jquery.autocomplete.min.js", "Backup/settings.js"));



        $sql = "SELECT a.name, a.id, a.ip, group_concat('',b.id) as id_mysql_database, group_concat('',b.name) as db
                from mysql_server a
                INNER JOIN mysql_database b ON a.id = b.id_mysql_server
                GROUP BY a.name, a.id, a.ip
                ORDER BY a.name";

        $data['database_list'] = $db->sql_fetch_yield($sql);

        $sql = "SELECT * FROM backup_type order by libelle";

        $res = $db->sql_query($sql);


        $data['type_backup'] = [];
        while ($ob                  = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle;

            $data['type_backup'][] = $tmp;
        }

        $sql                  = "SELECT * FROM backup_storage_area order by libelle";
        $res                  = $db->sql_query($sql);
        $data['storage_area'] = [];
        while ($ob                   = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->libelle." (".$ob->ip.")";

            $data['storage_area'][] = $tmp;
        }

        $sql               = "SELECT id,name FROM mysql_database WHERE id_mysql_server = (SELECT min(id_mysql_server) from mysql_database) order by name";
        $res               = $db->sql_query($sql);
        $data['databases'] = [];
        while ($ob                = $db->sql_fetch_object($res)) {
            $tmp                 = [];
            $tmp['id']           = $ob->id;
            $tmp['libelle']      = $ob->name;
            $data['databases'][] = $tmp;
        }




        $this->set('data', $data);
    }
    /* used for ajax */

    function getDatabaseByServer($param)
    {

        $this->layout_name = false;
        $db                = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id,name FROM mysql_database WHERE id_mysql_server = '".$db->sql_real_escape_string($param[0])."';";


        $res = $db->sql_query($sql);

        $data['databases'] = [];
        while ($ob                = $db->sql_fetch_object($res)) {
            $tmp            = [];
            $tmp['id']      = $ob->id;
            $tmp['libelle'] = $ob->name;

            $data['databases'][] = $tmp;
        }


        $this->set("data", $data);
    }

    function getServerByName($param)
    {
        $this->layout_name = false;
        $this->view        = false;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM `mysql_server` WHERE `name` LIKE '%".$db->sql_real_escape_string($_GET['q'])."%';";

        $data = $db->sql_fetch_yield($sql);

        foreach ($data as $line) {
            echo $line['name']."|".$line['id']."|".$line['ip']."\n";
        }
    }
    /* used for ajax */

    function getServerByIp($server)
    {
        $this->layout_name = false;
        $this->view        = false;

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT * FROM `mysql_server` WHERE `ip` LIKE '%".$db->sql_real_escape_string($_GET['q'])."%';";

        $data = $db->sql_fetch_yield($sql);

        foreach ($data as $line) {
            echo $line['ip']."|".$line['id']."|".$line['name']."\n";
        }
    }

    public function saveDb($param)
    {

        //23 heures max pour effectuer le backup
        \set_time_limit(3600 * 23);

        $this->view = false;

        $id_backup_database = $param[0];

        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT *, c.name as server_name, b.libelle as nas, 
            e.libelle as backup_type,a.id as id_backup_database,
            c.name as id_connection, d.name as db_name, b.ip as ip_nas, b.port as port_nas,b.path as path_nas,
            c.ssh_login as mysql_ssh_login,
            c.ssh_password as mysql_ssh_password,
            b.ssh_login as nas_ssh_login,
            b.ssh_password as nas_ssh_password,
            c.ip as mysql_ip,
            c.port as mysql_port
            FROM backup_database a
            INNER JOIN backup_storage_area b ON a.id_backup_storage_area = b.id
            INNER JOIN mysql_server c ON c.id = a.id_mysql_server
            INNER JOIN mysql_database d ON d.id = a.id_mysql_database
            INNER JOIN backup_type e ON e.id = a.id_backup_type
            INNER JOIN crontab f on f.id = a.id_crontab
            WHERE a.id = '".$id_backup_database."'";

        $res = $db->sql_query($sql);


        if ($db->sql_num_rows($res) != 1) {
            throw new \Exception("PMACTRL-066 : Impossible to find this id in backup_database($id_backup_database)");
        }

        $backups = $db->sql_to_array($res);
        $backup  = $backups[0];

        $backup_dump                                      = [];
        $backup_dump['backup_dump']['id_backup_database'] = $backup['id_backup_database'];
        $backup_dump['backup_dump']['date_start']         = date("Y-m-d H:i:s");


        $id_backup_dump = $db->sql_save($backup_dump);


        $start_time = microtime(true);


        $db->sql_close();


        $this->time_backup_start = microtime(true);

        switch ($backup['backup_type']) {
            case 'mysqldump':

                $source = $this->mysqldump($backup);
                break;

            default:

                throw new \Exception('PMACTRL-056 Backup with "'.$backup['backup_type'].'" not supported yet');
                break;
        }



        Crypt::$key              = CRYPT_KEY;
        $paramSource['hostname'] = $backup['mysql_ip'];
        $paramSource['port']     = $backup['mysql_port'];
        $paramSource['login']    = "pmacontrol";
        $paramSource['passwd']   = Crypt::decrypt(PMACONTROL_PASSWD);

        $paramDestination['hostname'] = $backup['ip_nas'];
        $paramDestination['port']     = $backup['port_nas'];
        $paramDestination['login']    = Crypt::decrypt($backup['nas_ssh_login']);
        $paramDestination['passwd']   = Crypt::decrypt($backup['nas_ssh_password']);

        $file_name = pathinfo($source)['basename'];

        $destination = $backup['path_nas']."/".$backup['server_name']."/".$backup['db_name']."/".$file_name;

        $this->sendBackup($source, $destination, $paramSource, $paramDestination);


        $this->time_transfert = microtime(true);


        $db = $this->di['db']->sql(DB_DEFAULT);


        $data                                   = [];
        $data['backup_dump']['id']              = $id_backup_dump;
        $data['backup_dump']['date_end']        = date("Y-m-d H:i:s");
        $data['backup_dump']['time']            = round($this->time_backup_end - $this->time_backup_start, 0);
        $data['backup_dump']['time_gz']         = round($this->time_gzip - $this->time_backup_end, 0);
        $data['backup_dump']['time_transfered'] = round($this->time_transfert - $this->time_gzip, 0);

        $data['backup_dump']['md5']            = $this->md5_file;
        $data['backup_dump']['md5_gz']         = $this->md5_gz;
        $data['backup_dump']['md5_transfered'] = $this->md5_transfered;

        $data['backup_dump']['is_completed'] = 1;


        $data['backup_dump']['size_file']       = $this->size_file;
        $data['backup_dump']['size_gz']         = $this->size_gz;
        $data['backup_dump']['size_transfered'] = $this->size_transfered;


        $data['backup_dump']['master_data'] = $this->master_data;
        $data['backup_dump']['slave_data']  = $this->slave_data;



        $res = $db->sql_save($data);

        if (!$res) {
            debug($db->sql_error());
            debug($data);
        }
    }

    public function deleteShedule($param)
    {
        $this->layout_name = false;
        $this->view        = false;
        $db                = $this->di['db']->sql(DB_DEFAULT);
        $id                = $param[0];

        $sql = "SELECT id_crontab FROM backup_database WHERE id ='".$id."'";
        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $id_crontab = $ob->id_crontab;
        }
        $sql = "DELETE FROM backup_database WHERE id='".$id."'";
        $db->sql_query($sql);
        Crontab::delete($id_crontab);
        $sql = "DELETE FROM crontab WHERE id='".$id_crontab."'";
        $db->sql_query($sql);

        header('location: '.LINK.'backup/settings');
    }

    public function toggleShedule($param)
    {
        $this->layout_name = false;
        $this->view        = false;

        $db = $this->di['db']->sql(DB_DEFAULT);
        $id = $param[0];

        $sql = "SELECT * FROM backup_database a
                INNER JOIN crontab b ON a.id_crontab = b.id
                WHERE a.id ='".$id."'";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {

            if ($ob->is_active === "1") {
                Crontab::delete($ob->id_crontab);
            } else {
                Crontab::insert($ob->minutes, $ob->hours, $ob->day_of_month, $ob->month, $ob->day_of_week, $ob->command, $ob->comment, $ob->id_crontab);
            }

            $id_crontab = $ob->id_crontab;
            $is_active  = $ob->is_active;
        }

        $backup_database                                 = [];
        $backup_database['backup_database']['id']        = $id;
        $backup_database['backup_database']['is_active'] = (empty($is_active)) ? 1 : 0;

        if (!$db->sql_save($backup_database)) {

            debug($backup_database);

            throw new Exception('PMACTRL-025 : impossible to update is_active');
        }

        header('location: '.LINK.'backup/settings');
    }

    function mysqldump($backup)
    {
        //$this->backup_dir = $this->backup_dir;

        $MS = new MasterSlave();

        $dumpoptions = " --quick --add-drop-table --default-character-set=utf8 --extended-insert ";

        $db_to_backup = $this->di['db']->sql($backup['id_connection']);
        $MS->setInstance($db_to_backup);

        $server_config = $db_to_backup->getParams();


        debug($backup['id_connection']);
        debug($server_config);


        $slave  = $MS->isSlave();
        $master = $MS->isMaster();

        $userpassword = " -h ".$server_config['hostname']." -P ".$backup['port']." -u ".$server_config['user']." -p".$server_config['password'];


        if ($slave) {

            $stop_slave = "STOP SLAVE;"; //because option --dump-slave restart replication after made the dump
            if ($db_to_backup->isMultiMaster()) {
                $stop_slave = "STOP ALL SLAVES;";
            }
            $cmd = "mysql ".$userpassword." -e '".$stop_slave.";'";
            $this->cmd($cmd);


            debug($slave);


            $slave = $MS->isSlave();

            $this->slave_data = json_encode($slave);
        }

        if ($master) {

            debug($master);

            $this->master_data = json_encode($master);
        }
//$backup['path']


        $file_name = $backup['db_name']."_".date("Y-m-d_His")."__".$backup['db_name'].".sql";
        $this->checkDirectory($this->backup_dir);

        $extra = " ";

        if ($master) {
            $extra .= " --master-data=2 --single-transaction";
        }

        if ($slave) {
            if (version_compare($version, "5.5.3", ">")) {
                $extra .= " --dump-slave=2";
            }
        }

        $this->di['db']->sql(DB_DEFAULT)->sql_close();

//$this->di['db']->sql(DB_DEFAULT);
//echo $mysql_dump . "\n";

        Crypt::$key         = CRYPT_KEY;
        $mysql_ssh_login    = Crypt::decrypt($backup['mysql_ssh_login']);
        $mysql_ssh_password = Crypt::decrypt($backup['mysql_ssh_password']);

        $nas_ssh_login    = Crypt::decrypt($backup['nas_ssh_login']);
        $nas_ssh_password = Crypt::decrypt($backup['nas_ssh_password']);

        $pmauser   = 'pmacontrol';
        $pmapasswd = Crypt::decrypt(PMACONTROL_PASSWD);


        echo "{$backup['ip']}, 22, $pmauser, $pmapasswd);\n";

        $ccc = new Ssh($backup['ip'], 22, $pmauser, $pmapasswd);
        $ccc->connect();

        $pwd = $ccc->exec('pwd');

        debug($pwd);

        $screen = $ccc->whereis("screen");
        //$screen = "/usr/bin/screen";

        $mysqldump = $ccc->whereis("mysqldump");

        $cmd       = $mysqldump.$userpassword.$dumpoptions.$extra." ".$backup['db_name']." > ".$this->backup_dir."/".$file_name;
        $id_backup = $backup['id_backup_database'];


        echo "MYSQL_DUMP CMD : ".$cmd."\n";

        $ccc->exec("echo \"#!/bin/sh\n$cmd\" > ".$this->backup_dir."/mysqldump.$id_backup.sh");

        $ccc->exec("echo \"#!/bin/sh\n$screen -S backup_database_$id_backup -d -m ".$this->backup_dir."/mysqldump.$id_backup.sh\n"
            ."$screen -list | grep backup_database_$id_backup | head -n1 | cut -f1 -d'.' | sed 's/\s//g' > ".$this->backup_dir."/pid.$id_backup.pid\n"
            ."\" > ".$this->backup_dir."/backup.$id_backup.sh");

        $ccc->exec("chmod +x ".$this->backup_dir."/mysqldump.".$backup['id_backup_database'].".sh");
        $ccc->exec("chmod +x ".$this->backup_dir."/backup.".$backup['id_backup_database'].".sh");
        $exec = $ccc->exec("sh ".$this->backup_dir."/backup.".$backup['id_backup_database'].".sh");


        $pid = trim($ccc->exec("cat ".$this->backup_dir."/pid.".$backup['id_backup_database'].".pid"));


        $waiting = ['/', '-', '\\', '|'];
        $i       = 0;


        echo "backup in progress ... ";
        do {
            $i++;

            $mod = $i % 4;
            echo " ".$waiting[$mod];
            echo "\033[2D";
            sleep(1);

            $nb_thread = $ccc->exec("ps -p $pid | grep $pid | wc -l");

            switch (trim($nb_thread)) {
                case "1":
                    $continue = true;
                    break;
                case "0":
                    $continue = false;
                    break;

                default:


                    throw new Exception("PMACTRL-085 : more than one thread ($nb_thread) have to audit code !");
                    break;
            }
        } while ($continue);



        if (!strpos("dump-slave", $extra) && $slave) {
            $start_slave = "START SLAVE;"; //because option --dump-slave restart replication after made the dump
            if ($db_to_backup->isMultiMaster()) {
                $start_slave = "START ALL SLAVES;";
            }
            $cmd = "mysql ".$userpassword." -e '".$start_slave.";'";
            $this->cmd($cmd);
        }




        $this->time_backup_end = microtime(true);
        $full_path             = $this->backup_dir."/".$file_name;
        $file_gz               = $this->backup_dir."/".$file_name.".gz";

        //get md5 of file
        $this->md5_file  = trim($ccc->exec("md5sum ".$this->backup_dir."/".$file_name." | awk '{ print $1 }'"));
        //get size of file
        $this->size_file = trim($ccc->exec("du -s ".$this->backup_dir."/".$file_name." | awk '{ print $1 }'"));

        $cmd = "nice gzip -c ".$this->backup_dir."/".$file_name.">".$file_gz;

        $ret = $ccc->exec($cmd);

        //get md5 of file
        $this->md5_gz = trim($ccc->exec("md5sum ".$this->backup_dir."/".$file_name.".gz | awk '{ print $1 }'"));


        $this->size_gz = trim($ccc->exec("du -s ".$this->backup_dir."/".$file_name.".gz | awk '{ print $1 }'"));

        $this->time_gzip = microtime(true);

        //remove old backup


        $grep  = $ccc->whereis("grep");
        $ls    = $ccc->whereis("ls");
        $sed   = $ccc->whereis("sed");
        $xargs = $ccc->whereis("xargs");
        $rm    = $ccc->whereis("rm");

        $cmd = 'cd '.$this->backup_dir.' && '.$ls.' -t | '.$grep.' \'__'.$backup['db_name'].'.sql\' | '.$sed.' -e \'1,2d\' | '.$xargs.' -d \'\n\' '.$rm.''."\n";
        $ret = $ccc->exec($cmd);

        $ret = $ccc->exec("ls ".$file_gz." | wc -l");

        if (trim($ret) === "1") {
            return $file_gz;
        }

        throw new \Exception("PMACTRL-052 : file not found '".$file_gz."'");
        echo "\n";
        
        return false;
    }

    private function deleteBackup()
    {
        
    }

    public function test()
    {
        // "/[\w-\d_-]+@[\w\d_-]+:[\~]?(?:\/[\w\d_-]+)*(?:\$|\#)[\s]?/";

        $input_lines = 'logftp@srv-backup-01:/data/Save/DB_ITPROD$';
        preg_match_all('/[\w-\d_-]+@[\w\d_-]+:[\~]?(?:\/[\w-\d_-]+)*(?:\$|\#)[\s]?/', $input_lines, $output_array);

        debug($input_lines);
        debug($output_array);

        $this->view = false;
    }

    public function dump()
    {
        $this->layout_name = 'pmacontrol';

        $this->title  = __("Backup's list");
        $this->ariane = " > ".__("Backup management")." > ".$this->title;


        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT *, a.id as id_dump, e.ip as ip_nas ,f.name as `database`, b.id as id_backup,d.name as server_name
            FROM `backup_dump` a
            INNER JOIN backup_database b ON a.id_backup_database = b.id
            INNER JOIN backup_type c ON c.id = b.id_backup_type
            INNER JOIN backup_storage_area e ON e.id = b.id_backup_storage_area
            INNER JOIN mysql_server d ON b.id_mysql_server = d.id
            INNER JOIN mysql_database f ON b.id_mysql_database = f.id
        WHERE date_start >= NOW() - INTERVAL 12 DAY 
        order by  date_start DESC";

        $data['dump'] = $db->sql_fetch_yield($sql);
        $this->set('data', $data);
    }

    private function sendBackup($source, $destination, $paramSource = array(), $paramDestination = array())
    {
        $this->view = false;

        $nas = new Ssh($paramDestination['hostname'], $paramDestination['port'], $paramDestination['login'], $paramDestination['passwd']);
        $nas->connect();

        $target_directory = pathinfo($destination)['dirname'];


        $line = $nas->exec("mkdir -p ".$target_directory);
        $pos  = strpos($line, "Permission denied");

        if ($pos !== false) {

            throw new \Exception("\nPMACTRL-077 : Impossible to create the directory : '".$target_directory."'");
        }


        $mysql = new Ssh($paramSource['hostname'], 22, $paramSource['login'], $paramSource['passwd']);
        $mysql->connect(Ssh::DEBUG_ON);

        $mysql->openShell();

        sleep(1);
        $mysql->waitPrompt($buffer);



        sleep(1);
        $scp = "scp $source ".$paramDestination['login']."@".$paramDestination['hostname'].":".$target_directory;
        $mysql->shellCmd($scp);

        $ret = $mysql->waitPrompt($buffer, '\?');
        if ($ret === Ssh::SSH_PROMPT_FOUND) {
            $mysql->shellCmd("yes");
        }


        $out = end(explode(PHP_EOL, $buffer));

        //set password
        $pos = strpos("password:", $out);

        if ($pos !== false) {

            echo ">>><<<";
            $mysql->shellCmd($paramDestination['passwd']);
        } else {
            $mysql->waitPrompt($buffer, "password\:\s*$");
            $mysql->shellCmd($paramDestination['passwd']);
        }

        $mysql->waitPrompt($buffer);

        $file                  = pathinfo($source)['basename'];
        $this->size_transfered = trim($nas->exec("du -s ".$target_directory."/".$file." | awk '{ print $1 }'"));
        $this->md5_transfered  = trim($nas->exec("md5sum ".$target_directory."/".$file." | awk '{ print $1 }'"));

        unset($nas);
    }

    public function addUserPmaControl()
    {

        $this->layout_name = false;
        $this->view        = false;


        fwrite(STDOUT, str_repeat("#", 80)."\n");
        $text = "Create user pmacontrol, Generating SSH keys and install it !";
        fwrite(STDOUT, "#".str_repeat(" ", (80 - strlen($text) - 2) / 2).$text.str_repeat(" ", (80 - strlen($text) - 2) / 2)."#\n");
        fwrite(STDOUT, str_repeat("#", 80)."\n");


        $passwdpma = $this->setPasswdPmaControl();

        fwrite(STDOUT, str_repeat("-", 80)."\n");

        $db      = $this->di['db']->sql(DB_DEFAULT);
        $sql     = "SELECT * FROM mysql_server";
        $servers = $db->sql_fetch_yield($sql);


        Crypt::$key = CRYPT_KEY;


        $account_valided = [];


        foreach ($servers as $server) {

            fwrite(STDOUT, "try to connect in ssh to ".$server['ip']." \n");
            $login    = Crypt::decrypt($server['ssh_login']);
            $password = Crypt::decrypt($server['ssh_password']);

            $failed = true;
            $run    = 1;

            reset($account_valided);

            /*
             * Try credntial in mysql_server (from db.config.ini)
             * else try last credentials successfully worked
             * to finish set your login // password
             */

            do {

                echo "Trying credential (".$server['ip']." login : ".$login." - password : ".$password.")- run n°".$run."\n";
                $ssh = new Ssh($server['ip'], 22, $login, $password);

                if ($ssh->connect(Ssh::DEBUG_ON)) {
                    fwrite(STDOUT, "Successfully connected\n");

                    $tmp             = [];
                    $tmp['login']    = $login;
                    $tmp['password'] = $password;

                    $account_valided[md5($tmp['login'].$tmp['password'])] = $tmp;

                    // create account pmacontrol

                    $ssh->openShell();
                    $ssh->waitPrompt($buffer);
                    $ssh->shellCmd("sudo su -");

                    $ret = $ssh->waitPrompt($buffer, 'password\sfor\s[a-z]+\:');

                    if ($ret) {
                        $ssh->shellCmd($password);
                        $ssh->waitPrompt($buffer);
                    }

                    $ssh->shellCmd("getent passwd pmacontrol");
                    $ret = $ssh->waitPrompt($buffer, 'pmacontrol\:');


                    if ($ret !== false) {
                        $ssh->shellCmd("useradd -ou 0 -g 0 pmacontrol");
                        $ssh->waitPrompt($buffer);
                    }

                    $ssh->shellCmd("passwd pmacontrol");
                    $ssh->waitPrompt($buffer, ":");
                    $ssh->shellCmd($passwdpma);

                    $ssh->waitPrompt($buffer, ":");
                    $ssh->shellCmd($passwdpma);
                    $ssh->waitPrompt($buffer);

                    $failed = false;
                } else {
                    fwrite(STDOUT, "Failed to connect\n");
                    list($elem) = each($account_valided);

                    if (isset($elem)) {
                        $login    = $account_valided[$elem]['login'];
                        $password = $account_valided[$elem]['password'];
                    } else {
                        $user     = $this->getLoginPassword();
                        $login    = $user['login'];
                        $password = $user['passwd'];
                    }
                }

                $run++;
            } while ($failed);

            //fwrite(STDOUT, "useradd -ou 0 -g 0 pmacontrol\n");


            fwrite(STDOUT, str_repeat("-", 80)."\n");


            unset($ssh);
        }
    }

    function promptSilent($prompt = "Enter Password:")
    {
        if (preg_match('/^win/i', PHP_OS)) {
            $vbscript = sys_get_temp_dir().'prompt_password.vbs';
            file_put_contents(
                $vbscript, 'wscript.echo(InputBox("'
                .addslashes($prompt)
                .'", "", "password here"))');
            $command  = "cscript //nologo ".escapeshellarg($vbscript);
            $password = rtrim(shell_exec($command));
            unlink($vbscript);
            return $password;
        } else {
            $command = "/usr/bin/env bash -c 'echo OK'";
            if (rtrim(shell_exec($command)) !== 'OK') {
                trigger_error("Can't invoke bash");
                return;
            }
            $command  = "/usr/bin/env bash -c 'read -s -p \""
                .addslashes($prompt)
                ."\" mypassword && echo \$mypassword'";
            $password = rtrim(shell_exec($command));
            echo "\n";
            return $password;
        }
    }

    function getLoginPassword()
    {

        fwrite(STDOUT, "Login :");
        //$login = fread(STDIN);
        $login = trim(fgets(STDIN));


        $passwd = self::promptSilent($login.' s password: ');

        $ret           = [];
        $ret['login']  = $login;
        $ret['passwd'] = $passwd;

        return $ret;
    }

    public function setPasswdPmaControl()
    {

        
        $this->view = false;

        do {
            fwrite(STDOUT, "Choose password for remote user pmacontrol\n");
            $passwdpma  = self::promptSilent("Enter new UNIX password:");
            $passwdpma2 = self::promptSilent("Retype new UNIX password:");

            if ($passwdpma != $passwdpma2) {
                echo Color::getColoredString("Sorry, passwords do not match\n\n", "red");
            }
        } while ($passwdpma != $passwdpma2);

        Crypt::$key = CRYPT_KEY;

        $password_crypted = Crypt::encrypt($passwdpma);

        $file = "<?php
if (! defined('PMACONTROL_PASSWD'))
{
    define('PMACONTROL_PASSWD', '".$password_crypted."');
}
";
        file_put_contents(CONFIG."pmacontrol.config.php", $file);

        return $passwdpma;
    }

    public function graph($param)
    {
        $sql = "SELECT * 
        FROM backup_database a
        INNER JOIN backup_dump b ON a.id = b.id_backup_database
        WHERE a.id = '".$param[0]."'
        ORDER BY b.date_start asc";


        //echo $sql;

        $db = $this->di['db']->sql(DB_DEFAULT);


        $res = $db->sql_query($sql);

        $time_backup     = [];
        $time_gz         = [];
        $time_transfered = [];
        $dates           = [];
        $size_file       = [];
        $size_gz         = [];
        $size_transfered = [];


        while ($ob = $db->sql_fetch_object($res)) {
            $time_backup[]     = $ob->time;
            $time_gz[]         = $ob->time_gz;
            $time_transfered[] = $ob->time_transfered;
            $dates[]           = $ob->date_start;

            $size_file[] = $ob->size_file;
            $size_gz[]   = $ob->size_gz;

            $size_transfered[] = $ob->size_transfered;

            $obj = $ob;
        }


        $this->di['js']->addJavascript(array("http://code.highcharts.com/highcharts.js", "http://code.highcharts.com/modules/exporting.js"));


        $this->di['js']->code_javascript("$(function () {
    $('#container').highcharts({
        chart: {
            type: 'area'
        },
        title: {
            text: 'Time to make the backup'
        },
        subtitle: {
            text: 'Backup, gzip and transfert'
        },
        xAxis: {
            categories: ['".implode("','", $dates)."'],
            tickmarkPlacement: 'on',
            title: {
                enabled: false
            }
        },
        yAxis: {
            title: {
                text: 'Time in seconds'
            },
            labels: {
                formatter: function () {
                    return this.value;
                }
            }
        },
        tooltip: {
            shared: true,
            valueSuffix: ' seconds'
        },
        plotOptions: {
            area: {
                stacking: 'normal',
                lineColor: '#000000',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    lineColor: '#000000'
                }
            }
        },
        series: [{
            name: 'Time to backup',
            data: [".implode(",", $time_backup)."]
        }, {
            name: 'Time to gzip',
            data: [".implode(",", $time_gz)."]
        }, {
            name: 'Time to scp',
            data: [".implode(",", $time_transfered)."]
        }]
    });
});");

        /* color: '#e2431e' },
          1: { color: '#e7711b' },
          2: { color: '#f1ca3a' },
          3: { color: '#6f9654' },
          4: { color: '#1c91c0' },
          5: { color: '#43459d' },
         */

        $this->di['js']->code_javascript("
            
Highcharts.setOptions({
    colors: ['#43459d', '#1c91c0', '#6f9654','#998d39', '#dd0e49', '#3a4091', '#dd0e49', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4']
});

$(function () {
    $('#container2').highcharts({
        chart: {
            type: 'area'
        },
        title: {
            text: 'Space took by the backup'
        },
        subtitle: {
            text: 'After backup, After Gzip, After transfered'
        },
        xAxis: {
            categories: ['".implode("','", $dates)."'],
            tickmarkPlacement: 'on',
            title: {
                enabled: false
            }
        },
        yAxis: {
            title: {
                text: 'in Giga octets'
            },
            labels: {
                formatter: function () {
                    return this.value/1024/1024;
                }
            }
        },
        tooltip: {
            shared: true,
            valueSuffix: ' KB'
        },
        plotOptions: {
            area: {
                stacking: 'normal',
                lineColor: '#000000',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    lineColor: '#000000'
                }
            }
        },
        series: [{
            name: 'Time to backup',
            data: [".implode(",", $size_file)."]
        }, {
            name: 'Time to gzip',
            data: [".implode(",", $size_gz)."]
        }, {
            name: 'Time to scp',
            data: [".implode(",", $size_transfered)."]
        }]
    });
});");
    }

    public function gant()
    {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->di['js']->addJavascript(array("jquery-latest.min.js", "jquery.colorbox-min.js", "timetable-script.min.js"));
    }

    public function deleteStorageArea($param)
    {
        $id_backup_storage_area = $param[0];
        $db                     = $this->di['db']->sql(DB_DEFAULT);
        $sql                    = "DELETE FROM  backup_storage_area WHERE id ='".$id_backup_storage_area."'";

        $db->sql_query($sql);
        header("location: ".LINK."backup/storageArea/");
    }


    function ssh2()
    {
        $ssh = new SSH2('10.0.51.117');

        if (!$ssh->login('root', 'zeb33tln')) {
            exit('Login Failed');
        }

        echo $ssh->exec('pwd');
        echo $ssh->exec('ls -la');
        echo $ssh->exec('whereis screen');
    }


    function check($param)
    {
        

        $cmd = array('grep','ls','sed','screen','whereis','rm','awk');
        $type_backup = array('mysqldump', 'xtrabackup', 'mydumper');


        $ssh = new SSH2('10.0.51.117');

        
    }
}
/*
     * echo "$(du -c /var/lib/mysql/mysql-bin.* | tail -1 | cut -f 1) $(df / | tail -1 | awk '{print $4}')" | awk '{printf "MySQL binlog consuming " "%3.2f Gigabytes of disk space:\n", $1 / 1024 / 1024; printf "%3.2f percent of total disk space\n", $1 / $2 *100}'
     */
