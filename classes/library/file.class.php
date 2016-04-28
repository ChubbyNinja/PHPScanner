<?php
/**
 * Created by PhpStorm.
 * User: chubbyninja
 * Date: 14/03/16
 * Time: 23:10
 */

namespace phpFUS\classes\library;

use phpFUS\Classes\Core\Error;
use phpFUS\Classes\Core\Request;

class file
{

    static $whitelist = [
        'image' => ['jpg','png'],
        'file'  => ['pdf','doc','docx']
    ];

    static $upload_path = 'uploads/';
    static $upload_dir = '/uploads/';

    /**
     * @return string
     */
    public static function getUploadPath()
    {
        return BASE_PATH . self::$upload_path;
    }

    /**
     * @param string $upload_path
     */
    public static function setUploadPath($upload_path)
    {
        self::$upload_path = $upload_path;
    }

    /**
     * @return string
     */
    public static function getUploadDir()
    {
        return self::$upload_dir;
    }

    /**
     * @param string $upload_dir
     */
    public static function setUploadDir($upload_dir)
    {
        self::$upload_dir = $upload_dir;
    }





    /**
     * @return array
     */
    public static function getWhitelist()
    {
        return self::$whitelist;
    }



    public static function returnExtension( $file )
    {
        return end( explode('.', $file ));
    }


    public static function trimFilename( $file ) {

        $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
        $file = mb_ereg_replace("([\.]{2,})", '', $file);

        return $file;

    }

    public static function upload( $type='image' ) {

        if( !Request::file('file') ) {
            Error::customError('No File set', 'No file uploaded',true);
        }

        $file = Request::file('file');

        $valid = false;
        foreach( self::getWhitelist()[ $type ] as $ext ) {
            if( self::returnExtension($file['name']) == $ext ) {
                $valid = true;
            }
        }

        if( !$valid ) {
            Error::customError('Invalid file', 'invalid file',true);
        }

        $filename = self::trimFilename( $file['name'] );

        $filename = self::returnUniqueFilename( $filename );

        $ok = move_uploaded_file( $file['tmp_name'], self::getUploadPath() . $filename );

        die( json_encode(['link'=>"/uploads/" . $filename]) );


    }

    private function returnUniqueFilename( $file, $orig=false, $ext=false, $inc=0 ) {

        $filename = $file;

        if( !$orig ) {
            $orig = current(explode('.', $file ));
        }

        $ext = self::returnExtension( $filename );

        if( file_exists( self::getUploadPath() . $filename ) ) {
            $inc++;
            $filename = $orig . '_' . $inc . '.' . $ext;
            $filename = self::returnUniqueFilename( $filename, $orig, $ext, $inc );
        }


        return $filename;
    }

}