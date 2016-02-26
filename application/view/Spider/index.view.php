<?php

\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array());

echo '<br />';

if (!empty($_POST['mysql_server']['id'] ))
{
	\Glial\Synapse\FactoryController::addNode("Spider", "testIfSpiderExist", array($_POST['mysql_server']['id']));
	\Glial\Synapse\FactoryController::addNode("Spider", "Server", array($_POST['mysql_server']['id']));
}

