<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class Dot extends Controller
{

    function index()
    {
        $this->layout_name = 'default';

        $this->title  = __("Error 404");
        $this->ariane = " > ".$this->title;

        //$this->javascript = array("");
    }
}
