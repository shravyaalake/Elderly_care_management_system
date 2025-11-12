<?php
class DatabaseConnection {
    private $host = 'localhost';
    private $username = '';
    private $password = '';
    private $database = 'dbms';
    private $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function prepare($query) {
        return $this->conn->prepare($query);
    }

    public function query($query) {
        return $this->conn->query($query);
    }

    public function close() {
        $this->conn->close();
    }
}

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, 'error.log');
}
