<?php

	/**
	 * Created for PHPScanner
	 * User: Danny Hearnah
	 * Author: ChubbyNinja
	 * URL: https://github.com/ChubbyNinja/PHPScanner
	 *
	 * Date: 28/04/2015
	 * Time: 15:30
	 */
	class PHPScanner {

		/**
		 * @var array
		 */
		private $definitions = array( );
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


		private $phpsc_version = '1.0';


		private $notify = array( );
		private $action = array( );

		private $notify_list = array( );

		/**
		 *
		 */
		function __construct() {

			global $_FILES;

			$this->set_definitions_file( PHPSC_ROOT . '/definitions/definitions.php' );
			$this->set_definitions_url( 'http://www.phpscanner.chubbyninja.co.uk/definitions/updater.php' );

			$this->load_config();

			$this->check_env_type();

			// load definitions
			$this->load_definitions();

			// scan _FILES
			$this->check_files();

			if( $this->get_env_type() == 'cli' ) {
				$this->run_cli_mode();
			}

		}


		private function load_config() {

			$notify = $action = array();

			require_once PHPSC_ROOT . '/conf.php';

			/**
			 * WARNING: YOU SHOULD NOT EDIT THIS FUNCTION - EDIT CONFIG IN conf.php
			 */
			$notify_default = array( 'level'=>1, 'email'=>'root@localhost','subject'=> 'PUP Found on ' . $_SERVER['SERVER_NAME'] );
			$action_default = array( 'level'=>1, 'iptables'=>false, 'iptables_string'=>'iptables -I INPUT -s %s -j DROP','threshold'=>3);

			$this->set_notify( array_merge( $notify_default, $notify ) );
			$this->set_action( array_merge( $action_default, $action ) );
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
		 * @return array
		 */
		public function get_notify( $key )
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
		 * @return array
		 */
		public function get_action( $key )
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




		/**
		 *
		 */
		private function run_cli_mode()	{
			global $argv;

			if( !isset( $argv[1] ) )
			{
				die( 'no argument set' );
			}

			switch( $argv[1] )
			{
				case '-version':
					$this->output_version();
					break;

				case '-u':
				case '-update':
					$this->update_definitions();
					break;
				case '-su':
				case '-silent-update':
					$this->set_silent_mode( true );
					$this->update_definitions();
					break;

				case '-help':

					$this->output_commands();

					break;
			}
		}


		public function get_real_ip()
		{
			$ipAddress = $_SERVER['REMOTE_ADDR'];
			if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
				$ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
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
		 * @return boolean
		 */
		public function is_silent_mode() {
			return $this->silent_mode;
		}

		/**
		 * @param boolean $silent_mode
		 */
		public function set_silent_mode( $silent_mode ) {
			$this->silent_mode = $silent_mode;
		}


		/**
		 *
		 */
		private function check_env_type() {
			if ( PHP_SAPI == 'cli' ) {
				$this->set_env_type( 'cli' );
			}
		}

        /**
         * @return boolean
         */
		public function get_definitions_file() {
			return $this->definitions_file;
		}

		/**
		 * @param boolean $definitions_file
		 */
		public function set_definitions_file( $definitions_file ) {
			$this->definitions_file = $definitions_file;
		}

		/**
		 * @return boolean
		 */
		public function get_definitions_url() {
			return $this->definitions_url;
		}

		/**
		 * @param boolean $definitions_url
		 */
		public function set_definitions_url( $definitions_url ) {
			$this->definitions_url = $definitions_url;
		}

		/**
		 * @return string
		 */
		public function get_env_type() {
			return $this->env_type;
		}

		/**
		 * @param string $env_type
		 */
		public function set_env_type( $env_type ) {
			$this->env_type = $env_type;
		}


		/**
		 * @return array
		 */
		public function get_definitions() {
			return $this->definitions;
		}

		/**
		 * @param array $definitions
		 */
		private function set_definitions( $definitions ) {
			$this->definitions = $definitions;
		}

		/**
		 *
		 */
		private function load_definitions() {
			$definitions = array( );

			require $this->get_definitions_file();

			$this->set_definitions( $definitions );
		}


		/**
		 *
		 */
		private function check_files() {
			if ( ! isset( $_FILES ) ) {
				return;
			}

			foreach ( $_FILES as $key => $file ) {

				// if the upload is a single file
				if ( is_string( $file[ 'name' ] ) ) {
					$_FILES[ $key ] = $this->do_scan( $file, $key );

				} elseif ( is_array( $file[ 'name' ] ) ) {
					// multiple files

					foreach ( $file[ 'name' ] as $file_key => $file_name ) {
						$tmp               = array( );
						$tmp[ 'tmp_name' ] = $file[ 'tmp_name' ][ $file_key ];

						$tmp = $this->do_scan( $tmp, $key, $file_key );

						foreach ( $tmp as $res_key => $res_data ) {
							$_FILES[ $key ][ $res_key ][ $file_key ] = $res_data;
						}
					}
				}
			}

			$this->trigger_notify( );
		}

		/**
		 * @param $arr
		 *
		 * @return mixed
		 */
		private function do_scan( $arr, $key, $multi=false ) {
			if ( ! $arr[ 'tmp_name' ] ) {
				return $arr;
			}

			$content = file_get_contents( $arr[ 'tmp_name' ] );

			$found = $this->_do_scan( $content );


			if ( count($found) >= $this->get_action('threshold') ) {

				$this->append_notify_list( $arr, $found );

				switch( $this->get_action('level') )
				{
					case 0:
						// actin level 0, do nothing but append scan results
						$arr[ 'scan_results' ] = 'PUP';
						$arr[ 'scan_details' ] = $found;
						break;
					default:
					case 1:
						// action level 1, quarantine file and append $_FILES array
						$arr[ 'error' ] = 8;
						$arr[ 'scan_results' ] = 'PUP';
						$arr[ 'scan_details' ] = $found;
						$arr[ 'phpsc_vault' ] = $arr['tmp_name'] . '___PHPSCVAULT_' . date('d.m.Y..H.i.s');
						rename( $arr['tmp_name'], $arr['phpsc_vault']  );
						break;
					case 2:
						// action level 2, quarantine file and remove from $_FILES array
						rename( $arr['tmp_name'], $arr['tmp_name'] . '___PHPSCVAULT_' . date('d.m.Y..H.i.s') );
						if( $multi !== false )
						{
							foreach( $_FILES[ $key ] as $ikey=>$inner )
							{
								unset( $_FILES[ $key ][ $ikey ][ $multi ] );
							}
						} else {
							unset($_FILES[ $key ]);
						}
						$arr = array();

						break;
					case 3:
						// action level 3, remove file and append $_FILES array
						unlink( $arr['tmp_name'] );
						$arr[ 'error' ] = 8;
						$arr[ 'scan_results' ] = 'PUP';
						$arr[ 'scan_details' ] = $found;
						$arr[ 'tmp_name' ] = false;
						break;
					case 4:
						// action level 4, remove file and remove from $_FILES array
						unlink( $arr['tmp_name'] );
						if( $multi !== false )
						{
							foreach( $_FILES[ $key ] as $ikey=>$inner )
							{
								unset( $_FILES[ $key ][ $ikey ][ $multi ] );
							}
						} else {
							unset($_FILES[ $key ]);
						}
						$arr = array();

						break;
				}

				if( $this->get_action( 'iptables' ) )
				{
					$str = sprintf( $this->get_action('iptables_string'), $this->get_real_ip() );
					print($str);//replace with exec...
				}

			} else {
				$arr[ 'scan_results' ] = 'OK';
				$arr[ 'scan_details' ] = array( );
			}

			return $arr;
		}

		private function append_notify_list( $arr, $found ) {
			$arr['scan_results'] = $found;
			$this->set_notify_list( $arr );
		}

		private function action_lvl_to_text( )
		{
			switch( $this->get_action('level') )
			{
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

		private function trigger_notify( ) {
			if( empty( $this->get_notify_list() ) )
			{
				return;
			}

			$html = false;

			switch( $this->get_notify('level') )
			{


				case 0:
					// notify level 0, do nothing
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
					Potentially Unwanted Program uploaded on <?=$_SERVER['SERVER_NAME']?>.

					Action Taken: <?=$this->action_lvl_to_text()?>
					</body>
					</html>
					<?php
					$html = ob_get_clean();
					break;


				case 2:
					// notify level 1, email summary

					ob_start();
					?>
					<html>
					<head>
						<title><?=$this->get_notify('subject')?></title>
					</head>
					<body>
					Potentially Unwanted Program uploaded on <?=$_SERVER['SERVER_NAME']?>.

					Action Taken: <?=$this->action_lvl_to_text()?>

					Details:
					<?php
					print_r( $this->get_notify_list() );
					?>

					Server:
					<?php
					print_r($_SERVER);
					?>

					</body>
					</html>
					<?php
					$html = ob_get_clean();
					break;
			}

			$headers = 'From: ' . $this->get_notify('from_email') . "\r\n" .
				'Reply-To: ' . $this->get_notify('from_email') . "\r\n" .
				'X-Mailer: PHP/' . phpversion();

			$sent = mail( $this->get_notify('email'), $this->get_notify('subject'), $html, $headers );

		}

		/**
		 * @param $content
		 *
		 * @return array
		 */
		private function _do_scan( $content ) {
			$found = array( );
			foreach ( $this->get_definitions() as $vun_id => $find ) {
				if ( stripos( $content, $find ) !== false ) {
					$found[ ] = array( 'vun_id' => $vun_id, 'vun_string' => $find );
				}
			}

			return $found;
		}

		/**
		 * @param string $file
		 *
		 * @return array
		 */
		public function manual_scan_file( $file = '' ) {
			if ( ! is_readable( $file ) ) {

				return array( 'msg' => 'File not found.', 'status' => 'error' );

			}

			$content = file_get_contents( $file );
			$found   = $this->_do_scan( $content );

			if ( $found ) {
				return array( 'msg' => 'PUP Found', 'found' => $found, 'status' => 'PUP' );
			}

			return array( 'msg' => 'File clean', 'status' => 'OK' );
		}

		/**
		 * @param string $string
		 *
		 * @return array
		 */
		public function manual_scan_string( $string = '' ) {
			$found = $this->_do_scan( $string );

			if ( $found ) {
				return array( 'msg' => 'PUP Found', 'found' => $found, 'status' => 'PUP' );
			}

			return array( 'msg' => 'File clean', 'status' => 'OK' );
		}


		/**
		 *
		 */
		public function update_definitions( ) {

			$this->output_status( 'Updating definitions' );
			$this->output_status( '--------------------' );
			$this->output_status( $this->get_definitions_url() );
			$this->output_status( ' ' );

			set_time_limit(0);
			$definitions = file_get_contents( $this->get_definitions_url() );

			if( !$definitions )
			{
				$this->output_status( 'Download:       FAIL' );
				die();
			}

			$this->output_status( 'Download:      OK' );

			$definitions = gzinflate( $definitions );

			$total = substr_count( $definitions, '$definitions[ ]' );


			ob_start();
			echo '<?php ' . "\n";
			echo $definitions;

			$new_list = ob_get_clean();

			$done = file_put_contents( $this->get_definitions_file(), $new_list );

			if( $done )
			{
				$this->output_status( 'Updated:       OK' );
				$this->output_status( 'Definitions:   ' . $total );
			} else {
				$this->output_status( 'Updated:       FAIL' . "\n" );
				$this->output_status( 'Definitions file not writable!' );
				$this->output_status( '------------------------------' );
				$this->output_status( $this->get_definitions_file() );
			}
		}



		private function output_version() {
			$this->output_status( 'PHPScanner ' . $this->get_phpsc_version() . ' By ChubbyNinja' );
			$this->output_status( 'chubbyninja.co.uk' );
		}

		private function output_commands() {

			$this->output_version();
			$this->output_status( '---------------' );
			$this->output_status('Available Commands: ' . "\n");

			$this->output_status('-version			Returns version number and author information');
			$this->output_status('-update				Runs definitions update in verbose mode');
			$this->output_status('-silent-update		Runs definitions update in silent mode');
			$this->output_status('-help				Returns this list of commands');
			$this->output_status("\n");
			$this->output_status('-u					Alias of -update');
			$this->output_status('-su					Alias of -silent-update');

		}

		/**
		 * @param $output
		 */
		private function output_status( $output ) {
			if( !$this->is_silent_mode() )
			{
				echo $output . "\n";
			}
		}


	}
