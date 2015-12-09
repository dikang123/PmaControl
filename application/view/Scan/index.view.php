<form action="" method="post">
    <div class="table-responsive">
        <table class="table table-bordered">
            <?php

            //debug($data['scan']);
            $list_port = [];
            foreach ($data['scan'] as $line) {
                foreach ($line['port'] as $port => $elem) {
                    if ($port > 3309) {
                        continue;
                    }
                    $list_port[$port] = $elem['name'];
                }
            }
            ksort($list_port);

            $status['open']     = "check";
            $status['closed']   = "close";
            $status['filtered'] = "filter";

            echo '<tr>';
            echo '<th rowspan="2"><input id="check-all" type="checkbox" name="vehicle" value="1"></th>';
            echo '<th rowspan="2">'.__('Top').'</th>';
            echo '<th rowspan="2">'.__('Hostname').'</th>';
            echo '<th rowspan="2">'.__('IP').'</th>';
            echo '<th rowspan="2">'.__('MAC').'</th>';
            echo '<th rowspan="2">'.__('Type').'</th>';
            echo '<th colspan="'.count($list_port).'">'.__('Port').'</th>';
            echo '<th rowspan="2">'.__('Monitoring').'</th>';
            echo '</tr>';
            echo '<tr>';
            foreach ($list_port as $port => $elems) {
                echo '<th title="'.$elems.'">'.$port.'</th>';
            }
            echo '</tr>';

            $i = 0;
            foreach ($data['scan'] as $line) {
                $is_mysql = false;

                $style = "";
                if (!in_array($line['ip'], $data['ip'])) {
                    if (!empty($line['port']['3306']['status'])) {
                        $style    = "background-color:#dff0d8";
                        $is_mysql = true;
                    }
                }

                $i++;
                echo '<tr>';
                echo '<td style="'.$style.'">'.($is_mysql ? '<input type="checkbox" name="vehicle" class="check-box">' : '').'</td>';
                echo '<td style="'.$style.'">'.$i.'</td>';
                echo '<td style="'.$style.'">'.$line['hostname'].'</td>';
                echo '<td style="'.$style.'">'.$line['ip'].'</td>';
                echo '<td style="'.$style.'">Mac</td>';

                if (!empty($line['port'][22]) && $line['port']['22']['status'] === "open") {
                    $os = "linux";
                } else {
                    $os = "windows";
                }
                echo '<td style="'.$style.'"><i style="font-size:16px" class="fa fa-'.$os.'"></i></td>';

                foreach ($list_port as $port => $elem) {

                    if (!empty($line['port'][$port])) {
                        echo '<td style="'.$style.'"><i style="font-size:16px" class="fa fa-'.$status[$line['port'][$port]['status']].'"></i></td>';
                    } else {
                        echo '<td style="'.$style.'"></td>';
                    }
                }
                echo '<td style="'.$style.'">';
                if ($is_mysql) {
                    echo '<a href="'.LINK.'mysql/add/'.$line['ip'].'" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> '.__("Add").'</a>';
                }

                echo '</td>';

                echo '</tr>';
            }
            ?>
        </table>
    </div>
</form>
<button type="button" class="btn btn-primary"><i style="font-size:14px" class="fa fa-share fa-flip-vertical"></i> <?= __("Add all these servers to monitoring") ?></button>
<a href="<?= LINK ?>scan/refresh/" role="button" class="btn btn-primary"><i style="font-size:14px" class="fa fa-refresh fa-spin"></i> <?= __("Refresh scan") ?></a>