<?php

use Glial\Html\Form\Form;
use \Glial\Synapse\FactoryController;
?>

<form action="" method="post">
    <div class="well">

        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4"> <b><?= __("Orginal") ?></b></div>
            <div class="col-md-4"> <b><?= __("Compare") ?></b></div>

        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Server") ?></div>
            <div class="col-md-4"><?=
                Form::select("compare_main", "id_mysql_server__original", $data['server'], "", array("class" => "form-control"))
                ?></div>
            <div class="col-md-4"><?=
                Form::select("compare_main", "id_mysql_server__compare", $data['server'], "", array("class" => "form-control"))
                ?></div>
        </div>

        <div class="row">
            <div class="col-md-4"><?= __("Database") ?></div>
            <div class="col-md-4">
                <?=
                Form::select("compare_main", "database__original", $data['listdb1'], "", array("class" => "form-control"))
                ?>
            </div>
            <div class="col-md-4">
                <?=
                Form::select("compare_main", "database__compare", $data['listdb2'], "", array("class" => "form-control"))
                ?>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-transfer" style="font-size:12px"></span> <?= __("Compare") ?></button>
                <button type="reset" class="btn btn-danger"><span class="glyphicon glyphicon-remove" style="font-size:12px"></span> <?= __("Reset") ?></button>
            </div>
        </div>
    </div>
</form>



<?php
if ($data['display']) {
    $menu = FactoryController::addNode("Compare", "menu", array($data));
    FactoryController::addNode("Compare", "getObjectDiff", array($data, $menu));
}
