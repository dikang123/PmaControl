<?php


use \Glial\Html\Form\Form;


echo Form::Select("mysql_server","id", $data['list_server'],"", array("class" => "form-control"));


