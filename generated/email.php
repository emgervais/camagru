<?php
class EmailService {
    private $conn;
    private $from = "noreply@camagru.com";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function sendCommentNotification($image_id) {
        $query = "SELECT u.email, u.notification_enabled 
                 FROM users u 
                 JOIN images i ON u.id = i.user_id 
                 WHERE i.id = :image_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":image_id", $image_id);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && $user['notification_enabled']) {
            $subject = "New Comment on Your Image";
            $message = "Someone commented on your image. Check it out!";
            mail($user['email'], $subject, $message, "From: {$this->from}");
        }
    }
}