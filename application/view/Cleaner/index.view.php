<table class="pmacontrol">
    <tr>
        <th><?= __('ID') ?> </th>
        <th><?= __('Name') ?> </th>
        <th><?= __('Server') ?> </th>
        <th><?= __('IP') ?> </th>
        <th><?= __('Main table') ?> </th>
        <th><?= __('Tools') ?> </th>
        <th><?= __('Status') ?> </th>
        <th><?= __('Remove') ?> </th>

    </tr>

    <?php
    foreach ($data['cleaner_main'] as $cleaner) {
        

        $hightlight = ($cleaner['id_cleaner_main'] === $data['id_cleaner']) ? "highlight_row" : "";
        echo '<tr class="cleaner_main clickable-row ' . $hightlight . '" data-id="' . $cleaner['id_cleaner_main'] . '" data-href="' . LINK . 'Cleaner/index/' . $cleaner['id_cleaner_main'] . '">';
        echo '<td>' . $cleaner['id_cleaner_main'] . '</td>';
        echo '<td>' . $cleaner['libelle'] . '</td>';
        echo '<td>' . str_replace("_", "-", $cleaner['mysql_server_name']) . '</td>';
        echo '<td>' . $cleaner['ip'] . '</td>';
        echo '<td>' . $cleaner['main_table'] . '</td>';
        echo '<td>';

        echo '<a href="' . LINK . 'mysql/mpd/' . str_replace("_", "-", $cleaner['mysql_server_name']) . '/' . $cleaner['database'] . '/' . $cleaner['id_cleaner_main'] . '" type="button" class="btn btn-primary" style="font-size:12px">' . ' <span class="glyphicon glyphicon-screenshot" aria-hidden="true" style="font-size:13px"></span> ' . __("View tables impacted") . '</a>';


        echo ' <div class="btn-group" role="group" aria-label="Default button group">';

        //

        echo '<a href="' . LINK . 'cleaner/stop/' . $cleaner['id_cleaner_main'] . '" type="button" class="btn btn-primary" style="font-size:12px">' . ' <span class="glyphicon glyphicon-stop" aria-hidden="true" style="font-size:13px"></span> ' . __("Stop Daemon") . '</a>';
        echo '<a href="' . LINK . 'cleaner/start/' . $cleaner['id_cleaner_main'] . '" type="button" class="btn btn-primary" style="font-size:12px">' . ' <span class="glyphicon glyphicon-play" aria-hidden="true" style="font-size:13px"></span> ' . __("Start Daemon") . '</a>';
        //echo '<a href="' . LINK . '" type="button" class="btn btn-primary" style="font-size:12px">' . ' <span class="glyphicon glyphicon-refresh aria-hidden="true" style="font-size:13px"></span> ' . __("Restart Daemon") . '</a>';
        echo '</div>';

        echo '</td>';
        echo '<td>';
        // . '<span class="label label-success" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;" title="2014-10-29">Running</span>' 
        // . ' <span class="label label-danger" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;">Error</span>'

        if (empty($cleaner['pid'])) {
            echo ' <span class="label label-warning" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;">' . __("Stopped") . '</span>';
        } elseif (!empty($cleaner['pid'])) {


            //put in controller, use anonymous function
            $cmd = "ps -p " . $cleaner['pid'];
            $alive = shell_exec($cmd);

            if (strpos($alive, $cleaner['pid']) !== false) {
                echo ' <span class="label label-success" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;" title="2014-10-29">' . __("Running") . ' (PID : ' . $cleaner['pid'] . ')</span>';
            } else {
                echo ' <span class="label label-danger" style="font-variant: small-caps; font-size: 15px; vertical-align: middle;">' . __("Error") . '</span>';
            }
        }

        echo '</td>';
        echo '<td>';

        echo ' <a href="' . LINK . 'Cleaner/delete/' . $cleaner['id_cleaner_main'] . '" type="button" class="btn btn-danger" style="font-size:12px">' . ' <span class="glyphicon glyphicon-remove aria-hidden="true" style="font-size:13px"></span> ' . __("Delete cleaner") . '</a>';

        echo '</td>';


        echo '</tr>';
    }
    ?>



</table>
<br />
<a href='<?= LINK ?>Cleaner/add/' id="add" class="btn btn-primary"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> Add a cleaner</a>
<br />
<br />

<?php
if (!empty($data['id_cleaner'])) {
    echo '<div class="well">';

    echo '<div class="btn-group" role="group" aria-label="Default button group">';

    $class = ("log" === $data['menu']) ? 'btn-primary' : 'btn-default';
    echo '<a href="' . LINK . 'Cleaner/index/' . $data['id_cleaner'] . '/log" type="button" class="btn ' . $class . '"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> ' . __('Logs') . '</a>';
    $class = ("treatment" === $data['menu']) ? 'btn-primary' : 'btn-default';
    echo '<a href="' . LINK . 'Cleaner/index/' . $data['id_cleaner'] . '/treatment" type="button" class="btn ' . $class . '"><span class="glyphicon glyphicon-tasks" aria-hidden="true"></span> ' . __('Last treatement') . '</a>';
    $class = ("statistics" === $data['menu']) ? 'btn-primary' : 'btn-default';
    echo '<a href="' . LINK . 'Cleaner/index/' . $data['id_cleaner'] . '/statistics" type="button" class="btn ' . $class . '"><span class="glyphicon glyphicon-stats" aria-hidden="true"></span> ' . __('Statistics') . '</a>';
    $class = ("settings" === $data['menu']) ? 'btn-primary' : 'btn-default';
    echo '<a href="' . LINK . 'Cleaner/index/' . $data['id_cleaner'] . '/settings" type="button" class="btn ' . $class . '"><span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> ' . __('Settings') . '</a>';

    echo '</div>';
    echo '</div>';
}
?>

<div class="well">
    <div class="btn-group" role="group" aria-label="Default button group">
        <?php
        echo "</div>";

        if (!empty($data['id_cleaner'])) {

            \Glial\Synapse\FactoryController::addNode("Cleaner", $data['menu'], array($data['id_cleaner']));
        }
        ?>
    </div>
</div>


<!--

<div class="jumbotron">
<h1>Hello, world!</h1>
<p>This is a simple hero unit, a simple jumbotron-style component for calling extra attention to featured content or information.</p>
<p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a></p>
</div>

<div class="bs-callout bs-callout-warning" id="callout-navbar-overflow">
<h4 id="overflowing-content">Overflowing content<a class="anchorjs-link" href="#overflowing-content"><span class="anchorjs-icon"></span></a></h4>
<p>Since Bootstrap doesn't know how much space the content in your navbar needs, you might run into issues with content wrapping into a second row. To resolve this, you can:</p>
<ol type="a">
  <li>Reduce the amount or width of navbar items.</li>
  <li>Hide certain navbar items at certain screen sizes using <a href="../css/#responsive-utilities">responsive utility classes</a>.</li>
  <li>Change the point at which your navbar switches between collapsed and horizontal mode. Customize the <code>@grid-float-breakpoint</code> variable or add your own media query.</li>
</ol>

-->