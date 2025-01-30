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
        $this->user->email = $data->email;
        $this->user->username = $data->username;
        $this->user->password = $data->password;
        if($this->user->emailExists()) {
            return ["status" => "error", "message" => "Email already exists"];
        }
        if ($this->user->userExists()) {
            return ["status" => "error", "message" => "Username already exists"];
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
        if(!$this->user->userExists()) {
            return ["status" => "error", "message" => "User does not exist or was not confirmed."];
        }
        if($this->user->verifyPassword($data->password)) {
            return ["status" => "success", "message" => "Login successful"];
        }
        return ["status" => "error", "message" => "Invalid password"];
    }
}
