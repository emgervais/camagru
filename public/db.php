<?php
class Database {
    private $host = "mysql";
    private $conn;
    private $env;
    
    public function __construct() {
        $this->env = parse_ini_file('/var/www/.env');
        if ($this->env === false) {
            throw new Exception("Failed to parse .env file");
        }
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->env['MYSQL_DATABASE'],
                $this->env['MYSQL_USER'],
                $this->env['MYSQL_PASSWORD']
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
}