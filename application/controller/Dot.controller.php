<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Form\Upload;
use \Glial\Date\Date;

class Dot extends Controller {

    CONST COLOR_SUCCESS = "green";
    CONST COLOR_ERROR = "red";
    CONST COLOR_DELAY = "orange";
    CONST COLOR_SOPPED = "#5c5cb8";
    CONST COLOR_BLACKOUT = "#000000";
    CONST COLOR_BUG = "pink"; //this case should be never happen on Graph

    var $server_alone = array();
    var $groups = array();
    var $grouped = array();

    public function index() {
        $this->layout_name = 'default';

        $this->title = __("Error 404");
        $this->ariane = " > " . $this->title;

        //$this->javascript = array("");
    }

    public function run() {
        $this->view = false;
        $graph = new Alom\Graphviz\Digraph('G');
    }

    /*
     * The goal is this function is to split the graph isloated to produce different dot
     * like that we can provide a better display to dend user and hide the part that they don't need
     * 
     */

    public function splitGraph() {

        $this->view = false;

        $db = $this->di['db']->sql(DB_DEFAULT);
        $lists = $this->generateGroup();

        $graphs = [];
        foreach ($lists as $list) {

            $tmp = [];

            $tmp['graph'] = $this->generateGraph($list);
            $tmp['servers'] = $list;


            $graphs[] = $tmp;
        }

        //generate standalone server

        $server_alone = $this->getServerStandAlone();

        foreach ($server_alone as $server) {
            $tmp = [];
            $tmp['graph'] = $this->generateGraph(array($server));
            $tmp['servers'] = array($server);

            $graphs[] = $tmp;
        }


        //echo $graphs[0];
        //print_r($graphs);

        return $graphs;
    }

    public function checkMasterSlave() {
        
    }

    private function nodeMain($node_id, $display_name, $lines) {




        $node = '  ' . $node_id . ' [style="" penwidth="3" fontname="arial" label =<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white">';

        $node .= $this->nodeHead($display_name);
        foreach ($lines as $line) {
            $node .= $this->nodeLine($line);
        }

        $node .= "</table>> ];\n";

        return $node;
    }

    private function nodeLine($line) {
        $line = '<tr><td bgcolor="lightgrey" align="left">' . $line . '</td></tr>';
        return $line;
    }

    private function nodeHead($display_name) {
        $line = '<tr><td bgcolor="black" color="white" align="center"><font color="white">' . $display_name . '</font></td></tr>';
        return $line;
    }

