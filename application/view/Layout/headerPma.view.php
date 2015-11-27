<?php

use \Glial\I18n\I18n;
use \Glial\Synapse\FactoryController;

echo "<!DOCTYPE html>\n";
echo "<html lang=\"" . I18n::Get() . "\">";
echo "<head>\n";
echo "<meta charset=utf-8 />\n";
echo "<meta name=\"Keywords\" content=\"\" />\n";
echo "<meta name=\"Description\" content=\"\" />\n";
echo "<meta name=\"Author\" content=\"Aurelien LEQUOY\" />\n";
echo "<meta name=\"robots\" content=\"index,follow,all\" />\n";
echo "<meta name=\"generator\" content=\"GLIALE 1.1\" />\n";
echo "<meta name=\"runtime\" content=\"[PAGE_GENERATION]\" />\n";
echo "<link rel=\"shortcut icon\" href=\"favicon.ico\" />";
echo "<title>" . $GLIALE_TITLE . " - PmaControl 0.8</title>\n";
//echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"\" />\n";
?>
<!--
<link rel="stylesheet" type="text/css" href="<?= CSS ?>bootstrap.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>iprod.css" />


-->
<link rel="stylesheet" type="text/css" href="<?= CSS ?>bootstrap.css">
<link rel="stylesheet" type="text/css" href="<?= CSS ?>autocomplete.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>notification.style.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>title.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>reporting.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>pmacontrol.css" />

<link href="<?= CSS ?>font-awesome.min.css" rel="stylesheet">


<!--<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
<!-- -->
</head>
<body>

    <?php
    if ($data['auth'] !== 1) {
        FactoryController::addNode("Menu", "show", array("1"));
    } else {

        FactoryController::addNode("Menu", "show", array("3"));
    }
