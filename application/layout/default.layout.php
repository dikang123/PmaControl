<?php

use \Glial\Synapse\FactoryController;
use \Glial\I18n\I18n;

FactoryController::addNode("Layout", "headerPma",$GLIALE_TITLE);



echo '<div id="page">';

echo "<div id=\"glial-title\">";
echo "<h2>".$GLIALE_TITLE."</h2>";
echo "<span class=\"ariane\"><a href=\"".WWW_ROOT."\">".__("Home")."</a> ".$GLIALE_ARIANE."</span>";
echo "</div>";


echo "<div style=\"padding:0 10px 10px 10px\">";

get_flash();
echo $GLIALE_CONTENT;
echo "</div>";
echo '</div>';


FactoryController::addNode("Layout", "footerPma");