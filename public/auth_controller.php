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
            return ["status" => "success", "message" => "User created"];
        }
        return ["status" => "error", "message" => "Failed to create user"];
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