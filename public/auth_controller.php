<?php

include "user.php";
class AuthController {
    private $conn;
    public User $user;

    public function __construct(
        private $db
    ) {
        $this->conn = $db;
        $this->user = new User($db);
    }

    public function register($data) {
        if(!$data || !isset($data->email) || !isset($data->username) || !isset($data->password))
            return ["status" => "error", "message" => "Missing data"];
        $this->user->email = $data->email;
        $this->user->username = $this->user->sanitize($data->username);
        $this->user->password = $data->password;
        if($this->user->emailExists($data->email) ||  !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            return ["status" => "error", "message" => "Email already exists or invalid"];
        }
        if (!$this->user->username || $this->user->userExists($data->username)) {
            return ["status" => "error", "message" => "Username already exists or invalid"];
        }
        if(!$this->user->passwordValidation($data->password)) {
            return ["status" => "error", "message" => "Password must contain at least 3 characters, one uppercase letter and one number"];
        }
        if($this->user->create()) {
            $to = $this->user->email;
            $subject = "Email Verification";
            $token = $this->user->verification_token;
            $message = "<html><body>";
            $message .= "<p>Please verify your email by clicking this link:</p>";
            $message .= "<p><a href='http://localhost:8080/api/verify?token=" . $token . "'>Click here to verify your email</a></p>";
            $message .= "</body></html>";
            $headers = array(
                'From' => 'Camagru <camagru.egerv@gmail.com>',
                'Reply-To' => 'Camagru <camagru.egerv@gmail.com>',
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/html; charset=UTF-8',
            );
            
            // mail($to, $subject, $message, $headers);
            return ["status" => "success", "message" => "email sent"];
        }
        return ["status" => "error", "message" => "Failed to create user"];
    }

    public function verifyToken($token) {
        if($token && $this->user->verifyToken($token)) {
            return true;
        }
        return false;
    }

    public function changePassword($data) {
        if($data && isset($data->token) && isset($data->password) && $this->user->verifyTokenPassword($data->token, $data->password)) {
            return true;
        }
        return false;
    }
    public function login($data) {
        $this->user->username = $data->username;
        if(!$this->user->userExists($data->username)) {
            return ["status" => "error", "message" => "User does not exist or was not confirmed."];
        }
        if($this->user->verifyPassword($data->password)) {
            return ["status" => "success", "message" => "Login successful"];
        }
        return ["status" => "error", "message" => "Invalid password"];
    }
    public function forgotPassword($data) {
        if($data && isset($data->email) && $this->user->emailExists($data->email)) {
            $token = $this->user->setResetToken($data->email);
            $to = $data->email;
            $subject = "Password Reset";
            $message = "<html><body>";
            $message .= "<p>Please reset your password by clicking this link:</p>";
            $message .= "<p><a href='http://localhost:8080/reset?token=" . $token . "'>Click here to reset your password</a></p>";
            $message .= "</body></html>";
            $headers = array(
                'From' => 'Camagru <camagru.egerv@gmail.com>',
                'Reply-To' => 'Camagru <camagru.egerv@gmail.com>',
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/html; charset=UTF-8',
            );
            // mail($to, $subject, $message, $headers);
            return true;
        }
        return false;
    }
    public function changeInfo($data) {
        if(!$data)
            return ["status" => "error", "message" => "No data provided"];
        return $this->user->changeInfo($data);
    }
    public function getPosts() {
        $query = "SELECT * FROM posts";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute() ? ["status" => "success", "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)] : ["status" => "error", "message" => "Failed to get posts"];
    }
    public function like($data) {
        if (!isset($_SESSION['user_id'])) {
            return ["status" => "error", "message" => "Please login to like posts"];
        }
        if (!$data || !isset($data->id) || !is_numeric($data->like))
            return ["status" => "error", "message" => "Missing data"];
        $query = $data->like ? "UPDATE posts SET likes = likes + 1 WHERE id = ?" : "UPDATE posts SET likes = likes - 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data->id);
        return $stmt->execute() ? ["status" => "success", "message" => "Post liked"] : ["status" => "error", "message" => "Failed to like post"];
    }
    public function comment($data) {
        if (!isset($_SESSION['user_id'])) {
            return ["status" => "error", "message" => "Please login to comment"];
        }
        if (!$data || !isset($data->id))
            return ["status" => "error", "message" => "Missing data"];
        $query = "select * from comments where post_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data->id);
        return $stmt->execute() ? ["status" => "success", "comments" => $stmt->fetchAll(PDO::FETCH_ASSOC)] : ["status" => "error", "message" => "Failed fetch comments"];
    }
    public function sendComment($data) {
        if (!isset($_SESSION['user_id'])) {
            return ["status" => "error", "message" => "Please login to comment"];
        }
        if (!$data || !isset($data->id) || !isset($data->comment))
            return ["status" => "error", "message" => "Missing data"];
        $query = "INSERT INTO comments (post_id, user_id, comment, username) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data->id);
        $stmt->bindParam(2, $_SESSION['user_id']);
        $stmt->bindParam(3, $data->comment);
        $stmt->bindParam(4, $_SESSION['username']);
        return $stmt->execute() ? ["status" => "success", "message" => "Comment added"] : ["status" => "error", "message" => "Failed to add comment"];
    }
}
