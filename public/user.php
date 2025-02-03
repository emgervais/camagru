<?php

class user {
    private $conn;

    public $id;
    public $username;
    public $email;
    public $password;
    public $token;
    public $verification_token;
    public $is_verified;
    public $password_reset_token;
    public $notification;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO users" . "
                SET username=:username, 
                    email=:email, 
                    password=:password,
                    verification_token=:token";

        $stmt = $this->conn->prepare($query);

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->verification_token = 1;//bin2hex(random_bytes(50));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":token", $this->verification_token);
        return $stmt->execute();
    }

    public function emailExists($email) {
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }


    public function setPasswordResetToken($email, $token) {
        $query = "UPDATE users " . " 
                 SET password_reset_token = :token 
                 WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":email", $email);
        
        return $stmt->execute();
    }

    public function verifyResetToken($token) {
        $query = "SELECT id FROM users " . " 
                 WHERE password_reset_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function updatePassword($token, $password) {
        $query = "UPDATE users " . " 
                 SET password = :password, 
                     password_reset_token = NULL 
                 WHERE password_reset_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":token", $token);
        
        return $stmt->execute();
    }

    public function verifyEmailToken($token) {
        $query = "UPDATE users " . " 
                 SET is_verified = 1, 
                     verification_token = NULL 
                 WHERE verification_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        
        return $stmt->execute();
    }

    public function updateSettings($user_id, $settings) {
        $query = "UPDATE users " . " 
                 SET notification_enabled = :notifications 
                 WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":notifications", $settings->notifications);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    }
    
    public function userExists($username) {
        $query = "SELECT id FROM users WHERE username = ? AND is_verified = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function verifyPassword($password) {
        $query = "SELECT id, password FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result && password_verify($password, $result['password'])) {
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['username'] = $this->username;
            $_SESSION['logged_in'] = true;
            return true;
        }
        return false;
    }

    public function checkSession() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function logout() {
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600);
    }

    public function verifyToken($token) {
        $query = "SELECT id, username FROM users WHERE verification_token = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $token);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() > 0) {
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['logged_in'] = true;
            $query = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $token);
            return $stmt->execute();
        }
        return false;
    }
    public function verifyTokenPassword($token, $password) {
        $query = "SELECT id, username FROM users WHERE password_reset_token = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $token);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() > 0) {
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['logged_in'] = true;
            $query = "UPDATE users SET password = ?, password_reset_token = NULL WHERE password_reset_token = ?";
            $stmt = $this->conn->prepare($query);
            $password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(1, $password);
            $stmt->bindParam(2, $token);
            return $stmt->execute();
        }
        return false;
    }

    public function setResetToken($email) {
        $query = "UPDATE users SET password_reset_token = ? WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $token = 1;//bin2hex(random_bytes(50));
        $stmt->bindParam(1, $token);
        $stmt->bindParam(2, $email);
        $stmt->execute();
        return $token;
    }
    public function passwordValidation($password) {
        //contains uppercase, minimum 3 characters and one number
        return preg_match('/^(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{3,}$/', $password);
    }
    public function sanitize($data) {
        $cleaned = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        echo $cleaned;
        if (!preg_match('/^[a-zA-Z0-9_-]{1,20}$/', $cleaned)) {
            return false;
        }
        
        return $cleaned;
    }
    public function changeInfo($data) {
        $query = "UPDATE users SET ";

        if(isset($data->password)) {
            if(!$this->passwordValidation($data->password))
                return ["status" => "error", "message" => "Password must contain at least 3 characters, one uppercase letter and one number"];
            $query .= "password = ?, ";
        }
        if(isset($data->email)) {
            if($this->emailExists($data->email) || !filter_var($data->email, FILTER_VALIDATE_EMAIL))
                return ["status" => "error", "message" => "Email already exists or invalid"];
            $query .= "email = ?, ";
        }
        if(isset($data->username)) {
            $username = $this->sanitize($data->username);
            if(!$username || $this->userExists($data->username))
                return ["status" => "error", "message" => "Username already exists or invalid"];
            $query .= "username = ?, ";
        }
        $query = substr($query, 0, -2);
        $query .= " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $i = 1;
        if(isset($data->password)) {
            $pass = password_hash($data->password, PASSWORD_DEFAULT);
            $stmt->bindParam($i, $pass);
            $i++;
        }
        if(isset($data->email)) {
            $stmt->bindParam($i, $data->email);
            $i++;
        }
        if(isset($data->username)) {
            $stmt->bindParam($i, $username);
            $i++;
        }
        $stmt->bindParam($i, $_SESSION['user_id']);
        if ($stmt->execute())
            return ["status" => "success", "message" => "User info updated"];
        return ["status" => "error", "message" => "Failed to update user info"];
    }
}