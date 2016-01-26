<?php

/*
 * Created for PHPScanner
 * User: Danny Hearnah
 * Author: ChubbyNinja
 * URL: https://github.com/ChubbyNinja/PHPScanner
 */

class Webpanel extends PHPScanner
{
    private $authenticated = false;

    public function __construct()
    {
        parent::load_config();
        $this->check_authenticated();
    }

    /**
     * @return bool
     */
    public function is_authenticated()
    {
        return $this->authenticated;
    }

    /**
     * @param bool $authenticated
     */
    public function set_authenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }

    public function try_authenticate($config_pass, $user_pass)
    {
        if ($config_pass !== $this->sanitize($user_pass)) {
            return false;
        }

        $this->set_authenticated(true);

        $hash = $this->random_str(32);
        $db = parent::get_db_connection();

        $sql = "INSERT INTO `phpsc_session` (`ip`,`hash`) VALUES(:ip,:hash)";
        $arr = array(':ip'=>parent::get_real_ip(), ':hash'=>$hash);

        $id = $db->run_sql($sql, $arr, 'lastInsertId');
        setcookie('phpsc_web', $id.'-'.$hash, 0);

        return true;
    }

    public function check_authenticated()
    {
        if (!isset($_COOKIE['phpsc_web'])) {
            $this->set_authenticated(false);
            return;
        }

        list($id, $hash) = explode('-', $_COOKIE['phpsc_web']);

        if (strlen($hash) != 32) {
            $this->set_authenticated(false);
            return;
        }

        $db = parent::get_db_connection();

        $sql = "SELECT * FROM `phpsc_session` WHERE `hash`=:hash LIMIT 1";
        $session = $db->run_sql($sql, array(':hash'=>$hash), 'fetch');

        if ($session['ip'] == parent::get_real_ip()) {
            $this->set_authenticated(true);
            return;
        }

        $this->set_authenticated(false);
        return;
    }


    private function random_str($length)
    {
        return substr(sha1(rand()), 0, $length);
    }

    public function logout()
    {
        list($id, $hash) = explode('-', $_COOKIE['phpsc_web']);

        setcookie('phpsc_web', false, -3600);
        $this->set_authenticated(false);

        $db = parent::get_db_connection();

        $sql = "DELETE FROM `phpsc_session` WHERE `hash`=:hash";
        $db->run_sql($sql, array(':hash'=>$hash), false);

        return true;
    }

    public function sanitize($input)
    {
        $content = strip_tags($input);
        $content = htmlspecialchars($content);
        $content = trim($content);

        return $content;
    }

    public function get_vault()
    {
        $db = parent::get_db_connection();

        $sql = 'SELECT * FROM `phpsc_vault` ORDER BY `id` DESC';

        return $db->run_sql($sql);
    }

    public function get_vault_item($id)
    {
        $db = parent::get_db_connection();

        $sql = 'SELECT * FROM `phpsc_vault` WHERE `id`=:id';

        return $db->run_sql($sql, array(':id'=>$id), 'fetch');
    }

    public function download_file($id)
    {
        $item = $this->get_vault_item($id);

        if (!$item) {
            return false;
        }

        $file = json_decode($item['file']);

        if (file_exists($file->phpsc_vault)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file->name).'.txt"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file->phpsc_vault));
            readfile($file->phpsc_vault);
            exit;
        }
    }
}
