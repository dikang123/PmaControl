<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Glial\Synapse\Controller;

class DeployRsaKey extends Controller
{

    public function index()
    {


        $this->title  = '<i style="font-size: 32px" class="fa fa-key" aria-hidden="true"></i> '."Deploy key RSA";
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > '
            .'<i style="font-size: 16px" class="fa fa-key" aria-hidden="true"></i> '."Deploy key RSA";
    }
}