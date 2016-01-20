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
 * Author: Skyblue Creations Ltd.
 *
 * Date: 1/19/2016
 * Time: 10:34 AM
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
        setcookie('phpsc_web', 1, 0);

        return true;
    }

    public function check_authenticated()
    {
        if (isset($_COOKIE['phpsc_web'])) {
            $this->set_authenticated(true);
        }
    }

    public function logout()
    {
        setcookie('phpsc_web', false, -3600);
        $this->set_authenticated(false);
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
