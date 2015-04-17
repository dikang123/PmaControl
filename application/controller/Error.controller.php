<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class Error extends Controller {

    
    
    function _404() {
        $this->layout_name = 'default';


        $this->title = __("Error 404");
        $this->ariane = " > " .$this->title;

        //$this->javascript = array("");


    }

}
