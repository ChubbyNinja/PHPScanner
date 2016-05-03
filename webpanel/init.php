<?php

/*
 * Created for PHPScanner
 * User: Danny Hearnah
 * Author: ChubbyNinja
 * URL: https://github.com/ChubbyNinja/PHPScanner
 */

if (isset($_POST['phpsc_action'])) {
    switch ($_POST['phpsc_action']) {

        case 'login':
            if (!$Webpanel->is_authenticated()) {
                $Webpanel->try_authenticate($this->get_action('web_password'), $_POST['phpsc_password']);
                header('Location: ?phpsc');
                die();
            }
            break;
    }
}


if (isset($_GET['phpsc_action']) && $Webpanel->is_authenticated()) {
    switch ($_GET['phpsc_action']) {

        case 'download':
            $Webpanel->download_file($_GET['phpsc_id']);
            break;

        case 'logout':
            $Webpanel->logout();
            break;

        case 'banip':
            $Webpanel->ban_ip($_GET['phpsc_ip']);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            die();
            break;

    }
}
