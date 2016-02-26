<?php


use \Glial\Html\Form\Form;


echo '<form action="" method="post" class="form-inline">';
echo '<div class="form-group">';
echo Form::Select("mysql_server","id", $data['list_server'],"", array("class" => "form-control"));


echo ' <button type="submit" class="btn btn-primary">Submit</button>';

echo '</div>';
echo '</form>';
