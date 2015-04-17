<?php




$svg = 'tmp/replication.svg';
//echo '<div style="background: url('.IMG.$svg.')"></div>';
	

//echo '<embed src="'.IMG.$svg.'" type="image/svg+xml" />';

$filename = $data['file'];


echo '<div id="svg">';

$handle = fopen($filename, "r");

$remove = true;

if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        
        if ("<svg" != substr($buffer, 0,4) && $remove)
        {
            $remove = false;
            continue;
        }
        
        echo $buffer;
    }
    if (!feof($handle)) {
        echo "Erreur: fgets() a échoué\n";
    }
    fclose($handle);
}


echo '</div>';
