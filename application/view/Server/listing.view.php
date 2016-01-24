<?php
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use \Glial\Synapse\FactoryController;

$converter = new AnsiToHtmlConverter();

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<div class="well">
    <?php
    echo ' <div class="btn-group" role="group" aria-label="Default button group">';

    foreach ($data['menu'] as $key => $elem) {

        if ($_GET['menu'] == $key) {
            $color = "btn-info";
        } else {
            $color = "btn-primary";
        }

        echo '<a href="'.$elem['url'].'" type="button" class="btn '.$color.'" style="font-size:12px">'
        .' '.$elem['icone'].' '.__($elem['name']).'</a>';
    }
    echo '</div>';
    ?>
</div>

<?php

echo $_GET['url'];
\Glial\Synapse\FactoryController::addNode("Server", explode('/',$_GET['url'])[2], array());

echo "FG";

echo '<table class="table table-bordered table-striped" id="table">';


echo '<tr>';

echo '<th>'.__("Top").'</th>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Available").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.__("IP").'</th>';
//echo '<th>'.__("Hostname").'</th>';
echo '<th>'.__("Version").'</th>';

/*
echo '<th>'.__("Operations system").'</th>';
echo '<th>'.__("Product name").'</th>';
echo '<th>'.__("Arch").'</th>';
echo '<th>'.__("Kernel").'</th>';
echo '<th>'.__("Processor").'</th>';
echo '<th>'."Mhz".'</th>';

echo '<th>'.__("Memory").'</th>';
echo '<th title="0.75*CPU*GHZ + 0.5 Memory Go">'.__("Indice").'</th>';
*/

echo '<th>'.__("Error").'</th>';
echo '</tr>';


$i = 0;
foreach ($data['servers'] as $server) {
    $i++;

    $style = "";
    if(! empty($server['version']) && empty($server['is_available']))
    {
        $style = 'background-color:#d9534f';
    }


    echo '<tr>';
    echo '<td style="'.$style.'">'.$i.'</td>';
    echo '<td style="'.$style.'">'.$server['id'].'</td>';
    echo '<td style="'.$style.'">';
    echo '<span class="glyphicon '.($server['is_available'] == 1 ? "glyphicon-ok" : "glyphicon-remove").'" aria-hidden="true"></span>';
    echo '</td>';
    echo '<td style="'.$style.'">'.str_replace('_', '-', $server['name']).'</td>';
    echo '<td style="'.$style.'">'.$server['ip'].'</td>';
    //echo '<td style="'.$style.'">'.$server['hostname'].'</td>';



    echo '<td style="'.$style.'">'.$server['version'].'</td>';
    
echo '<td style="'.$style.'" class="">';

    if (strstr($server['error'],'[0m') || strstr($server['error'],'Call Stack:'))
{
$converter = new AnsiToHtmlConverter();
$html = $converter->convert($server['error']);



echo '<pre style="background-color: black; overflow: auto; height:500px; padding: 10px 15px; font-family: monospace;">'.$html.'</pre>';
//$server['error'];
}
else
{
    echo str_replace("\n",'<br>', trim($server['error']));
}
    echo '</td>';


/*
    echo '<td style="'.$style.'">'.$server['operating_system'].'</td>';

    echo '<td style="'.$style.'">'.$server['product_name'].'</td>';


    $class = ("i686" == $server['arch']) ? "error" : "";
    echo '<td style="'.$style.'" class="'.$class.'">'.$server['arch'].'</td>';
    echo '<td style="'.$style.'">'.$server['kernel'].'</td>';

    echo '<td style="'.$style.'">'.$server['processor'].'</td>';
    echo '<td style="'.$style.'">'.$server['cpu_mhz'].'</td>';
    echo '<td style="'.$style.'">'.round($server['memory_kb'] / 1024 / 1024, 2).' Go</td>';
    echo '<td style="'.$style.'">'.round(0.75 * $server['processor'] * ($server['cpu_mhz'] / 1024) + 0.5 * ($server['memory_kb'] / 1024 / 1024), 2).'</td>';
*/

    echo '</tr>';
}

echo '</table>';



//FactoryController::addNode("Agent", "index", array());
