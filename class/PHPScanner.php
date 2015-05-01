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

		/**
		 *
		 */
		function __construct() {

			global $_FILES;

			$this->set_definitions_file( rtrim( dirname( __FILE__ ), '/' ) . '/../definitions/definitions.php' );
			$this->set_definitions_url( 'http://www.phpscanner.chubbyninja.co.uk/definitions/updater.php' );

			$this->check_env_type();

			// load definitions
			$this->load_definitions();

			// scan _FILES
			$this->check_files();

			if( $this->get_env_type() == 'cli' ) {
				$this->run_cli_mode();
			}

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
					echo 'PHPScanner v0.1 Build: 29/04/2015 13:35';
					break;

				case '-u':
				case '-update':
					$this->update_definitions();
					break;
				case '-su':
				case '-silent-update':
					$this->set_silent_mode( true );
					$this->update_definitions( );
					break;
			}
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
					$_FILES[ $key ] = $this->do_scan( $file );

				} elseif ( is_array( $file[ 'name' ] ) ) {
					// multiple files

					foreach ( $file[ 'name' ] as $file_key => $file_name ) {
						$tmp               = array( );
						$tmp[ 'tmp_name' ] = $file[ 'tmp_name' ][ $file_key ];

						$tmp = $this->do_scan( $tmp );

						foreach ( $tmp as $res_key => $res_data ) {
							$_FILES[ $key ][ $res_key ][ $file_key ] = $res_data;
						}
					}
				}
			}
		}

		/**
		 * @param $arr
		 *
		 * @return mixed
		 */
		private function do_scan( $arr ) {
			if ( ! $arr[ 'tmp_name' ] ) {
				return $arr;
			}

			$content = file_get_contents( $arr[ 'tmp_name' ] );

			$found = $this->_do_scan( $content );

			$arr[ 'scan_results' ] = 'OK';
			$arr[ 'scan_details' ] = array( );

			if ( $found ) {
				$arr[ 'error' ]        = 8;
				$arr[ 'scan_results' ] = 'PUP';
				$arr[ 'scan_details' ] = $found;

				/*
				 * append tmp name to stop upload script continuing
				 * we only append so the developer can decide if they
				 * want to continue based on the scan_details array
				 * All they require doing is removing _VIRUS_FOUND from
				 * the tmp_name.
				 *
				 */
				$arr[ 'tmp_name' ] .= '_VIRUS_FOUND';
			}

			return $arr;
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