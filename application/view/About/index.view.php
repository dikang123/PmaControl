<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div style="margin: 0 auto 0 auto; width:500px">
    <span style="color:#000; font-size: 48px">
    <i class="fa fa-database fa-lg"></i>
    PmaControl
    </span><br />
    <span class="badge badge-info" style="font-variant: small-caps; font-size: 20px; vertical-align: middle; background-color: #4384c7">v0.8 beta (<?=$data['date']?>)</span>
</div>
<br />
<h3>Product</h3>
<ul>
    <li>Product Version: <b>PmaControl IDE <?=$data['pmacontrol']?></b> (Build 123d00eab1c5d13e8f8d071e430e4369d8cb5c7b)</li>
    <li>Lisense: <b><a href="http://www.gnu.org/licenses/gpl-3.0.fr.html">GNU GPL v3</a></b></li>
</ul>
<h3>Dependencies</h3>
<ul>

    <li>PHP Version: <b><?=$data['php']?></b></li>
    <li>MySQL / MariaDB / Percona Server Version: <b><?=$data['mysql']?></b></li>
    <li>GraphViz: <b><?=$data['graphviz']?></b></li>
    <li>MySQL-sys: <b>v1.5.0</b> (<a href="https://github.com/Esysteme/mysql-sys">Esysteme/mysql-sys</a>
        forked from <a href="https://github.com/mysql/mysql-sys">mysql/mysql-sys</a>)</li>
    <li>Kernel: <b><?=$data['kernel']?></b></li>
    <li>GNU/Linux: <b><?=$data['os']?></b></li>
    
</ul>
<h3>Powered by</h3>
<ul>
    <li><img src="<?=IMG ?>main/esysteme.jpg" height="32" width="32" /><b>Esysteme</b> (<a href="http://wwww.esysteme.com">www.esysteme.com</a>)</li>
    <li>Author : <b>Aurélien LEQUOY</b></li>
    <li>Email : <b>pmacontrol@esysteme.com</b></li>
</ul>
<h3>Credits</h3>
<ul>
    <li>Stéphane SVAROQUIE</li>
    <li>Mark LEITH</li>
    <li>Anna GOLUB</li>
    <li>Serge FREZEFOND</li>
    <li>Olivier DAISINI</li>
    <li>Dimitri KRAVTCHUK</li>
</ul>
