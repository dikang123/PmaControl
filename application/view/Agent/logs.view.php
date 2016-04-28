<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

$converter = new AnsiToHtmlConverter();
$html = $converter->convert($data['log']);
?>

File log : <code><?=$data['log_file'] ?></code><br /><br />
<pre id="data_log" style="background-color: black; overflow: auto; height:500px; padding: 10px 15px; font-family: monospace;"><?php echo $html ?></pre>


<form action="" method="post">
    
    <?php
    echo '<div class="form-group">';


echo ' <button type="submit" class="btn btn-primary">Submit</button>';

echo '</div>';
    ?>
</form>