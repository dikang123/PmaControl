<?php



use Glial\Html\Form\Form;
?>

<div class="container">
    <div class="row">
        <div class="span4 offset4" style="margin-top: 100px;">
            <!-- Session lost information -->
            <!-- Authentication failed -->
            <!-- Login form -->
            <div class="well" style="text-align: center;">
                <h3 style="margin-bottom: 3px;"><?=__("Bienvenue, veuillez vous identifier","fr")?></h3>
                <form id="loginForm" name="loginForm" method="post" action="" class="form-horizontal">
                    <input type="hidden" name="loginForm" value="loginForm">

                    <table class="table" style="margin-top: 7px;">
                        <tbody>
                            <tr>
                                <td style="text-align:right;"><?=__("Login")?> (Email)</td>
                                <td><?=Form::input("user_main", "login", array("class"=>"form-control")) ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:right;"><?=__("Password")?>
                                </td>
                                <td><?=Form::input("user_main", "password", array("type"=>"password", "class"=>"form-control")) ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:right;">
                                    <input id="Reset" type="reset" name="Reset" value="Réinitialiser" class="btn btn-large btn-default">
                                    
                                </td>
                                <td><input id="login" type="submit" name="login" value="Connexion" class="btn btn-primary btn-large">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
                <?php
                
                if (! LDAP_CHECK)
                {
                ?>
                <p>
                    <a href="<?=LINK ?>user/register/"><?=__("Sign up, it's free !")?></a>
                    (<a href="<?=LINK ?>user/lost_password/"><?=__("password forgotten")?></a>)
                </p>
                <?php
                /**/
                }
                ?>
                
                <p>
                    <a href="mailto:aurelien.lequoy@esysteme.com">
                        <i class="icon-envelope" style="margin-right: 5px;"></i><?=__("Contacter le créateur de l'application","fr")?>
                    </a>
                </p>
            </div>
        </div>
    </div><!-- End of row -->
</div>