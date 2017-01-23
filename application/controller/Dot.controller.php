<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Form\Upload;
use \Glial\Date\Date;
use \Glial\Cli\Color;

//add virtual_ip
// ha proxy

class Dot extends Controller
{
    CONST COLOR_SUCCESS          = "green";
    CONST COLOR_ERROR            = "red";
    CONST COLOR_DELAY            = "orange";
    CONST COLOR_DONOR            = "#90EE90"; //liht green
    CONST COLOR_DONOR_DESYNCED   = "yellow";
    CONST COLOR_ARROW_SST        = "#0000ff";
    CONST COLOR_STOPPED          = "#5c5cb8";
    CONST COLOR_BLACKOUT         = "#000000";
    CONST COLOR_NO_PRIMARY       = "red";
    CONST COLOR_BUG              = "pink"; //this case should be never happen on Graph
    CONST COLOR_MANUAL_DESYNC    = "cyan";
    CONST COLOR_CONNECTING       = "green";
    CONST COLOR_SPLIT_BRAIN      = "orange";
    CONST COLOR_NODE_RECEIVE_SST = "#000000";

    var $node    = array();
    var $segment = array();
    var $sst     = false;

    /*
     *
     * Contient les serveur suceptible de recevoir le SST
     * A ne pas pas prendre en compte dans el cache des cluster & master / slave
     */
    var $exclude = array();
    var $debug   = false;

    public function index()
    {
        $this->layout_name = 'default';
        $this->title       = __("Error 404");
        $this->ariane      = " > ".$this->title;
        //$this->javascript = array("");
    }

    public function run()
    {
        $this->view = false;
        $graph      = new Alom\Graphviz\Digraph('G');
    }
    /*
     * The goal is this function is to split the graph isloated to produce different dot
     * like that we can provide a better display to dend user and hide the part that they don't need
     * 
     */

    public function splitGraph()
    {

        $this->view = false;

        $db  = $this->di['db']->sql(DB_DEFAULT);
        $ret = $this->generateGroup();

        $graphs = [];
        foreach ($ret['groups'] as $list) {


            $tmp = [];

            $tmp['graph']   = $this->generateGraph($list);
            $tmp['servers'] = $list;

            $graphs[] = $tmp;

            if ($this->debug) {
                echo str_repeat("#", 79)."\n";
                echo "SERVER List : ".implode(",", $list)."\n";
                echo str_repeat("#", 79)."\n";
            }
        }

        //generate standalone server

        $server_alone = $this->getServerStandAlone($ret['grouped']);

        foreach ($server_alone as $server) {
            $tmp            = [];
            $tmp['graph']   = $this->generateAlone(array($server));
            $tmp['servers'] = array($server);

            $graphs[] = $tmp;
        }

        //echo $graphs[0];
        //print_r($graphs);

        return $graphs;
    }

    public function checkMasterSlave()
    {
        
    }

    private function nodeMain($node_id, $display_name, $lines, $databases = "")
    {


        $node = '  '.$node_id.' [style="" penwidth="3" fontname="arial" label =<<table border="0" cellborder="0" cellspacing="0" cellpadding="2" bgcolor="white">';

        $node .= $this->nodeHead($display_name);
        foreach ($lines as $line) {
            $node .= $this->nodeLine($line);
        }

        if (!empty($databases)) {
            $node .= '<tr><td bgcolor="lightgrey"><table border="0" cellborder="0" cellspacing="0" cellpadding="2">';
            foreach ($databases as $database) {
                $node .= '<tr>'
                    .'<td bgcolor="darkgrey" color="white" align="left">'.$database['name'].'</td>'
                    .'<td bgcolor="darkgrey" color="white" align="right">'.$database['tables'].'</td>'
                    .'<td bgcolor="darkgrey" color="white" align="right">'.$database['rows'].'</td>'
                    .'</tr>';
            }
            $node .= '</table></td></tr>';
        }

        $node .= "</table>> ];\n";

        return $node;
    }

    private function nodeLine($line)
    {
        $line = '<tr><td bgcolor="lightgrey" align="left">'.$line.'</td></tr>';
        return $line;
    }

