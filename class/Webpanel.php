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
    private $vault_size = 0;
    private $page = 1;
    private $total_pages = 1;

    /**
     * Webpanel constructor.
     */
    public function __construct()
    {
        parent::load_config();
        $this->check_authenticated();
        $this->check_page();
    }

    /**
     * @return int
     */
    public function get_vault_size()
    {
        return $this->vault_size;
    }

    /**
     * @param int $vault_size
     */
    public function set_vault_size($vault_size)
    {
        $this->vault_size = $vault_size;
    }

    /**
     * @return int
     */
    public function get_total_pages()
    {
        return $this->total_pages;
    }

    /**
     * @param int $total_pages
     */
    public function set_total_pages($total_pages)
    {
        $this->total_pages = $total_pages;
    }



    /**
     * @return int
     */
    public function get_page()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function set_page($page)
    {
        $this->page = $page;
    }


    private function check_page()
    {
        if (isset($_GET['phpsc_page'])) {
            $this->set_page((int)$_GET['phpsc_page']);
        }
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

    public function get_vault($status = false)
    {
        $db = parent::get_db_connection();

        $where = ' WHERE 1=1 ';

        if ($status) {
            switch ($status) {
                case 'blocked':
                    $where .= " AND `banip`.`status` ='blocked' ";
                    break;

                case 'pending':
                    $where .= " AND `banip`.`status` ='pending' ";
                    break;

                case 'unbanned':
                    $where .= " AND `banip`.`status` IS NULL ";
                    break;
            }
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS `vault`.*, `banip`.`status` FROM `phpsc_vault` AS `vault` '.
            ' LEFT JOIN `phpsc_banip` AS `banip` ON `banip`.`ip` = `vault`.`ip` ' .
            $where .
            ' ORDER BY `id` DESC ' .
            ' LIMIT ' . (($this->get_page()-1) * parent::get_action('web_perpage'))  . ', ' . parent::get_action('web_perpage');

        $results = $db->run_sql($sql);

        $sql = "SELECT FOUND_ROWS() AS total;";
        $total = $db->run_sql($sql);
        $this->set_vault_size($total[0]['total']);
        $this->set_total_pages(ceil($total[0]['total']/ parent::get_action('web_perpage')));

        return $results;
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

    public function ban_ip($ip)
    {
        $db = parent::get_db_connection();

        $sql = "INSERT INTO `phpsc_banip` (`ip`) VALUES( :ip ) ";

        $db->run_sql($sql, array(':ip'=>$ip), false);
    }
}
