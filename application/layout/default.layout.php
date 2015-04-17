<?php

use \Glial\Synapse\FactoryController;
use \Glial\I18n\I18n;

FactoryController::addNode("Layout", "header",$GLIALE_TITLE);


echo '<div id="page">';

echo "<div id=\"title\">";
echo "<h2>".$GLIALE_TITLE."<br />";
echo "<span><a href=\"".WWW_ROOT."\">".__("Home")."</a> ".$GLIALE_ARIANE."</span>";
echo "</h2>";
echo "</div>";

get_flash();
echo $GLIALE_CONTENT;


echo '</div>';


FactoryController::addNode("Layout", "footer");