<?php



echo '<table class="table table-bordered table-striped" id="table">';


echo '<tr>';

echo '<th>'.__("Top").'</th>';
echo '<th>'.__("ID").'</th>';
echo '<th>'.__("Name").'</th>';
echo '<th>'.__("IP").'</th>';
echo '<th>'.__("Port").'</th>';
echo '<th>'.__("User connected").'</th>';
echo '<th>'.__("Select").'</th>';
echo '<th>'.__("Insert").'</th>';
echo '<th>'.__("Update").'</th>';
echo '<th>'.__("Delete").'</th>';
echo '<th>'.__("OLAP / OLTP").'</th>';
echo '</tr>';

$total['connected'] = 0;
$total['select'] = 0;
$total['insert'] = 0;
$total['update'] = 0;
$total['delete'] = 0;

$i = 0;
foreach ($data['servers'] as $server) {
    $i++;

	$style ="";
    echo '<tr>';
    echo '<td style="'.$style.'">'.$i.'</td>';
    echo '<td style="'.$style.'">'.$server['id'].'</td>';
    echo '<td style="'.$style.'">'.str_replace('_', '-', $server['name']).'</td>';
    echo '<td style="'.$style.'">'.$server['ip'].'</td>';
    echo '<td style="'.$style.'">'.$server['port'].'</td>';
    echo '<td style="'.$style.'">'.$server['connected'].'</td>';
    echo '<td style="'.$style.'">'.$server['select'].'</td>';
    echo '<td style="'.$style.'">'.$server['insert'].'</td>';
    echo '<td style="'.$style.'">'.$server['update'].'</td>';
    echo '<td style="'.$style.'">'.$server['delete'].'</td>';
    echo '<td style="'.$style.'">'.round ($server['select'] / ($server['delete']+ $server['select']+ $server['insert']+ $server['update'])*100,2).' %</td>';
    echo '</tr>';

    $total['connected'] += $server['connected'];
    $total['select'] += $server['select'];
    $total['insert'] += $server['insert'];
    $total['update'] += $server['update'];
    $total['delete'] += $server['delete'];
}

echo '<tr>';
echo '<td style="'.$style.'" colspan="5">'.__('Total').'</td>';
echo '<td style="'.$style.'">'.$total['connected'].'</td>';
echo '<td style="'.$style.'">'.$total['select'].'</td>';
echo '<td style="'.$style.'">'.$total['insert'].'</td>';
echo '<td style="'.$style.'">'.$total['update'].'</td>';
echo '<td style="'.$style.'">'.$total['delete'].'</td>';
echo '<td style="'.$style.'">'.round ($total['select'] / ($total['delete']+ $total['select']+ $total['insert']+ $total['update'])*100,2).' %</td>';

echo '</tr>';

echo '</table>';