    private function nodeHead($display_name)
    {
        $line = '<tr><td bgcolor="black" color="white" align="center"><font color="white">'.$display_name.'</font></td></tr>';
        return $line;
    }

    public function generateAlone($list_id)
    {
        //label=\"Step 2\";
        $graph = "digraph PmaControl {
rankdir=LR;
 graph [fontname = \"helvetica\"];
 node [fontname = \"helvetica\"];
 edge [fontname = \"helvetica\"];
 node [shape=rect style=filled fontsize=8 fontname=\"arial\" ranksep=0 concentrate=true splines=true overlap=false];\n";


        $graph .= $this->generateNode($list_id);
        $graph .= '}';

        return $graph;
    }

    public function generateGraph($list_id)
    {
        //label=\"Step 2\";
        $graph = "digraph PmaControl {
rankdir=LR; splines=ortho;
 graph [fontname = \"helvetica\"];
 node [fontname = \"helvetica\"];
 edge [fontname = \"helvetica\"];
 node [shape=rect style=filled fontsize=8 fontname=\"arial\" ranksep=0 concentrate=true splines=true overlap=false];\n";




        $gg2 = $this->groupEdgeSegment($list_id);
        $gg  = $this->generateCluster($list_id);
        $graph .= $this->generateNode($list_id);

        //$gg2 = $this->generateMerge($list_id);
        $graph .= $this->generateEdge($list_id);

        if ($this->sst) {
            $graph .= $this->generateEdgeSst();
        }

        $graph .= $gg;
        $graph .= $gg2;


        $graph .= '}';

        /*
          if (!empty($gg2)) {
          echo $graph;
          } */


        return $graph;
    }

    public function generateNode($list_id)
    {
        $db               = $this->di['db']->sql(DB_DEFAULT);
        $id_mysql_servers = implode(',', $list_id);

        $sql = "SELECT *,b.id as id_db FROM mysql_server a
            INNER JOIN mysql_database b ON b.id_mysql_server = a.id
                WHERE a.id IN (".$id_mysql_servers.");";

        $res2 = $db->sql_query($sql);

        $databases = [];
        while ($ob        = $db->sql_fetch_object($res2)) {

            $databases[$ob->id_mysql_server][$ob->id_db]['name']   = $ob->name;
            $databases[$ob->id_mysql_server][$ob->id_db]['tables'] = $ob->tables;
            $databases[$ob->id_mysql_server][$ob->id_db]['rows']   = number_format($ob->rows, 0, '.', ' ');
            $databases[$ob->id_mysql_server][$ob->id_db]['size']   = $ob->data_length + $ob->data_free + $ob->index_length;
        }

        $sql = "SELECT * FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
                WHERE a.id IN (".$id_mysql_servers.");";

        $res3 = $db->sql_query($sql);

        $ret = "";

        while ($ob = $db->sql_fetch_object($res3)) {
            $lines = ['IP : '.$ob->ip, $ob->version, "Time zone : ".$ob->time_zone, "Binlog format : ".$ob->binlog_format];

            $tmp_db = "";



            if (!empty($databases[$ob->id_mysql_server]) && count($databases[$ob->id_mysql_server]) > 0) {
                $tmp_db = $databases[$ob->id_mysql_server];
            }

            $ret .= $this->getColorNode($ob);
            $ret .= $this->nodeMain($ob->id_mysql_server, $ob->name, $lines, $tmp_db);
        }


        return $ret;
    }

    public function generateEdge($list_id)
    {
        $db               = $this->di['db']->sql(DB_DEFAULT);
        $id_mysql_servers = implode(',', $list_id);

        $sql = "SELECT a.*, b.*, c.*, d.id as id_master, a.id as id_slave FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
            INNER JOIN mysql_replication_thread c ON c.id_mysql_replication_stats = b.id
            INNER JOIN mysql_server d ON d.ip = c.master_host AND d.port = a.port
                WHERE a.id IN (".$id_mysql_servers.") ".$this->getFilter();

        if ($this->debug) {
            //echo SqlFormatter::format($sql);
        }

        $res = $db->sql_query($sql);

        $ret = "";

        while ($ob = $db->sql_fetch_object($res)) {

            if (empty($this->exclude[$ob->id_mysql_server])) {
                $ret .= $this->getColorEdge($ob);
            }
        }

        return $ret;
    }

    public function getColorEdge($ob)
    {
        $edge  = [];
        $label = "";
        $style = "filled";

        if ($ob->thread_io === "Yes" && $ob->thread_sql === "Yes" && $ob->time_behind === "0") {
            $edge['color'] = self::COLOR_SUCCESS;
        } elseif ($ob->thread_io === "Yes" && $ob->thread_sql === "Yes" && $ob->time_behind !== "0") {
            $edge['color'] = self::COLOR_DELAY;
            $label         = $ob->time_behind." sec";
        } else if (($ob->last_io_errno !== "0" || $ob->last_sql_errno !== "0") && ( $ob->thread_io == "Yes" || $ob->thread_sql == "Yes")) {
            $edge['color'] = self::COLOR_ERROR;
        } else if ($ob->last_io_errno !== "0" || $ob->last_sql_errno !== "0" && $ob->thread_io == "No" && $ob->thread_sql == "No") {
            $edge['color'] = self::COLOR_BLACKOUT;
        } else if ($ob->thread_io == "0" && $ob->thread_sql == "0") {
            $edge['color'] = self::COLOR_STOPPED;
        } else if ($ob->thread_io == "0" && $ob->thread_sql == "Connecting") { // replace int by sth else
            $edge['color'] = self::COLOR_CONNECTING;
            $style         = "dotted";
        } else {
            $edge['color'] = "pink";
        }

        return " ".$ob->id_master." -> ".$ob->id_slave
            ." [ arrowsize=\"1.5\" style=".$style.",penwidth=\"2\" fontname=\"arial\" fontsize=8 color =\""
            .$edge['color']."\" label=\"".$label."\"  edgetarget=\"".LINK."mysql/thread/"
            .str_replace('_', '-', $ob->name)."/\" edgeURL=\"".LINK."mysql/thread/"
            .str_replace('_', '-', $ob->name)."/".$ob->thread_name."\"];\n";
        ;
    }

    public function generateEdgeSst()
    {

        $ret = "";
        foreach ($this->exclude as $key => $value) {
            $label = "SST";

            $ret .= " ".$value." -> ".$key
                ." [ arrowsize=\"1.5\" ,penwidth=\"2\" fontname=\"arial\" fontsize=8 color =\""
                .self::COLOR_ARROW_SST."\" label=\"".$label." 67%\"];\n";
        }

        $this->sst = false;

        return $ret;
    }

    public function getColorNode($object)
    {

        //COLOR_DONOR

        if ($object->is_available) {

            if (!empty($this->node[$object->id_mysql_server])) {
                $color_node = $this->node[$object->id_mysql_server];
            } else {
                $color_node = self::COLOR_SUCCESS;
            }
        } else {
            $color_node = self::COLOR_ERROR;
        }

        if (!empty($this->exclude[$object->id_mysql_server])) {
            $color_node = self::COLOR_NODE_RECEIVE_SST;
        }

        return "node [color = \"".$color_node."\"];\n";
    }

    public function generateGroup()
    {
        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);

        //case of Master / Slave
        $sql = "SELECT a.*, b.*, c.*, d.id as id_master, a.id as id_slave FROM mysql_server a
            INNER JOIN mysql_replication_stats b ON b.id_mysql_server = a.id
            INNER JOIN mysql_replication_thread c ON c.id_mysql_replication_stats = b.id
            INNER JOIN mysql_server d ON d.ip = c.master_host AND d.port = a.port WHERE 1 ".$this->getFilter();

        if ($this->debug) {
            debug($sql);
        }

        $res = $db->sql_query($sql);

        $id_group = 0;

        $tmp_group = [];
        while ($ob        = $db->sql_fetch_object($res)) {

            $tmp_group[$id_group][] = $ob->id_master;
            $tmp_group[$id_group][] = $ob->id_slave;
            $id_group++;
        }
        $master_slave = $tmp_group;

        //case of Galera Cluster
        $sql = "SELECT * FROM galera_cluster_node";
        $ret = "";

        $res = $db->sql_query($sql);

        $tmp_group = [];
        $tmp_name  = "";

        while ($ob = $db->sql_fetch_object($res)) {

            $tmp_group[$ob->id_galera_cluster_main][] = $ob->id_mysql_server;
            $grouped[]                                = $ob->id_mysql_server;
        }

        $galera = $tmp_group;


        // cas des SST (regrouper les serveurs en cours de transfert avec le cluster auquel il est censé être rataché)
        $sst = [];
        $sql = "SELECT * FROM galera_cluster_node b WHERE b.comment = 'Donor/Desynced'";
        $res = $db->sql_query($sql);
        while ($ob  = $db->sql_fetch_object($res)) {

            $id_mysql_server = $this->getDestinationSst($ob);

            $sst[$ob->id_mysql_server] = $id_mysql_server;
        }

        // cas des segments (cluster lier : un galera cluster de 3 noeuds sur 2 continents)
        $sql = "SELECT group_concat(b.id_mysql_server) as id_mysql_server
            FROM `galera_cluster_main` a
            INNER JOIN galera_cluster_node b ON a.id = b.id_galera_cluster_main
            GROUP BY a.name";


        $tmp_group = [];
        $res       = $db->sql_query($sql);
        while ($ob        = $db->sql_fetch_object($res)) {

            $tmp = explode(',', $ob->id_mysql_server);


            //ratachement des serveur en cours de SST
            foreach ($sst as $key => $value) {
                if (in_array($key, $tmp)) {
                    $tmp[] = $value;
                }
            }

            $tmp_group[] = $tmp;
        }
        $segments = $tmp_group;

        //cas des serveurs avec plusieurs instances
        $sql = "SELECT group_concat(id) as allid from mysql_server GROUP BY ip having count(1) > 1;";
        $res = $db->sql_query($sql);

        $tmp_group = [];
        while ($ob        = $db->sql_fetch_object($res)) {

            $tmp_group[] = explode(',', $ob->allid);
        }
        $instance = $tmp_group;

        $groups = $this->array_merge_group(array_merge($galera, $instance, $master_slave, $segments));

        $result['groups']  = $groups;
        $result['grouped'] = $this->array_values_recursive($result['groups']);

        debug($result);

        return $result;
    }

    public function renderer()
    {
        $data['groups'] = $this->splitGraph();
        $data['svg']    = [];

        foreach ($data['groups'] as $dot) {
            $data['svg'][] = $this->dotToSvg($dot['graph']);
        }

        $this->set('data', $data);
        return $data['svg'];
    }

    public function dotToSvg($dot)
    {
        file_put_contents(TMP."tmp.dot", $dot);

        $cmd = "dot ".TMP."/tmp.dot -Tsvg -o ".TMP."/image.svg";
        shell_exec($cmd);

        return file_get_contents(TMP."/image.svg");
    }

    public function getServerStandAlone($grouped)
    {
        $this->view = false;
        $db         = $this->di['db']->sql(DB_DEFAULT);

        $sql = "SELECT id from mysql_server a WHERE 1 ".$this->getFilter();
        $res = $db->sql_query($sql);
        $all = [];
        while ($ob  = $db->sql_fetch_object($res)) {

            $all[] = $ob->id;
        }

        $this->server_alone = array_diff($all, $grouped);
        return $this->server_alone;
    }

    //to mutualize
    //considere mysql_server a
    private function getFilter()
    {

        $where = "";

        if (!empty($_GET['environment']['libelle'])) {
            $environment = $_GET['environment']['libelle'];
        }
        if (!empty($_SESSION['environment']['libelle']) && empty($_GET['environment']['libelle'])) {
            $environment                    = $_SESSION['environment']['libelle'];
            $_GET['environment']['libelle'] = $environment;
        }

        if (!empty($_SESSION['client']['libelle'])) {
            $client = $_SESSION['client']['libelle'];
        }
        if (!empty($_GET['client']['libelle']) && empty($_GET['client']['libelle'])) {
            $client                    = $_GET['client']['libelle'];
            $_GET['client']['libelle'] = $client;
        }

        if (!empty($environment)) {
            $where .= " AND a.id_environment = '".$environment."'";
        }

        if (!empty($client)) {
            $where .= " AND a.id_client = '".$client."'";
        }

        if (!empty($this->exclude)) {
            $tab = array_flip($this->exclude);
            $where .= " AND a.id NOT IN  (".implode(',', $tab).")";
        }

        return $where;
    }

    //each minutes ?
    public function generateCache($param)
    {
        if (!empty($param)) {
            foreach ($param as $elem) {
                if ($elem == "--debug") {
                    $this->debug = true;
                    echo Color::getColoredString("DEBUG activated !", "yellow")."\n";
                }
            }
        }

        $db = $this->di['db']->sql(DB_DEFAULT);

        $this->view = false;
        $graphs     = $this->splitGraph();


        $sql = "BEGIN";
        $db->sql_query($sql);

        $sql = "DELETE FROM link__architecture__mysql_server WHERE 1";
        $db->sql_query($sql);

        $sql = "DELETE FROM architecture WHERE 1";
        $db->sql_query($sql);


        //@TODO : we parse more graph that we should to do
        //echo "Nombre de graphs : ".count($graphs)."\n";

        foreach ($graphs as $graph) {
            $date = date('Y-m-d H:i:s');


            $svg = $this->dotToSvg($graph['graph']);


            preg_match_all("/width=\"([0-9]+)pt\"\sheight=\"([0-9]+)pt\"/", $svg, $output);


            $sql = "INSERT INTO architecture (`date`, `data`, `display`,`height`,`width`)
            VALUES ('".$date."','".$db->sql_real_escape_string($graph['graph'])."','".$db->sql_real_escape_string($svg)."',"
                .$output[2][0]." ,".$output[1][0].")";

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
                $sql = "INSERT INTO link__architecture__mysql_server (`id_architecture`, `id_mysql_server`) VALUES ('".$id_architecture."','".$id_mysql_server."')";
                $db->sql_query($sql);
            }
        }

        $sql = "COMMIT";
        $db->sql_query($sql);

        if ($this->debug) {
            debug($this->node);
            debug($this->segment);
        }
    }

    public function generateCluster($list_id)
    {
        $db               = $this->di['db']->sql(DB_DEFAULT);
        $this->view       = false;
        $id_mysql_servers = implode(',', $list_id);

        $sql = "SELECT *,a.id as id_galera_cluster_main FROM galera_cluster_main a
             INNER JOIN galera_cluster_node b ON a.id = b.id_galera_cluster_main
             WHERE b.id_mysql_server IN (".$id_mysql_servers.") ORDER BY NAME;";


        if ($this->debug) {
            //echo SqlFormatter::format($sql);
        }

        $ret = "";
        //echo $sql."\n";

        $res           = $db->sql_query($sql);
        $galera_name[] = array();

        $color_cluster = self::COLOR_SUCCESS;


        $current_cluster    = "";
        $super_cluster_open = false;

        while ($ob = $db->sql_fetch_object($res)) {

            if ($ob->comment === "Donor/Desynced" && $ob->desync === "OFF") {
                $this->getDestinationSst($ob);
            }

            $display_name = $ob->name;
            if (in_array($ob->name, $galera_name)) {
                $display_name = $ob->name."()";
            }

            $name = $ob->name;

            if (!empty($this->segment[$ob->name][$ob->id_galera_cluster_main])) {
                $name = $ob->name." (".$this->segment[$ob->name][$ob->id_galera_cluster_main]['segment'].")";

                if ($current_cluster != $ob->name) {

                    if ($super_cluster_open) {
                        $ret .= ' }'."\n";
                    }

                    $ret .='penwidth=1.5; color=gray;';

                    $ret .= 'subgraph cluster_'.str_replace('-', '', $ob->name).' {';
                    $ret .= 'rankdir="LR";';
                    $ret .= 'label = "Galera : '.$ob->name.'";';

                    $super_cluster_open = true;
                }
            } else {
                $super_cluster_open = false;
            }


            if ($super_cluster_open) {
                $ret .= 'color='.$color_cluster.';style=dashed;fontname="arial";';
            }
            $ret .= 'subgraph cluster_'.str_replace('-', '', $ob->name).''.$ob->segment.' {';
            //$ret .= 'rankdir="LR";';
            $ret .= 'label = "Segment : '.$this->segment[$ob->name][$ob->id_galera_cluster_main]['segment'].'";';

            $ret .= ''.$ob->id_mysql_server.';';


            // nodeA -> nodeB [style=invis]
            // outputorder="edgesfirst"
            //debug($ob->id_mysql_server);
            //debug($this->exclude);

            if (in_array($ob->id_mysql_server, $this->exclude)) {
                $tab = array_flip($this->exclude);



                $ret .= ''.$tab[$ob->id_mysql_server].';';
                $this->sst = true;

                echo "xfhxfgxfh ".$tab[$ob->id_mysql_server];
            }


            $ret .= ' }'."\n";

            $this->node[$ob->id_mysql_server] = $this->getColorGalera($ob);
        }


        if ($super_cluster_open) {
            $ret .= ' }'."\n";
        }

        return $ret;
    }

    private function array_values_recursive($ary)
    {
        $lst = array();
        foreach (array_keys($ary) as $k) {
            $v = $ary[$k];
            if (is_scalar($v)) {
                $lst[] = $v;
            } elseif (is_array($v)) {
                $lst = array_merge($lst, $this->array_values_recursive($v)
                );
            }
        }
        return $lst;
    }
    /*
     * merge des groups de value, pour faire des regroupement avec les groupes qui ont les même id et retire les doublons
     */

    private function array_merge_group($array)
    {
        $all_values  = $this->array_values_recursive($array);
        $group_merge = [];
        foreach ($all_values as $value) {
            $tmp = [];
            foreach ($array as $key => $sub_group) {
                if (in_array($value, $sub_group)) {
                    $tmp = array_merge($sub_group, $tmp);
                    unset($array[$key]);
                }
            }
            $array[] = array_unique($tmp);
        }
        //@TODO : Improvement because we parse all_value and we delete all array from orgin no need to continue;
        return $array;
    }

    private function generateMerge($list_id)
    {
        $db         = $this->di['db']->sql(DB_DEFAULT);
        $this->view = false;

        $id_mysql_servers = implode(',', $list_id);

        $sql = "SELECT group_concat(id) as id_all,ip,id FROM mysql_server a
             WHERE a.id IN (".$id_mysql_servers.") ".$this->getFilter()." GROUP BY ip having count(1)>1;";

        $ret = "";


        //echo  $sql."\n";

        $res = $db->sql_query($sql);

        while ($ob = $db->sql_fetch_object($res)) {
            $ret .= 'subgraph cluster_'.$ob->id.' {';
            $ret .= 'rankdir="LR";';
            $ret .= 'color=blue;fontname="arial";';
            $ret .= 'label = "IP : '.$ob->ip.'";';

            $ids = explode(",", $ob->id_all);
            foreach ($ids as $id) {
                $ret .= ''.$id.';';
            }
            $ret .= ' }'."\n";
        }

        return $ret;
    }

    public function getColorGalera($ob)
    {

        //COLOR_DONOR
        if ($ob->comment === "Synced") {
            $color_node = self::COLOR_SUCCESS;
        } else if ($ob->desync == "ON") {
            $color_node = self::COLOR_MANUAL_DESYNC;
        } else if ($ob->comment == "Donor/Desynced" && stristr($ob->comment, "xtrabackup")) {
            $color_node = self::COLOR_DONOR;
        } else if ($ob->comment == "Donor/Desynced" && stristr($ob->comment, "xtrabackup") === false) {
            $color_node = self::COLOR_DONOR_DESYNCED;
        } else if ($ob->comment == "Joined") {
            $color_node = self::COLOR_ERROR;
        } else if ($ob->comment == "Joining") {
            $color_node = self::COLOR_ERROR;
        } else if ($ob->cluster_status == "non-Primary") {
            $color_node = self::COLOR_SPLIT_BRAIN;
        } else {
            $color_node = self::COLOR_BUG;
        }

        return $color_node;
    }

    public function getDestinationSst($ob)
    {


        $db = $this->di['db']->sql(DB_DEFAULT);

        $addrs = explode(",", $ob->incoming_addresses);

        $couple = [];
        foreach ($addrs as $addr) {
            $part     = explode(":", $addr);
            $couple[] = "SELECT * FROM mysql_server WHERE `ip`='".$part[0]."' AND port='".$part[1]."' and error != ''";
        }


        $sql = "(".implode(") UNION (", $couple).");";

        //echo SqlFormatter::format($sql);

        $res = $db->sql_query($sql);


        $i   = 0;
        while ($ob2 = $db->sql_fetch_object($res)) {
            $this->exclude[$ob2->id] = $ob->id_mysql_server;
            $id_mysql_server         = $ob2->id;
            $i++;
        }

        if ($i > 1) {
            throw new \Exception("Warning : SST more than 1 candidate", 80);
        }

        return $id_mysql_server;
    }

    public function groupEdgeSegment($list_id)
    {
        $ret              = '';
        $id_mysql_servers = implode(',', $list_id);

        $db  = $this->di['db']->sql(DB_DEFAULT);
        $sql = "SELECT a.name,a.segment as segment, a.id as id,group_concat(b.id_mysql_server) as id_mysql_server
            FROM `galera_cluster_main` a
            INNER JOIN galera_cluster_node b ON b.id_galera_cluster_main = a.id
            WHERE a.name in(SELECT name FROM `galera_cluster_main` GROUP BY name having count(1) > 1)
            AND b.id_mysql_server IN (".$id_mysql_servers.")
            GROUP BY a.name,a.segment";

        if ($this->debug) {

            //echo SqlFormatter::format($sql);
        }

        $res = $db->sql_query($sql);


        $val = array();
        while ($ob  = $db->sql_fetch_object($res)) {

            $this->segment[$ob->name][$ob->id]['segment']         = $ob->segment;
            $this->segment[$ob->name][$ob->id]['id_mysql_server'] = $ob->id_mysql_server;

            $median_id = $this->median(explode(",", $ob->id_mysql_server));

            $val[]                    = $median_id;
            $cluster_name[$median_id] = 'cluster_'.str_replace('-', '', $ob->name).$ob->segment;
        }

        $nb_segments = count($val);

        for ($pn = 1; $pn < $nb_segments; $pn++) {
            for ($on = $pn + 1; $on <= $nb_segments; $on++) {
                //printf("%u link %u\n", $pn, $on);

                $contrainte = '';



                $gg = $val[$pn - 1].' -> '.$val[$on - 1]." [arrowsize=0, color=green, penwidth=0, ".$contrainte." dir=both,ltail="
                    .$cluster_name[$val[$pn - 1]].",lhead=".$cluster_name[$val[$on - 1]]."];\n";

                //debug($gg);

                $ret .= $gg;

                /* */
                //$ret .= $cluster_name[$val[$on - 1]].' -> '.$cluster_name[$val[$pn - 1]]." [arrowsize=\"1.5\", penwidth=\"2\", dir=both]\n";
            }
        }


        return $ret;
    }

    public function median($arr)
    {
        //Sort Array Numerically, because Median calculation has to have a set in order
        sort($arr, SORT_NUMERIC);

        //Get Total Amount Of Elements
        $count = count($arr);

        //Need to get the mid point of array.  Use Floor because we always want to round down.
        $mid = floor($count / 2);
        return $arr[$mid];
    }
}
/*
     *
     * $array = array('Alpha', 'Beta', 'Gamma', 'Sigma');

      function depth_picker($arr, $temp_string, &$collect) {
      if ($temp_string != "")
      $collect []= $temp_string;

      for ($i=0; $i<sizeof($arr);$i++) {
      $arrcopy = $arr;
      $elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
      if (sizeof($arrcopy) > 0) {
      depth_picker($arrcopy, $temp_string ." " . $elem[0], $collect);
      } else {
      $collect []= $temp_string. " " . $elem[0];
      }
      }
      }

      $collect = array();
      depth_picker($array, "", $collect);
      print_r($collect);
     */