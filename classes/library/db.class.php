<?php
namespace phpFUS\Classes\Library;

use phpFUS\Classes\Core\Error;


class db
{
    /**
     * @var null
     */
    static $dbc = NULL;
    /**
     * @var string
     */
    static $db_host = DB_HOST;
    /**
     * @var string
     */
    static $db = DB;
    /**
     * @var string
     */
    static $db_user = DB_USER;
    /**
     * @var string
     */
    static $db_pass = DB_PASS;

    static $db_string = '';


    /**

     */
    public function __construct()
    {
    }


    /**
     * @return bool
     */
    public function getDbc()
    {
        return self::$dbc;
    }

    /**
     * @param null $dbc
     */
    public function setDbc($dbc)
    {
        self::$dbc = $dbc;
    }


    /**
     * @param        $sql
     * @param        $ar
     * @param string $return
     *
     * @return mixed
     */
    public static function runQuery($sql, $ar = [], $return = 'fetch')
    {
        if ( !is_object(self::$dbc) ) {
            self::connect();
        }

        $stmt = self::$dbc->prepare($sql);
        $stmt->execute($ar);

        if ( $return == 'fetch' ) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if ( $return == 'fetchAll' ) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        if ( $return == 'lastInsertId' ) {
            return self::$dbc->lastInsertId('id');
        }

        if ($return == 'rowCount') {
            return $stmt->rowCount();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function connect()
    {
        try {
            $dbc = new \PDO("mysql:host=" . self::$db_host . ";dbname=" . self::$db, self::$db_user, self::$db_pass, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
            //$dbc->exec("SET CHARACTER SET utf8");
            self::setDbc($dbc);

            return true;

        } catch (\PDOException $ex) {

            Error::customError('DB Error','Could not connect to database: ' . $ex->getMessage(), true);

            return false;

        }
    }


    public static function dbParseJSONtoArray( $result, $key ) {

        $result[ $key ] = json_decode('[' . $result[$key]. ']', true );

        foreach( $result[ $key ] as $meta_key=>$meta ) {
            unset($result[ $key ][ $meta_key ]);

            if( !array_key_exists( $meta['slug'], $result[$key] ) ) {
                $result[ $key ][ $meta['slug']  ] = [];
            }

            $result[ $key ][ $meta['slug']  ][] = $meta['value'];

            $result[ $key ][ '_'.$meta['slug'] ][] = $meta;
        }

        return $result;

    }


}
