<?php

    class db
    {

        /**
         * @var null
         */
        public $dbc = NULL;
        /**
         * @var string
         */
        private $db_host = null;
        /**
         * @var string
         */
        private $db = null;
        /**
         * @var string
         */
        private $db_user = null;
        /**
         * @var string
         */
        private $db_pass = null;

		private $db_string = '';


        /**
         * @param null $db_host
         * @param null $db
         * @param null $db_user
         * @param null $db_pass
         */
        public function __construct($db_host = null, $db = null, $db_user = null, $db_pass = null)
        {
            if ( $db_host ) {
                $this->set_db_host($db_host);
            }
            if ( $db ) {
                $this->set_db($db);
            }
            if ( $db_user ) {
                $this->set_db_user($db_user);
            }
            if ( $db_pass ) {
                $this->set_db_pass($db_pass);
            }

            $this->settings = ['table' => '', 'limit' => 20, 'page' => 1, 'returns' => '*', 'leftjoin' => '', 'where' => ['1' => ['value' => '1', 'operator' => '=']], 'orderby' => '1'];
        }

        /**
         * @param string $db_host
         */
        public function set_db_host($db_host)
        {
            $this->db_host = $db_host;
        }

        /**
         * @param string $db
         */
        public function set_db($db)
        {
            $this->db = $db;
        }

        /**
         * @param string $db_user
         */
        public function set_db_user($db_user)
        {
            $this->db_user = $db_user;
        }

        /**
         * @param string $db_pass
         */
        public function set_db_pass($db_pass)
        {
            $this->db_pass = $db_pass;
        }

        /**
         * @return bool
         */
        public function get_dbc()
        {
            return $this->dbc;
        }

        /**
         * @param null $dbc
         */
        public function set_dbc($dbc)
        {
            $this->dbc = $dbc;
        }

		/**
		 * @return string
		 */
		public function get_db_string()
		{
			return $this->db_string;
		}

		/**
		 * @param string $db_string
		 */
		public function set_db_string($db_string)
		{
			$this->db_string = $db_string;
		}



        /**
         * @param        $sql
         * @param        $ar
         * @param string $return
         *
         * @return bool
         */
        public function run_sql($sql, $ar = [], $return = 'fetchAll')
        {
            if ( !is_object($this->dbc) ) {
				$this->connect();
            }

            $stmt = $this->dbc->prepare($sql);
            $stmt->execute($ar);

            if ( $return == 'fetch' ) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if ( $return == 'fetchAll' ) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            if ( $return == 'lastInsertId' ) {
                return $this->dbc->lastInsertId('id');
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
                $dbc = new PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db, $this->db_user, $this->db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
                //$dbc->exec("SET CHARACTER SET utf8");
                $this->set_dbc($dbc);

                return true;

            } catch (PDOException $ex) {

                return false;

            }
        }


    }