<?php
echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';



//obsolete !!!
//$blocks = \Glial\Synapse\FactoryController::addNode("Dot", "renderer", array());
/*
  foreach ($blocks as $block) {
  echo $block;
  //echo '<div style="float:left; width:20px; height:20px; background:#00F"></div>';
  } */


foreach ($data['graphs'] as $graph) {
    if (!empty($graph['display'])) {
        echo '<div style="float:left; border:#000 0px solid">';
        //echo $graph['height'];
        echo $graph['display'];
        echo '</div>';
    }
}

echo '<div style="clear:both"></div>';
