<?php
class Database {
    private $host = 'localhost';
    private $username = 'root';  // Update with your MySQL username
    private $password = 'password';      // Update with your MySQL password
    private $database = 'dbms';  // Update with your database name
    public $conn;

    public function __construct() {
        $this->getConnection();
    }

    public function getConnection() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            return $this->conn;
        } catch(PDOException $e) {
            // Improved error handling
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function closeConnection() {
        $this->conn = null;
    }
}

// Usage in your script
try {
    $database = new Database();
    $conn = $database->conn;  // Now $conn will be available
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
