<?php
class UserProfileController {
    private $conn;
    private $user;

    public function __construct($db) {
        $this->conn = $db;
        $this->user = new User($db);
    }

    public function updateProfile($data, $user_id) {
        if(isset($data->username)) {
            $this->user->updateUsername($user_id, $data->username);
        }
        if(isset($data->email)) {
            $this->user->updateEmail($user_id, $data->email);
        }
        if(isset($data->notification_enabled)) {
            $this->user->updateNotificationPreference($user_id, $data->notification_enabled);
        }
        return ["status" => "success", "message" => "Profile updated"];
    }
}

<?php
class UserController {
    private $conn;
    private $user;
    private $emailService;

    public function __construct($db) {
        $this->conn = $db;
        $this->user = new User($db);
        $this->emailService = new EmailService($db);
    }

    public function updateProfile($data, $user_id) {
        if(isset($data->username)) {
            $this->user->updateUsername($user_id, $data->username);
        }
        if(isset($data->email)) {
            $this->user->updateEmail($user_id, $data->email);
        }
        if(isset($data->notification_enabled)) {
            $this->user->updateNotificationPreference($user_id, $data->notification_enabled);
        }
        return ["status" => "success", "message" => "Profile updated"];
    }

    public function requestPasswordReset($email) {
        $token = bin2hex(random_bytes(32));
        if($this->user->setPasswordResetToken($email, $token)) {
            $this->emailService->sendPasswordReset($email, $token);
            return ["status" => "success", "message" => "Password reset email sent"];
        }
        return ["status" => "error", "message" => "Email not found"];
    }

    public function resetPassword($token, $new_password) {
        if($this->user->verifyResetToken($token)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            if($this->user->updatePassword($token, $hashed_password)) {
                return ["status" => "success", "message" => "Password updated"];
            }
        }
        return ["status" => "error", "message" => "Invalid token"];
    }

    public function verifyEmail($token) {
        if($this->user->verifyEmailToken($token)) {
            return ["status" => "success", "message" => "Email verified"];
        }
        return ["status" => "error", "message" => "Invalid token"];
    }

    public function updateSettings($user_id, $settings) {
        return $this->user->updateSettings($user_id, $settings);
    }
}