<?php
    class Database {
        public $connection;
        protected $severname = "db:3306";
        protected $username = "root";
        protected $password = "wordpress";
        protected $dbname = "hongphatkgpy2021";
        function __construct() {
            $this->connection = mysqli_connect($this->severname, $this->username, $this->password);
            mysqli_select_db($this->connection, $this->dbname);
            mysqli_query($this->connection, "SET NAMES 'utf8'");
            //$this->connection = new mysqli($this->severname,$this->username,$this->password,$this->dbname);
        }
    }
?>