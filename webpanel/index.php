<?php
require PHPSC_ROOT . '/class/Webpanel.php';
$Webpanel = new Webpanel();

require 'init.php';

require 'inc/header.php';
if( !$Webpanel->is_authenticated() ) {
    require 'inc/login.php';
}

if( $Webpanel->is_authenticated() ) {

    $page = 'dashboard';

    if( isset($_GET['page']) ) {
        $page = $Webpanel->sanitize($_GET['page']);
    }

    if( stream_resolve_include_path( 'inc/page-' . $page . '.php') ) {
        include 'inc/page-' . $page . '.php';
    } else {
        include 'inc/page-error.php';
    }


}

require 'inc/footer.php';