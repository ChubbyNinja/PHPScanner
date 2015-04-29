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

		private $definitions_file = false;
		private $definitions_url = false;

		/**
		 *
		 */
		function __construct() {

			global $_FILES;

			$this->definitions_file = rtrim( dirname( __FILE__ ), '/' ) . '/../definitions/definitions.php';
			$this->definitions_url = 'http://www.phpscanner.chubbyninja.co.uk/definitions/updater.php';

			// load definitions
			$this->load_definitions();

			// scan _FILES
			$this->check_files();

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

			require $this->definitions_file;

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


		public function update_definitions( )
		{
			$definitions = file_get_contents( $this->definitions_url );

			$definitions = gzinflate( $definitions );

			ob_start();
			echo '<?php ' . "\n";
			echo $definitions;

			$new_list = ob_get_clean();

			file_put_contents( $this->definitions_file, $new_list );
		}


	}