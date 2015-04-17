<?php

use \Glial\Synapse\Controller;
use \Glial\Sgbd\Sql\Mysql\MasterSlave;
use \Glial\Cli\Color;
use \Glial\Sgbd\Sgbd;
use \Glial\Parser\LinkedIn\LinkedIn;

//

class Cv extends Controller
{
    function getCV()
    {

        $data['mission'] = LinkedIn::getExperience('https://www.linkedin.com/pub/aur%C3%A9lien-lequoy/73/554/302');
        
        $this->set('data', $data);   
    }
}


/*
 * http://10.10.7.150/
 * 
 * <p class="orgstats organization-details past-position">
Société à responsabilité limitée (SRL); 1001-5&nbsp;000&nbsp;employés;
secteur Télécommunications
</p>
 */