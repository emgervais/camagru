<?php
class AuthController {
    private $conn;
    private $user;

    public function __construct($db) {
        $this->conn = $db;
        $this->user = new User($db);
    }

    public function register($data) {
        if(empty($data->username) || empty($data->email) || empty($data->password)) {
            return array("message" => "Missing required fields");
        }

        $this->user->username = $data->username;
        $this->user->email = $data->email;
        $this->user->password = $data->password;

        if($this->user->emailExists()) {
            return array("message" => "Email already exists");
        }

        if($this->user->create()) {
            // Send verification email
            $this->sendVerificationEmail($this->user->email, $this->user->verification_token);
            return array("message" => "User registered successfully");
        }
        return array("message" => "Unable to register user");
    }

    private function sendVerificationEmail($email, $token) {
        $to = $email;
        $subject = "Email Verification";
        $message = "Please click this link to verify your email: http://yourdomain.com/verify.php?token=" . $token;
        $headers = "From: noreply@yourdomain.com";

        mail($to, $subject, $message, $headers);
    }
}

class Auth {
    public static function verifyToken() {
        $headers = apache_request_headers();
        if(!isset($headers['Authorization'])) {
            return false;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        return JWT::validate($token);
    }

    public static function getUserFromToken() {
        $headers = apache_request_headers();
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $payload = JWT::decode($token);
        return $payload->user_id;
    }
}