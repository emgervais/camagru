<?php

class user {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($username, $email, $password) {
        $query = "INSERT INTO users (username, email, password, verification_token) VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        $password = password_hash($password, PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(50));

        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $password);
        $stmt->bindParam(4, $verification_token);
        if(!$stmt->execute())
            return 0;
        return $verification_token;
    }

    public function emailExists($email) {
        $query = "SELECT id FROM users WHERE email = ?";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function setPasswordResetToken($email, $token) {
        $query = "UPDATE users SET password_reset_token = ? WHERE email = ?";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $token);
        $stmt->bindParam(2, $email);
        
        return $stmt->execute();
    }

    public function verifyResetToken($token) {
        $query = "SELECT id FROM users WHERE password_reset_token = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $token);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function updatePassword($token, $password) {
        $query = "UPDATE users SET password = ?, password_reset_token = NULL WHERE password_reset_token = ?";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $password);
        $stmt->bindParam(2, $token);
        
        return $stmt->execute();
    }

    public function verifyEmailToken($token) {
        $query = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $token);
        
        return $stmt->execute();
    }
    
    public function userExists($username) {
        $query = "SELECT id FROM users WHERE username = ? AND is_verified = 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $username);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function verifyPassword($username, $password) {
        $query = "SELECT id, password FROM users WHERE username = ?";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result && password_verify($password, $result['password'])) {
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;

            return true;
        }

        return false;
    }

    public function checkSession() {
        return (isset($_SESSION) && isset($_SESSION['logged_in']) && $_SESSION['logged_in']) === true;
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

        $token = bin2hex(random_bytes(50));
        $stmt->bindParam(1, $token);
        $stmt->bindParam(2, $email);
        $stmt->execute();

        return $token;
    }

    public function passwordValidation($password) {
        return preg_match('/^(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{7,}$/', $password);
    }

    public function sanitize($data) {
        $cleaned = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        if (!preg_match('/^[a-zA-Z0-9_-]{1,20}$/', $cleaned)) {
            return false;
        }
        
        return $cleaned;
    }

    public function changeInfo($data) {
        $query = "UPDATE users SET ";

        if(isset($data['password']) && $data['password']) {
            if(!$this->passwordValidation($data['password']))
                return ["status" => "error", "message" => "Password must contain at least 3 characters, one uppercase letter and one number"];
            $query .= "password = ?, ";
        }

        if(isset($data['email']) && $data['email']) {
            if($this->emailExists($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL))
                return ["status" => "error", "message" => "Email already exists or invalid"];
            $query .= "email = ?, ";
        }

        if(isset($data['username']) && $data['username']) {
            $username = $this->sanitize($data['username']);
            if(!$username || $this->userExists($data['username']))
                return ["status" => "error", "message" => "Username already exists or invalid"];
            $query .= "username = ?, ";
        }
        if ($query === "UPDATE users SET ")
        return ["status" => "error", "message" => "Please enter data"];
        $query = substr($query, 0, -2);
        $query .= " WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $i = 1;
        if(isset($data['password']) && $data['password']) {
            $pass = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam($i, $pass);
            $i++;
        }

        if(isset($data['email']) && $data['email']) {
            $stmt->bindParam($i, $data['email']);
            $i++;
        }

        if(isset($data['username']) && $data['username']) {
            $stmt->bindParam($i, $username);
            $i++;
        }

        $stmt->bindParam($i, $_SESSION['user_id']);
        if ($stmt->execute())
            return ["status" => "success", "message" => "User info updated"];

        return ["status" => "error", "message" => "Failed to update user info"];
    }
}