    public function generateGraph($list_id) {


        //label=\"Step 2\";
        $graph = "digraph Migration_MariaDB {
rankdir=LR;
 graph [fontname = \"helvetica\"];
 node [fontname = \"helvetica\"];
 edge [fontname = \"helvetica\"];
 node [color=green shape=rect style=filled fontsize=8 fontname=\"arial\" ranksep=0 concentrate=true splines=true overlap=false];\n";

        $graph .= $this->generateNode($list_id);
        $graph .= $this->generateEdge($list_id);
        $graph .= '}';


        return $graph;
    }

    public function generateNode($list_id) {
        $db = $this->di['db']->sql(DB_DEFAULT);


        $id_mysql_servers = implode(',', $list_id);

        $sql = "SELECT * FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
                WHERE a.id IN (" . $id_mysql_servers . ");";

        $res = $db->sql_query($sql);


        $ret = "";
        while ($ob = $db->sql_fetch_object($res)) {



            $lines = ['IP : ' . $ob->ip, $ob->version, "Time zone : " . $ob->time_zone];


            $ret .= $this->getColorNode($ob);
            $ret .= $this->nodeMain($ob->id_mysql_server, $ob->name, $lines);
        }


        return $ret;
    }

    public function generateEdge($list_id) {
        $db = $this->di['db']->sql(DB_DEFAULT);
        $id_mysql_servers = implode(',', $list_id);

        $sql = "SELECT a.*, b.*, c.*, d.id as id_master, a.id as id_slave FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
            INNER JOIN mysql_replication_thread c ON c.id_mysql_replication_stats = b.id
            INNER JOIN mysql_server d ON d.ip = c.master_host AND d.port = a.port
                WHERE a.id IN (" . $id_mysql_servers . ") " . $this->getFilter();

        $res = $db->sql_query($sql);

        $label = "";


        $ret = "";
        while ($ob = $db->sql_fetch_object($res)) {

            $color_edge = $this->getColorEdge($ob);

            $ret .= " " . $ob->id_master . " -> " . $ob->id_slave
                    . " [ arrowsize=\"1.5\" penwidth=\"2\" fontname=\"arial\" fontsize=8 color =\""
                    . $color_edge . "\" label =\"" . $label . "\"  edgetarget=\"" . LINK . "mysql/thread/"
                    . str_replace('_', '-', $ob->name) . "/\" edgeURL=\"" . LINK . "mysql/thread/"
                    . str_replace('_', '-', $ob->name) . "/" . $ob->thread_name . "\"];\n";
        }


        return $ret;
    }

    public function getColorEdge($object) {

        $edge = [];

        if ($object->thread_io === "1" && $object->thread_sql === "1" && $object->time_behind === "0") {
            $edge['color'] = self::COLOR_SUCCESS;
        } elseif ($object->thread_io === "1" && $object->thread_sql === "1" && $object->time_behind !== "0") {
            $edge['color'] = self::COLOR_DELAY;
        } else if ($object->last_io_errno !== "0" && $object->last_sql_errno !== "0") {
            $edge['color'] = self::COLOR_ERROR;
        } else if ($object->thread_io == "0" && $object->thread_sql == "0") {
            $edge['color'] = self::COLOR_SOPPED;
        } else {
            $edge['color'] = "pink";
        }

        return $edge['color'];
    }

    public function getColorNode($object) {
        if ($object->is_available) {
            $color_node = "node [color = \"" . self::COLOR_SUCCESS . "\"];\n";
        } else {
            $color_node = "node [color = \"" . self::COLOR_ERROR . "\"];\n";
        }

        return $color_node;
    }

    public function generateGroup() {

        $this->view = false;

        $db = $this->di['db']->sql(DB_DEFAULT);

        //case of Master / Slave
        $sql = "SELECT a.*, b.*, c.*, d.id as id_master, a.id as id_slave FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
            INNER JOIN mysql_replication_thread c ON c.id_mysql_replication_stats = b.id
            INNER JOIN mysql_server d ON d.ip = c.master_host AND d.port = a.port WHERE 1 " . $this->getFilter();


        $res = $db->sql_query($sql);

        $groups = [];
        $seen = [];
        $grouped = [];
        $all = [];

        $id_group = 0;

        while ($ob = $db->sql_fetch_object($res)) {
            if (in_array($ob->id_master, $seen) || in_array($ob->id_slave, $seen)) {

                foreach ($groups as $key => $group) {
                    if (in_array($ob->id_master, $group)) {
                        $groups[$key][] = $ob->id_slave;
                        $grouped[] = $ob->id_slave;
                    }
                    if (in_array($ob->id_slave, $group)) {
                        $groups[$key][] = $ob->id_master;
                        $grouped[] = $ob->id_master;
                    }

                    //in case of duble edge
                    $groups[$key] = array_unique($groups[$key]);


                    break;
                }
            } else {
                $groups[$id_group][] = $ob->id_master;
                $groups[$id_group][] = $ob->id_slave;
                $grouped[] = $ob->id_master;
                $grouped[] = $ob->id_slave;
                $id_group++;
            }

            $seen[] = $ob->id_master;
            $seen[] = $ob->id_slave;

            $seen = array_unique($seen);
        }



        $this->grouped = array_unique($grouped);


        //case of Galera Cluster
        return $groups;
    }

    public function renderer() {


        $data['groups'] = $this->splitGraph();
        $data['svg'] = [];



        foreach ($data['groups'] as $dot) {
            
            $data['svg'][] = $this->dotToSvg($dot['graph']);
        }



        $this->set('data', $data);
        return $data['svg'];
    }

    public function dotToSvg($dot) {
        file_put_contents(TMP . "tmp.dot", $dot);

        $cmd = "dot " . TMP . "/tmp.dot -Tsvg -o " . TMP . "/image.svg";
        shell_exec($cmd);

        return file_get_contents(TMP . "/image.svg");
    }

    public function getServerStandAlone() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id from mysql_server a WHERE 1 " . $this->getFilter();
        $res = $db->sql_query($sql);

        $all = [];
        while ($ob = $db->sql_fetch_object($res)) {

            $all[] = $ob->id;
        }

        $this->server_alone = array_diff($all, $this->grouped);


        return $this->server_alone;
    }

    //to mutualize
    private function getFilter() {

        $where = "";

        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }

        if (!empty($environment)) {
            $where .= " AND a.id_environment = '" . $environment . "'";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client = '" . $client . "'";
        }

        return $where;
    }

    //each minutes ?
    public function generateCache() {
        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->view = false;
        $graphs = $this->splitGraph();


        $sql = "DELETE FROM link__architecture__mysql_server WHERE 1";
        $db->sql_query($sql);

        $sql = "DELETE FROM architecture WHERE 1";
        $db->sql_query($sql);


        foreach ($graphs as $graph) {
            $date = date('Y-m-d H:i:s');

            
            $svg = $this->dotToSvg($graph['graph']);

            $sql = "INSERT INTO architecture (`date`, `data`, `display`) VALUES ('" . $date . "','" . $graph['graph'] . "','" . $svg . "' )";

            $db->sql_query($sql);

            $sql = "SELECT max(id) as last FROM architecture";
            $res = $db->sql_query($sql);

            while ($ob = $db->sql_fetch_object($res)) {
                $id_architecture = $ob->last;
            }


            foreach ($graph['servers'] as $id_mysql_server) {
                /* $table =[];
                  $table['link__architecture__mysql_server']['id_architecture'] = $id_architecture;
                  $table['link__architecture__mysql_server']['id_mysql_server'] = $id_mysql_server;
                 */
                $sql = "INSERT INTO link__architecture__mysql_server (`id_architecture`, `id_mysql_server`) VALUES ('" . $id_architecture . "','" . $id_mysql_server . "')";
                $db->sql_query($sql);
            }
        }
    }

}
