
<div>



    <nav class="navbar navbar-inverse navbar-static navbar-fixed-<?= $data['position'] ?>">

        <div class="container-fluid">

            <?php
            if ($data['position'] === "top"):
                ?>
                <div class="navbar-header">
                    <button class="navbar-toggle collapsed" type="button" data-toggle="collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#" style="color:#fff"><i class="fa fa-database fa-lg"></i> PmaControl <span class="badge badge-info" style="font-variant: small-caps; font-size: 14px; vertical-align: middle; background-color: #4384c7" title="2015-12-04">v0.8 beta (2015-12-04)</span></a>
                </div>
                <?php
            endif;
            ?>

            
            <?php
            $class="";
            if ($data['position'] === "bottom")
            {
                $class=" pull-right";
            }
            ?>

            <div class="collapse navbar-collapse bs-example-js-navbar-collapse<?=$class ?>">
                <ul class="nav navbar-nav">
                    <?php
                    $close_at = [];
                    $i = 1;

                    foreach ($data['menu'] as $item) {

                        foreach ($close_at as $key => $to_close) {
                            if ($item['bg'] > $to_close) {
                                echo '
                                </ul>
                                </li>' . "\n";
                                unset($close_at[$key]);
                            }
                        }

                        if ($item['bd'] - $item['bg'] > 1) {
                            echo '
                                <li class="dropdown">
                                <a id="drop' . $i . '" href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false">
                                ' . $item['icon'] . ' ' . __($item['title']) . '
                                <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu" aria-labelledby="drop' . $i . '">';

                            $close_at[] = $item['bd'];
                            $i++;
                        } else {
                                $item['url'] = str_replace('[IMG]', IMG, $item['url']);
                                $item['icon'] = str_replace('[IMG]', IMG, $item['icon']);

                                
                                $item['url'] = str_replace(array('{LINK}'), array(LINK), $item['url']);

                                if (strstr($item['url'],'{PATH}'))
                                {
                                    $item['url'] = WWW_ROOT .str_replace('{PATH}', '', $item['url']).'/'.substr($_GET['glial_path'], 3);
                                }


                                $PATH = WWW_ROOT .substr($_GET['glial_path'], 3);


                            echo '<li role="presentation"><a role="menuitem" tabindex="-1" href="' . $item['url'] . '">' . $item['icon'] . ' ' . __($item['title']) . '</a></li>';
                        }
                    }


                    /*

                      <li class="dropdown">
                      <a id="drop1" href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false">
                      Dropdown
                      <span class="caret"></span>
                      </a>
                      <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                     * 
                     * 
                      <li role=""><a role="" tabindex="-1" href="https://twitter.com/fat">Action</a></li>
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Another action</a></li>
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Something else here</a></li>
                      <li role="presentation" class="divider"></li>
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Separated link</a></li>
                      </ul>
                      </li>
                      <li class="dropdown">
                      <a id="drop2" href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false">
                      Dropdown
                      <span class="caret"></span>
                      </a>
                      <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Action</a></li>
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Another action</a></li>
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Something else here</a></li>
                      <li role="presentation" class="divider"></li>
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Separated link</a></li>
                      </ul>
                      </li>
                      </ul>
                      <ul class="nav navbar-nav navbar-right">
                      <li id="fat-menu" class="dropdown">
                      <a id="drop3" href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" role="button" aria-expanded="false">
                      Dropdown
                      <span class="caret"></span>
                      </a>
                      <ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Action</a></li>
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Another action</a></li>
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Something else here</a></li>
                      <li role="presentation" class="divider"></li>
                      <li role="presentation"><a role="menuitem" tabindex="-1" href="https://twitter.com/fat">Separated link</a></li>
                      </ul>
                      </li>

                     */
                    ?>


                </ul>
            </div>
        </div>
    </nav>
</div>