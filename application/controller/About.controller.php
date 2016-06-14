<?php

use \Glial\Synapse\Controller;

class About extends Controller {

    public function index() {


        $name = __("About");
        $this->title = '<i class="fa fa-info-circle" style="font-size:32px"></i> ' . $name;
        $this->ariane = '> <i style="font-size: 16px" class="fa fa-puzzle-piece"></i> Plugins > <i class="fa fa-info-circle" style="font-size:16px"></i> ' 
                . $name;
        
        
        $data['graphviz'] = shell_exec("dot -V");
        $data['php'] = phpversion();
        $data['mysql'] = shell_exec("mysql --version");
        $data['kernel'] = shell_exec("uname -a");
        $data['os'] = shell_exec("lsb_release -ds");
        //$data['mysql'] = shell_exec("mysql --version");
        
        
        $this->set('data',$data);
    }



}
