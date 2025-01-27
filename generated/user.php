<?php
class User {
    private $conn;
    private $table = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $verification_token;
    public $is_verified;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . "
                SET username=:username, 
                    email=:email, 
                    password=:password,
                    verification_token=:token";

        $stmt = $this->conn->prepare($query);

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->verification_token = bin2hex(random_bytes(50));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":token", $this->verification_token);

        return $stmt->execute();
    }

    public function emailExists() {
        $query = "SELECT id, password FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    private $conn;
    private $table = "users";

    public function setPasswordResetToken($email, $token) {
        $query = "UPDATE " . $this->table . " 
                 SET password_reset_token = :token 
                 WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":email", $email);
        
        return $stmt->execute();
    }

    public function verifyResetToken($token) {
        $query = "SELECT id FROM " . $this->table . " 
                 WHERE password_reset_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function updatePassword($token, $password) {
        $query = "UPDATE " . $this->table . " 
                 SET password = :password, 
                     password_reset_token = NULL 
                 WHERE password_reset_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":token", $token);
        
        return $stmt->execute();
    }

    public function verifyEmailToken($token) {
        $query = "UPDATE " . $this->table . " 
                 SET is_verified = 1, 
                     verification_token = NULL 
                 WHERE verification_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        
        return $stmt->execute();
    }

    public function updateSettings($user_id, $settings) {
        $query = "UPDATE " . $this->table . " 
                 SET notification_enabled = :notifications 
                 WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":notifications", $settings->notifications);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    }
    
}