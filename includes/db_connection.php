<?php
class db_connection {
    private $host = 'localhost';
    private $username = '';
    private $password = '';
    private $database = '';
    public $conn;
    
    public function __construct() {
        // Create connection using MySQLi
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
    
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    
    /**
     * Get the database connection
     * @return mysqli Connection object
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Close the database connection
     */
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Execute a prepared statement query
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return mysqli_stmt Prepared statement
     */
    public function prepareStatement($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $this->conn->error);
        }

        // Bind parameters if provided
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Default to string type
            $stmt->bind_param($types, ...$params);
        }

        return $stmt;
    }

    /**
     * Execute a select query
     * @param string $query SQL select query
     * @param array $params Query parameters
     * @return array Result set
     */
    public function select($query, $params = []) {
        $stmt = $this->prepareStatement($query, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }
}
   

?>