<?php

/*
 * Created for PHPScanner
 * User: Danny Hearnah
 * Author: ChubbyNinja
 * URL: https://github.com/ChubbyNinja/PHPScanner
 */

/**
 * Created for PHPScanner
 * User: Danny Hearnah
 * Author: ChubbyNinja.
 *
 * Date: 1/19/2016
 * Time: 11:40 AM
 */
if (isset($_POST['phpsc_action'])) {
    switch ($_POST['phpsc_action']) {

        case 'login':

            $loggedin = $Webpanel->try_authenticate($this->get_action('web_password'), $_POST['phpsc_password']);
            var_dump($loggedin);

            break;

    }
}
