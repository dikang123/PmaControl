<?php

use \Glial\Synapse\Controller;

class Layout extends Controller
{

    function header($title)
    {
        $this->set('GLIALE_TITLE', $title);
    }

    function footer()
    {
        
        
    }

    function headerPma($title)
    {
        //$this->di['js']->addJavascript(array("jquery-latest.min.js","http://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"));
        
        
        $data['auth'] = $this->di['auth']->getAccess();

        $this->set('data', $data);
        $this->set('GLIALE_TITLE', $title);
    }

    function footerPma()
    {


        $data['auth'] = $this->di['auth']->getAccess();

        if ($data['auth'] !== 1) {
            $user = $this->di['auth']->getuser();
            $data['name'] = $user->firstname . " " . $user->name . " (" . $user->email . ")";
        }
        $this->set('data', $data);
    }

}
