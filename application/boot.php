<?php

/**
 * Basic Gliale functionality.
 *
 * Handles loading of core files needed on every request
 *
 * PHP versions 5.5
 *
 * GLIALE(tm) : Rapid Development Framework (http://gliale.com)
 * Copyright 2008-2012, Esysteme Software Foundation, Inc. (http://www.esysteme.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2007-2010, Esysteme Software Foundation, Inc. (http://www.esysteme.com)
 * @link          http://www.glial.com GLIALE(tm) Project
 * @package       gliale
 * @subpackage    gliale.app.webroot
 * @since         Gliale(tm) v 0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
header("Charset: UTF-8");

ini_set('error_log', TMP . 'log' . DS . 'error_php.log');
ini_set('APACHE_LOG_DIR', TMP . 'log' . DS);

//tput cols tells you the number of columns.
//tput lines tells you the number of rows.

use \Glial\Synapse\Config;
use \Glial\Debug\Debug;
use \Glial\Synapse\FactoryController;
use \Glial\Tools\ArrayTools;
use \Glial\I18n\I18n;
use \Glial\Acl\Acl;
use \Glial\Auth\Auth;
use \Glial\Sgbd\Sgbd;
use \Glial\Synapse\Javascript;

require ROOT . DS . 'vendor/autoload.php';

session_start();



$config = new Config;
$config->load(CONFIG);
FactoryController::addDi("config", $config);

if (ENVIRONEMENT) {
    $_DEBUG = new Debug;
    $_DEBUG->save("Starting...");
}





spl_autoload_register(function($className) {

    //echo LIBRARY . str_replace('\\', DIRECTORY_SEPARATOR, ltrim($className, '\\')) . '.php';
    if (file_exists(LIBRARY . str_replace('\\', DIRECTORY_SEPARATOR, ltrim($className, '\\')) . '.php')) {
        require(LIBRARY . str_replace('\\', DIRECTORY_SEPARATOR, ltrim($className, '\\')) . '.php');
    } else {
        return;
        //debug(debug_backtrace());
        require(APP_DIR . DS . "controller" . DS . $className . '.controller.php');
    }
});


//$_POST = ArrayTools::array_map_recursive("htmlentities", $_POST);


require __DIR__ . "/basic.php";

//debug($_GET);

(ENVIRONEMENT) ? $_DEBUG->save("Loading class") : "";

$db = $config->get("db");
$_DB = new Sgbd($db);

FactoryController::addDi("db", $_DB);

(ENVIRONEMENT) ? $_DEBUG->save("Init database") : "";


if (!IS_CLI) {
    include __DIR__ . DS . 'router.php';

    $route = new router();
    $route->parse($_GET['glial_path']);
    $url = $route->get_routes();

    if (isset($_GET['lg'])) {
        $_SESSION['language'] = $_GET['lg'];
        //SetCookie("language", $_GET['lg'], time() + 60 * 60 * 24 * 365, "/", $_SERVER['SERVER_NAME'], false, true);
        //SetCookie("language", $_GET['lg'], time() + 60 * 60 * 24 * 365, "/");
    }
}

(ENVIRONEMENT) ? $_DEBUG->save("Rooter loaded") : "";

I18n::injectDb($_DB);
I18n::SetDefault("en");
I18n::SetSavePath(TMP . "translations");

if (empty($_SESSION['language'])) {
    $_SESSION['language'] = "en";
}

$lg = explode(",", LANGUAGE_AVAILABLE);


if (!in_array($_SESSION['language'], $lg)) {
    $_SESSION['URL_404'] = $_SERVER['QUERY_STRING'];
    header("location: " . WWW_ROOT . "en/error/_404/");
}

I18n::load($_SESSION['language']);
(ENVIRONEMENT) ? $_DEBUG->save("Language loaded") : "";



//mode with php-cli
if (IS_CLI) {
    if ($_SERVER["argc"] >= 3) {
        $_SYSTEM['controller'] = $_SERVER["argv"][1];
        $_SYSTEM['action'] = $_SERVER["argv"][2];
        $_SYSTEM['param'] = !empty($_SERVER["argv"][3]) ? $_SERVER["argv"][3] : '';


        if ($_SERVER["argc"] > 3) {
            $params = array();
            for ($i = 3; $i < $_SERVER["argc"]; $i++) {
                $params[] = $_SERVER["argv"][$i];
            }

            $_SYSTEM['param'] = $params;
        }

        cli_set_process_title("glial-" . $_SYSTEM['controller'] . "-" . $_SYSTEM['action']);
    } else {

        throw new InvalidArgumentException('usage : gial <controlleur> <action> [params]');
    }
    define('LINK', WWW_ROOT . "en" . "/");
} else {  //mode with apache
    define('LINK', WWW_ROOT . I18n::Get() . "/");

    $auth = new Auth();
    $auth->setInstance($_DB->sql(DB_DEFAULT), "user_main", array("login", "password"));
    $auth->setFctToHashCookie(function ($password) {
        return password_hash($password . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'], PASSWORD_DEFAULT);
    });
    $auth->authenticate(false);
    FactoryController::addDi("auth", $auth);

    

    (ENVIRONEMENT) ? $_DEBUG->save("User connexion") : "";

    $_SYSTEM['controller'] = \Glial\Utility\Inflector::camelize($url['controller']);
    $_SYSTEM['action'] = $url['action'];
    $_SYSTEM['param'] = $url['param'];
    

    $acl = new Acl(CONFIG . "acl.config.ini");
    
    
    
    FactoryController::addDi("acl", $acl);

    $js = new Javascript();
    FactoryController::addDi("js", $js);


    if ($acl->checkIfResourceExist($_SYSTEM['controller'] . "/" . $_SYSTEM['action'])) {
        if (!$acl->isAllowed($auth->getAccess(), $_SYSTEM['controller'] . "/" . $_SYSTEM['action'])) {
            if ($auth->getAccess() == 1) {

                $url = "user/connection/";
                $msg = $_SYSTEM['controller'] . "/" . $_SYSTEM['action'] . "<br />" . __("You have to be registered to acces to this page");
            } else {
                //die("here");
                $url = "home/index/";
                $msg = $_SYSTEM['controller'] . "/" . $_SYSTEM['action'] . "<br />" . __("Your rank to this website is not enough to acess to this page");
            }

            set_flash("error", __("Acess denied"), __("Acess denied") . " : " . $msg);
            header("location: " . LINK . $url);
            exit;
        }
    } else {
        set_flash("error", __("Error 404"), __("Page not found") . " : " . __("Sorry, the page you requested : \"" . $_SYSTEM['controller'] . "/" . $_SYSTEM['action'] . "\"is not on this server. Please contact us if you have questions or concerns"));
        header("location: " . LINK . "Error/_404");
        exit;
    }
}



(ENVIRONEMENT) ? $_DEBUG->save("ACL loaded") : "";


//demarre l'application
FactoryController::rootNode($_SYSTEM['controller'], $_SYSTEM['action'], $_SYSTEM['param']);


$i = 10;


(ENVIRONEMENT) ? $_DEBUG->save("Layout loaded") : "";



if ((ENVIRONEMENT) && (!IS_CLI) && (!IS_AJAX) && ("10.10.100.115" === $_SERVER['REMOTE_ADDR'])) {//ENVIRONEMENT
    $execution_time = microtime(true) - TIME_START;

    echo "<hr />";

    echo "Temps d'exéution de la page : " . round($execution_time, 5) . " seconds";
    echo "<br />Nombre de requette : " . $_DB->sql(DB_DEFAULT)->get_count_query();
    $file_list = get_included_files();
    echo "<br />Nombre de fichier loaded : <b>" . count($file_list) . "</b><br />";
    debug($file_list);

    if ($_DB->sql(DB_DEFAULT)->get_count_query() != 0) {
        echo "<table class=\"debug\" style=\"width:100%\">";
        echo "<tr><th>#</th><th>File</th><th>Line</th><th>Query</th><th>Rows</th><th>Last inserted id</th><th>Time</th></tr>";
        $i = 0;
        $j = 0;
        $k = 0;
        foreach ($_DB->sql(DB_DEFAULT)->query as $value) {
            echo "<tr><td>" . $k . "</td><td>" . $value['file'] . "</td><td>" . $value['line'] . "</td><td>" . SqlFormatter::format($value['query']) . "</td><td>" . $value['rows'] . "</td><td>" . $value['last_id'] . "</td><td>" . $value['time'] . "</td></tr>";
            $i += $value['time'];
            $j += $value['rows'];
            $k++;
        }
        
        echo "<tr><td></td><td></td><td></td><td><b>Total</b></td><td>" . $j . "</td><td><b>" . $i . "</b></td></tr>";
        echo "</table>";
    }
    $_DEBUG->print_table();
    echo $_DEBUG->graph();
    echo $_DEBUG->graph2();


    //debug(get_declared_classes());

    echo "SESSION";
    debug($_SESSION);
    echo "GET";
    debug($_GET);
    echo "POST";
    debug($_POST);
    echo "COOKIE";
    debug($_COOKIE);
    echo "REQUEST";
    debug($_REQUEST);
    debug($_SERVER);

    debug($_SITE);


    echo "CONSTANTES : <br />";


    $display = false;
    $constantes = get_defined_constants();
    foreach ($constantes as $constante => $valeur) {
        if ($constante == "TIME_START") {
            $display = true;
        }

        if ($display) {
            echo 'Constante: <b>' . $constante . '</b> Valeur: ' . $valeur . '<br/>';
        }
    }
}
    