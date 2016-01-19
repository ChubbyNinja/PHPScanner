<?php

/**
 * Created for PHPScanner
 * User: Danny Hearnah
 * Author: Skyblue Creations Ltd.
 *
 * Date: 1/19/2016
 * Time: 10:34 AM
 */
class Webpanel extends PHPScanner
{

    private $authenticated = false;

    function __construct()
    {
        parent::load_config();
        $this->check_authenticated();
    }

    /**
     * @return boolean
     */
    public function is_authenticated()
    {
        return $this->authenticated;
    }

    /**
     * @param boolean $authenticated
     */
    public function set_authenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }


    public function try_authenticate( $config_pass, $user_pass )
    {

        if( $config_pass !== $this->sanitize($user_pass) ) {
            return false;
        }

        $this->set_authenticated( true );
        setcookie('phpsc_web', 1, 0 );

        return true;

    }

    public function check_authenticated()
    {
        if( isset($_COOKIE['phpsc_web']) ) {
            $this->set_authenticated( true );
        }
    }

    public function logout()
    {
        setcookie('phpsc_web', false, -3600 );
        $this->set_authenticated( false );
    }


    public function sanitize( $input )
    {
        $content = strip_tags($input);
        $content = htmlspecialchars( $content );
        $content = trim( $content );

        return $content;
    }

    public function get_vault()
    {
        $db = parent::get_db_connection();

        $sql = "SELECT * FROM `phpsc_vault` ORDER BY `id` DESC";

        return $db->run_sql($sql );
    }
}