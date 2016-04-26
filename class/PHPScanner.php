<?php

    class PHPScanner
    {
        /**
         * @var array
         */
        private $definitions = array();
        /**
         * @var string
         */
        private $definitions_file = '';
        /**
         * @var string
         */
        private $definitions_url = '';
        /**
         * @var string
         */
        private $env_type = 'web';
        /**
         * @var bool
         */
        private $silent_mode = false;

        private $phpsc_version = '1.0.3';

        private $notify = array();
        private $action = array();
        private $notify_list = array();

        private $db_connection = null;

        /**
         *
         */
        public function __construct()
        {
            global $_FILES;

            $this->set_definitions_file(PHPSC_ROOT.'/definitions/definitions.php');
            $this->set_definitions_url('http://www.phpscanner.chubbyninja.co.uk/definitions/updater.php');

            $this->load_config();

            $this->check_env_type();

            // load definitions
            $this->load_definitions();

            // scan _FILES
            $this->check_files();

            if ($this->get_env_type() === 'cli') {
                $this->run_cli_mode();
            }

            if ($this->get_env_type() === 'web') {
                $this->check_web_panel();
            }
        }

        public function load_config()
        {
            $notify = $action = array();

            if (!isset($_SERVER['SERVER_NAME'])) {
                $_SERVER['SERVER_NAME'] = 'server';
            }

            require PHPSC_ROOT.'/conf.php';

            $notify_default = $notify;
            $action_default = $action;


            if (file_exists(PHPSC_ROOT .'/conf.custom.php')) {
                require PHPSC_ROOT .'/conf.custom.php';
            }

            $this->set_notify(array_merge($notify_default, $notify));
            $this->set_action(array_merge($action_default, $action));
        }

        /**
         * @return array
         */
        public function get_notify_list()
        {
            return $this->notify_list;
        }

        /**
         * @param array $notify_list
         */
        public function set_notify_list($notify_list)
        {
            $this->notify_list[] = $notify_list;
        }

        /**
         * @param $key
         *
         * @return array
         */
        public function get_notify($key)
        {
            return $this->notify[ $key ];
        }

        /**
         * @param array $notify
         */
        public function set_notify($notify)
        {
            $this->notify = $notify;
        }

        /**
         * @param $key
         *
         * @return array
         */
        public function get_action($key)
        {
            return $this->action[ $key ];
        }

        /**
         * @param array $action
         */
        public function set_action($action)
        {
            $this->action = $action;
        }

        private function check_web_panel()
        {
            if (isset($_GET['phpsc'])) {
                require PHPSC_ROOT.'/webpanel/index.php';
                die();
            }
        }

        /**
         * @return null
         */
        public function get_db_connection()
        {
            if (!$this->db_connection) {
                require PHPSC_ROOT.'/class/Db.php';
                $db = new db($this->get_action('mysql_host'), $this->get_action('mysql_db'), $this->get_action('mysql_user'), $this->get_action('mysql_pass'));
                $this->set_db_connection($db);

                return $this->get_db_connection();
            }

            return $this->db_connection;
        }

        /**
         * @param null $db_connection
         */
        public function set_db_connection($db_connection)
        {
            $this->db_connection = $db_connection;
        }

        /**
         *
         */
        private function run_cli_mode()
        {
            global $argv;

            if (!isset($argv[1])) {
                die('no argument set');
            }

            switch ($argv[1]) {
                case '-version':
                    $this->output_version();
                    break;

                case '-u':
                case '-update':
                    $this->update_definitions();
                    break;
                case '-su':
                case '-silent-update':
                    $this->set_silent_mode(true);
                    $this->update_definitions();
                    break;

                case '-help':
                    $this->output_commands();
                    break;

                case '-scan':
                    $this->cli_scan();
                    break;

                case 'cron-banip':
                    $this->ban_ip('cronjob');
                    break;
            }
        }

        public function get_real_ip()
        {
            if (!isset($_SERVER['REMOTE_ADDR'])) {
                return '0.0.0.0';
            }

            $ipAddress = $_SERVER['REMOTE_ADDR'];
            if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
                if (filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
                    $ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
                }
            }

            return $ipAddress;
        }

        /**
         * @return float
         */
        public function get_phpsc_version()
        {
            return $this->phpsc_version;
        }

        /**
         * @return bool
         */
        public function is_silent_mode()
        {
            return $this->silent_mode;
        }

        /**
         * @param bool $silent_mode
         */
        public function set_silent_mode($silent_mode)
        {
            $this->silent_mode = $silent_mode;
        }

        /**
         *
         */
        private function check_env_type()
        {
            if (PHP_SAPI === 'cli') {
                $this->set_env_type('cli');
            }
        }

        /**
         * @return bool
         */
        public function get_definitions_file()
        {
            return $this->definitions_file;
        }

        /**
         * @param bool $definitions_file
         */
        public function set_definitions_file($definitions_file)
        {
            $this->definitions_file = $definitions_file;
        }

        /**
         * @return bool
         */
        public function get_definitions_url()
        {
            return $this->definitions_url;
        }

        /**
         * @param bool $definitions_url
         */
        public function set_definitions_url($definitions_url)
        {
            $this->definitions_url = $definitions_url;
        }

        /**
         * @return string
         */
        public function get_env_type()
        {
            return $this->env_type;
        }

        /**
         * @param string $env_type
         */
        public function set_env_type($env_type)
        {
            $this->env_type = $env_type;
        }

        /**
         * @return array
         */
        public function get_definitions()
        {
            return $this->definitions;
        }

        /**
         * @param array $definitions
         */
        private function set_definitions($definitions)
        {
            $this->definitions = $definitions;
        }

        /**
         *
         */
        private function load_definitions()
        {
            $definitions = array();

            require $this->get_definitions_file();

            $this->set_definitions($definitions);
        }

        /**
         *
         */
        private function check_files()
        {
            if (!isset($_FILES) || empty($_FILES)) {
                return;
            }

            foreach ($_FILES as $key => $file) {

                // if the upload is a single file
                if (is_string($file[ 'name' ])) {
                    $_FILES[ $key ] = $this->do_scan($file, $key);
                } elseif (is_array($file[ 'name' ])) {
                    // multiple files

                    foreach ($file[ 'name' ] as $file_key => $file_name) {
                        $tmp = array();
                        $tmp[ 'tmp_name' ] = $file[ 'tmp_name' ][ $file_key ];
                        $tmp[ 'size' ] = $file['size'][$file_key];
                        $tmp[ 'name' ] = $file['name'][$file_key];

                        $tmp = $this->do_scan($tmp, $key, $file_key);

                        foreach ($tmp as $res_key => $res_data) {
                            $_FILES[ $key ][ $res_key ][ $file_key ] = $res_data;
                        }
                    }
                }
            }

            $this->trigger_notify();
        }

        /**
         * @param $arr
         * @param $key
         * @param bool $multi
         *
         * @return mixed
         */
        private function do_scan($arr, $key, $multi = false)
        {
            if (!$arr[ 'tmp_name' ]) {
                return $arr;
            }

            // size of upload exceeds our specified max_file_size variable in config
            if ($arr['size'] > $this->get_action('max_file_size')) {
                return $arr;
            }

            $found = array();
            if ($this->get_action('use_clamav')) {
                $found = $this->_do_clamav_scan($arr['tmp_name']);
            }

            if ($this->get_action('use_phpsc')) {
                $content = @file_get_contents($arr['tmp_name']);
                if (!$content) {
                    return $arr;
                }
                $found = $this->_do_scan($content, $found);
            }

            if ((count($found) >= $this->get_action('threshold')) || (count($found) && $this->get_action('use_clamav'))) {
                $notify_arr = $arr;

                switch ($this->get_action('level')) {
                    case 0:
                        // action level 0, do nothing but append scan results
                        $arr[ 'scan_results' ] = 'PUP';
                        $arr[ 'scan_details' ] = $found;
                        $notify_arr = $arr;

                        break;
                    default:
                    case 1:
                        // action level 1, quarantine file and append $_FILES array
                        $arr[ 'error' ] = 8;
                        $arr[ 'scan_results' ] = 'PUP';
                        $arr[ 'scan_details' ] = $found;
                        $arr[ 'phpsc_vault' ] = $arr['tmp_name'].'___PHPSCVAULT_'.date('d.m.Y..H.i.s');
                        rename($arr['tmp_name'], $arr['phpsc_vault']);
                        $notify_arr = $arr;

                        break;
                    case 2:
                        // action level 2, quarantine file and remove from $_FILES array

                        $arr[ 'phpsc_vault' ] = $arr['tmp_name'].'___PHPSCVAULT_'.date('d.m.Y..H.i.s');
                        rename($arr['tmp_name'], $arr['phpsc_vault']);
                        $notify_arr = $arr;

                        if ($multi !== false) {
                            foreach ($_FILES[ $key ] as $ikey => $inner) {
                                unset($_FILES[ $key ][ $ikey ][ $multi ]);
                            }
                        } else {
                            unset($_FILES[ $key ]);
                        }
                        $arr = array();

                        break;
                    case 3:
                        // action level 3, remove file and append $_FILES array
                        unlink($arr['tmp_name']);
                        $arr[ 'error' ] = 8;
                        $arr[ 'scan_results' ] = 'PUP';
                        $arr[ 'scan_details' ] = $found;
                        $arr[ 'tmp_name' ] = false;
                        $notify_arr = $arr;

                        break;
                    case 4:
                        // action level 4, remove file and remove from $_FILES array
                        unlink($arr['tmp_name']);
                        if ($multi !== false) {
                            foreach ($_FILES[ $key ] as $ikey => $inner) {
                                unset($_FILES[ $key ][ $ikey ][ $multi ]);
                            }
                        } else {
                            unset($_FILES[ $key ]);
                        }
                        $arr = array();

                        break;
                }

                $this->append_notify_list($notify_arr, $found);

                if ($this->get_action('mysql_enabled')) {

                    $server_details = '';
                    foreach($_SERVER as $key=>$val) {
                        $server_details .= $key.': ' . $val . "\n";
                    }

                    $db = $this->get_db_connection();
                    $sql = 'INSERT INTO `phpsc_vault` (`ip`,`file`,`threat`,`server_details`) VALUES(:ip,:file,:threat,:server_details)';

                    $sql_arr = array();
                    $sql_arr[':ip'] = $this->get_real_ip();
                    $sql_arr[':file'] = json_encode($arr);
                    $sql_arr[':threat'] = json_encode($found);
                    $sql_arr[':server_details'] = $server_details;

                    $db->run_sql($sql, $sql_arr, false);
                }

                if ($this->get_action('log_enabled')) {
                    if (!in_array($this->get_real_ip(), $this->get_action('ip_whitelist'))) {
                        $str = sprintf('%s: %s - %s'."\n", date('Y-m-d H:i:s'), $this->get_real_ip(), $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);

                        if (is_writable($this->get_action('log_location'))) {
                            $handle = fopen($this->get_action('log_location'), 'a');

                            if ($handle) {
                                fwrite($handle, $str);
                                fclose($handle);
                            }
                        }
                    }
                }

                if ($this->get_action('iptables')) {
                    if (!in_array($this->get_real_ip(), $this->get_action('ip_whitelist'))) {
                        $str = sprintf($this->get_action('iptables_string'), $this->get_real_ip());
                        //print($str);
                    }
                }
            } else {
                $arr[ 'scan_results' ] = 'OK';
                $arr[ 'scan_details' ] = array();
            }

            return $arr;
        }

        private function append_notify_list($arr, $found)
        {
            $arr['scan_results'] = $found;
            $this->set_notify_list($arr);
        }

        private function action_lvl_to_text()
        {
            switch ($this->get_action('level')) {
                case 0:
                    return 'No action Taken';
                    break;

                case 1:
                case 2:
                    return 'File Quarantined';
                    break;

                case 3:
                case 4:
                    return 'File Deleted';
                    break;
            }
        }

        private function trigger_notify()
        {
            if (!$this->get_notify_list()) {
                return;
            }

            $html = false;

            switch ($this->get_notify('level')) {

                case 0:
                    // notify level 0, do nothing
                    return;

                    break;

                case 1:
                    // notify level 1, email summary
                    ob_start();
                    ?>
					<html>
					<head>
						<title><?=$this->get_notify('subject')?></title>
					</head>
					<body>
					Potentially Unwanted Program uploaded on <?=$_SERVER['SERVER_NAME']?>.<br><br>

					Action Taken: <?=$this->action_lvl_to_text()?><br>
					</body>
					</html>
					<?php
                    $html = ob_get_clean();
                    break;

                case 2:
                    // notify level 2, email detailed
                    ob_start();
                    ?>
					<html>
					<head>
						<title><?=$this->get_notify('subject')?></title>
					</head>
					<body>
					Potentially Unwanted Program uploaded on <?=$_SERVER['SERVER_NAME']?>.<br><br>

					Action Taken: <?=$this->action_lvl_to_text()?><br>

					Details:<br>
<pre>
<?php
print_r($this->get_notify_list());
?>
</pre>
					Server:<br>
<pre>
<?php
print_r($_SERVER);
?>
</pre>

					</body>
					</html>
					<?php
                    $html = ob_get_clean();
                    break;
            }

            $headers = 'MIME-Version: 1.0'."\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
            $headers .= 'To: '.$this->get_notify('email')."\r\n";
            $headers .= 'From: '.$this->get_notify('from_email')."\r\n";
            $headers .= 'X-Mailer: PHP/'.phpversion();

            $sent = mail($this->get_notify('email'), $this->get_notify('subject'), $html, $headers);
        }

        /**
         * @param $content
         *
         * @return array
         */
        private function _do_scan($content, $found)
        {
            foreach ($this->get_definitions() as $vun_id => $find) {
                if (stripos($content, $find) !== false) {
                    $found[ ] = array('vun_id' => $vun_id, 'vun_string' => htmlspecialchars($find));
                }
            }

            return $found;
        }

        public function array_trim(&$item, $key)
        {
            $item = trim($item);
        }

        private function _do_clamav_scan($location)
        {
            $found = array();
            $result = shell_exec('clamdscan --no-summary --fdpass '.$location);
            $results = explode(' ', $result);
            array_walk($results, array($this, 'array_trim'));

            if (end($results) !== 'OK') {
                $found[ ] = array('vun_id' => 'clamav', 'vun_string' => $results[1]);
            }

            return $found;
        }

        /**
         * @param string $file
         *
         * @return array
         */
        public function manual_scan_file($file = '', $output = false)
        {
            if (!is_readable($file)) {
                if ($output) {
                    $this->output_status('ERROR: File not found.');

                    return;
                }

                return array('msg' => 'File not found.', 'status' => 'error');
            }

            // size of upload exceeds our specified max_file_size variable in config
            if (filesize($file) > $this->get_action('max_file_size')) {
                $this->output_status('ERROR: File too large.');

                return;
            }

            $found = array();
            if ($this->get_action('use_clamav')) {
                $found = $this->_do_clamav_scan($file);
            }

            if ($this->get_action('use_phpsc')) {
                $content = @file_get_contents($file);
                if (!$content) {
                    return false;
                }
                $found = $this->_do_scan($content, $found);
            }

            if ($found) {
                if ($output) {
                    $this->output_status('PUP: Thread detected.');
                    foreach ($found as $key => $val) {
                        $this->output_status('VUNID['.$val['vun_id'].'] '.$val['vun_string']);
                    }

                    return;
                }

                return array('msg' => 'PUP Found', 'found' => $found, 'status' => 'PUP');
            }

            if ($output) {
                $this->output_status('CLEAN');

                return;
            }

            return array('msg' => 'File clean', 'status' => 'OK');
        }

        /**
         * @param string $string
         *
         * @return array
         */
        public function manual_scan_string($string = '')
        {
            $found = $this->_do_scan($string, array());

            if ($found) {
                return array('msg' => 'PUP Found', 'found' => $found, 'status' => 'PUP');
            }

            return array('msg' => 'File clean', 'status' => 'OK');
        }

        /**
         *
         */
        public function cli_scan()
        {
            global $argv;

            $this->output_status('Running manual scan');
            $this->output_status('--------------------');
            $this->output_status(' ');

            if (!isset($argv[2])) {
                $this->output_status('argument 2 must be a file');
                die();
            }

            $this->manual_scan_file($argv[2], true);
        }


        public function ban_ip($type)
        {
            if ($type == 'cronjob') {
                $db = $this->get_db_connection();

                $sql = "SELECT * FROM `phpsc_banip` WHERE `status`='pending'";
                $ip_list = $db->run_sql($sql, array(), 'fetchAll');

                if (!$ip_list) {
                    die();
                }

                $iptables = $this->get_action('iptables_string');

                $whitelist = $this->get_action('ip_whitelist');

                foreach ($ip_list as $entry) {
                    /* if IP is in our whitelist, do not ban */
                    if (in_array($entry['ip'], $whitelist)) {
                        continue;
                    }

                    $string = sprintf($iptables, $entry['ip']);
                    $result = shell_exec($string);

                    if (!$result) {
                        $sql = "UPDATE `phpsc_banip` SET `status`='blocked' WHERE `id` = :id ";
                        $db->run_sql($sql, array(':id' => $entry['id']), false);
                    }
                }
            }
        }

        /**
         *
         */
        public function update_definitions()
        {
            $this->output_status('Updating definitions');
            $this->output_status('--------------------');
            $this->output_status($this->get_definitions_url());
            $this->output_status(' ');

            set_time_limit(0);
            $definitions = @file_get_contents($this->get_definitions_url());

            if (!$definitions) {
                $this->output_status('Download:       FAIL');
                die();
            }

            $this->output_status('Download:      OK');

            $definitions = gzinflate($definitions);

            $total = substr_count($definitions, '$definitions[ ]');

            ob_start();
            echo '<?php '."\n";
            echo $definitions;

            $new_list = ob_get_clean();

            $done = file_put_contents($this->get_definitions_file(), $new_list);

            if ($done) {
                $this->output_status('Updated:       OK');
                $this->output_status('Definitions:   '.$total);
            } else {
                $this->output_status('Updated:       FAIL'."\n");
                $this->output_status('Definitions file not writable!');
                $this->output_status('------------------------------');
                $this->output_status($this->get_definitions_file());
            }
        }

        private function output_version()
        {
            $this->output_status("\n");
            $this->output_status('##############################');
            $this->output_status('PHPScanner '.$this->get_phpsc_version().' By ChubbyNinja');
            $this->output_status('Author: chubbyninja.co.uk');
            $this->output_status('Support: https://github.com/ChubbyNinja/PHPScanner');
            $this->output_status('##############################');
            $this->output_status("\n");
        }

        private function output_commands()
        {
            $this->output_version();
            $this->output_status('Available Commands ');
            $this->output_status('-=-=-=-=-=-=-=-=-=-'."\n");

            $this->output_status(' -version		Returns version number and author information');
            $this->output_status(' -scan [filename]	Scans the given file');
            $this->output_status(' -update		Runs definitions update in verbose mode');
            $this->output_status(' -silent-update		Runs definitions update in silent mode');
            $this->output_status(' -help			Returns this list of commands');
            $this->output_status("\n");
            $this->output_status('Aliases');
            $this->output_status('-=-=-=-'."\n");
            $this->output_status(' -u		Alias of -update');
            $this->output_status(' -su		Alias of -silent-update');
            $this->output_status(' -su		Alias of -silent-update');
            $this->output_status("\n");
        }

        /**
         * @param $output
         */
        private function output_status($output)
        {
            if (!$this->is_silent_mode()) {
                echo $output."\n";
            }
        }
    }
