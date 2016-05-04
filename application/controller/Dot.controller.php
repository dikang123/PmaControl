<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;
use \Glial\Form\Upload;
use \Glial\Date\Date;


class Dot extends Controller
{

    public function index()
    {
        $this->layout_name = 'default';

        $this->title  = __("Error 404");
        $this->ariane = " > ".$this->title;

        //$this->javascript = array("");
    }
    
    
    public function run()
    {
        $this->view = false;
        $graph = new Alom\Graphviz\Digraph('G');
        
        
        
    }
    
    
    /*
     * The goal is this function is to split the graph isloated to produce different dot
     * like that we can provide a better display to dend user and hide the part that they don't need
     * 
     */
    public function splitGraph()
    {
        
        
    }
    
    public function checkMasterSlave()
    {
        
    }
    
    
}
