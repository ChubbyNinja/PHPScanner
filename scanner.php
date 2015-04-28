<?php
/**
 * Created for PHPScanner
 * User: Danny Hearnah
 * Author: ChubbyNinja
 * URL: https://github.com/ChubbyNinja/PHPScanner
 *
 * Date: 28/04/2015
 * Time: 13:23
 */

	require rtrim(dirname(__FILE__), '/') .'/definitions/definitions.php';


	function do_scan( $arr )
	{
		global $definitions;

		if( !$arr['tmp_name'] ) { return $arr; }

		$found = [];
		$content = file_get_contents( $arr['tmp_name'] );

		foreach( $definitions as $vun_id => $find )
		{
			if( stripos( $content, $find ) !== false )
			{
				$found[] = ['vun_id'=>$vun_id,'vun_string'=>$find];
			}
		}

		$arr['scan_results'] = 'OK';
		$arr['scan_details'] = [];

		if( $found )
		{
			$arr['error'] = 8;
			$arr['scan_results'] = 'PUP';
			$arr['scan_details'] = $found;

			/*
			 * append tmp name to stop upload script continuing
			 * we only append so the developer can decide if they
			 * want to continue based on the scan_details array
			 * All they require doing is removing _VIRUS_FOUND from
			 * the tmp_name.
			 *
			 */
			$arr['tmp_name'] .= '_VIRUS_FOUND';
		}

		return $arr;

	}

	function check_files( )
	{
		if( !isset($_FILES) ) {
			return;
		}

		foreach( $_FILES as $key=>$file )
		{

			// if the upload is a single file
			if( is_string($file['name']) )
			{
				$_FILES[$key] = do_scan( $file );
			}

			// if the upload is multiple files, fix php's awful array structure
			if( is_array( $file['name'] ) )
			{
				foreach( $file['name'] as $file_key=> $file_name )
				{
					$tmp = [];
					$tmp['tmp_name'] = $file['tmp_name'][$file_key];

					$tmp = do_scan( $tmp );

					foreach( $tmp as $res_key=>$res_data )
					{
						$_FILES[$key][$res_key][$file_key] = $res_data;
					}
				}
			}
		}
	}

	// trigger scanner
	check_files();