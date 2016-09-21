<?php

use \Glial\Synapse\Controller;

class Migration extends Controller
{
    var $database = "test21";
    var $dump     = "/data/client/humanis/structure.sql";
    //var $dump = "/data/client/humanis/sql_workbench/migration_script.sql";

    var $path           = "/data/client/humanis/lot2/";
    var $exclude_tables = array("cache", "cache_block", "cache_bootstrap", "cache_eck", "cache_entity_embed", "cache_features", "cache_field",
        "cache_filter", "cache_form", "cache_geocoder", "cache_image", "cache_l10n_update", "cache_libraries", "cache_menu", "cache_menu",
        "cache_metatag", "cache_page", "cache_panels", "cache_path", "cache_rules", "cache_search_api_solr", "cache_token",
        "cache_update", "cache_variable", "cache_views", "cache_views_data");

    public function export()
    {
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;

        $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'autonomie'";

        $res = $db->sql_query($sql);
        while ($ob  = $db->sql_fetch_object($res)) {
            echo 'bcp Drupal_ACS.dbo.'.$ob->table_name.' out '.$ob->table_name.'.txt -n -SDBSQL-DRUPAL-DEV\PRGCL_DEV -T -C -t"||" -r"++" -c'."\n";
        }
    }

    public function importData()
    {
        $path = $this->path."*.txt";

        $db         = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;

        $files = glob($path);

        foreach ($files as $file) {
            //echo $file."\n";

            $table_name = pathinfo($file)['filename'];


            if (in_array($table_name, $this->exclude_tables)) {
                continue;
            }

            // sql sever nous fourni du latin 1 xD
            //$sql = 'LOAD DATA INFILE "'.$file.'" INTO TABLE `'.$this->database.'`.`'.$table_name.'` COLUMNS TERMINATED BY "||" LINES TERMINATED BY "++";';
            $sql = 'LOAD DATA INFILE "'.$file.'" INTO TABLE `'.$this->database.'`.`'.$table_name.'` CHARACTER SET latin1 COLUMNS TERMINATED BY "||" LINES TERMINATED BY "++";';

            echo $sql."\n";
            $db->sql_query($sql);
            //echo $table_name ."\n";
            //exit;
        }
    }

    public function unHex()
    {

        $db         = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;

        $sql = 'SELECT a.TABLE_NAME,b.COLUMN_NAME,b.data_type,a.TABLE_ROWS
 FROM information_schema.tables a
 INNER JOIN information_schema.COLUMNS b ON a.TABLE_SCHEMA = b.TABLE_SCHEMA AND a.table_name = b.table_name
 WHERE a.table_schema = "'.$this->database.'"
 AND b.data_type LIKE "%blob%";';

        $res = $db->sql_query($sql);

        $total             = 0;
        $number_to_convert = 0;
        $zero              = 0;

        while ($ob = $db->sql_fetch_object($res)) {
            $sql  = "SELECT `".$ob->COLUMN_NAME."` FROM `".$this->database."`.`".$ob->TABLE_NAME."` ";
            $res2 = $db->sql_query($sql);

            $is_hex = false;
            $i      = 0;

            while ($ob2 = $db->sql_fetch_object($res2)) {
                $i++;

                //to prevent empty line


                if (empty($ob2->{$ob->COLUMN_NAME})) {
                    $zero++;
                    continue;
                }

                if (strstr($ob->COLUMN_NAME,'etab_geolocalisation'))
                {
                    continue;
                }


                if (ctype_xdigit($ob2->{$ob->COLUMN_NAME})) {
                    $is_hex = true;
                } else {
                    $is_hex = false;

                    echo "ERROR : ".$ob->TABLE_NAME." ".$ob->COLUMN_NAME."\n";
                    break;
                }
            }

            if ($is_hex) {
                $sql = "UPDATE `".$this->database."`.`".$ob->TABLE_NAME."` SET `".$ob->COLUMN_NAME."`=UNHEX(`".$ob->COLUMN_NAME."`);";

                echo $sql."\n";
                $db->sql_query($sql);

                $number_to_convert++;
            }

            $total++;
        }

        echo "Converted (".$number_to_convert."/".$total.")  zero : ".$zero."\n";
    }

    public function crateDb()
    {
        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "CREATE DATABASE `".$this->database."` CHARACTER SET UTF8;";
        $db->sql_query($sql);
    }

    public function all()
    {

        $this->crateDb();
        $this->importStruct();
        $this->importData();
        $this->unHex();
        $this->patch();
    }

    public function importStruct()
    {
        echo "Loading structure...\n";
        shell_exec("mysql -u root -pMarneuse127* ".$this->database." < ".$this->dump);
    }

    public function convertionUt8()
    {
        
    }

    public function checkHex()
    {
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;

        $sql  = 'SELECT a.TABLE_NAME,b.COLUMN_NAME,b.data_type,a.TABLE_ROWS
 FROM information_schema.tables a
 INNER JOIN information_schema.COLUMNS b ON a.TABLE_SCHEMA = b.TABLE_SCHEMA AND a.table_name = b.table_name
 WHERE a.table_schema = "'.$this->database.'"
 AND b.data_type LIKE "%blob%";';
        $res2 = $db->sql_query($sql);

        $is_hex = false;
        $i      = 0;

        //field_data_field_etab_geolocalisation field_revision_field_etab_geolocalisation
        //load_functions
        //to_arg_functions
        //session

        while ($ob = $db->sql_fetch_object($res2)) {

            if (in_array($ob->TABLE_NAME, $this->exclude_tables)) {
                continue;
            }

            echo "SELECT `".$ob->COLUMN_NAME."` from `".$this->database."`.`".$ob->TABLE_NAME."`;"."\n";
        }
    }

    function patch()
    {

        $db         = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;

        $sqls   = [];
        $sqls[] = 'UPDATE `'.$this->database.'`.menu_router SET load_functions=UNHEX(load_functions),to_arg_functions=UNHEX(to_arg_functions);';
        $sqls[] = 'UPDATE `'.$this->database.'`.sessions SET session=UNHEX(session);';

        foreach ($sqls as $sql) {
             $db->sql_query($sql);
        }

        // UPDATE sessions SET session=UNHEX(session);
    }
}
/*
     *
     * generate insert into :
     * select concat('INSERT INTO auto_test1.`',TABLE_NAME,'` SELECT * FROM `autonomie_mig`.`',TABLE_NAME,'`;') FROM information_schema.tables WHERE table_schema = 'autonomie_mig';
     *
     * 
     * unhex :
      SELECT CONCAT("UPDATE autonomie_mig.",a.TABLE_NAME," SET ",b.COLUMN_NAME,'=UNHEX(',COLUMN_NAME,');') AS gg
      FROM information_schema.tables a
      INNER JOIN information_schema.COLUMNS b ON a.TABLE_SCHEMA = b.TABLE_SCHEMA AND a.table_name = b.table_name
      WHERE a.TABLE_ROWS != 0
      AND a.table_schema = "autonomie_mig"
      AND b.data_type LIKE '%blob%';
     */