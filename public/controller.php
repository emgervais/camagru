<?php

include "user.php";
class Controller {
    private $conn;
    public User $user;

    public function __construct(
        private $db
    ) {
        $this->conn = $db;
        $this->user = new User($db);
    }

    public function register($data) {
        if(!isset($data) || !isset($data["email"]) || !isset($data["username"]) || !isset($data["password"]))
            return ["status" => "error", "message" => "Missing data"];

        $data['username'] = $this->user->sanitize($data['username']);

        if($this->user->emailExists($data['email']) ||  !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ["status" => "error", "message" => "Email already exists or invalid"];
        }

        if ($this->user->userExists($data['username'])) {
            return ["status" => "error", "message" => "Username already exists or invalid"];
        }

        if(!$this->user->passwordValidation($data['password'])) {
            return ["status" => "error", "message" => "Password must contain at least 3 characters, one uppercase letter and one number"];
        }

        $token = $this->user->create($data['username'], $data['email'], $data['password']);
        if($token) {
            $to = $data['email'];
            $subject = "Email Verification";
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
            
            mail($to, $subject, $message, $headers);
            return ["status" => "success", "message" => "email sent"];
        }
        return ["status" => "error", "message" => "Failed to create user"];
    }

    public function verifyToken($token) {
        if(isset($token) && $this->user->verifyToken($token)) {
            return true;
        }
        return false;
    }

    public function changePassword($data) {
        if(isset($data) && isset($data['token']) && isset($data['password']) && $this->user->passwordValidation($data['password']) && $this->user->verifyTokenPassword($data['token'], $data['password'])) {
            return true;
        }
        return false;
    }
    public function login($data) {
        if(!isset($data) || !isset($data['username']) || !$this->user->userExists($data['username'])) {
            return ["status" => "error", "message" => "User does not exist or was not confirmed."];
        }
        if(isset($data['password']) && $this->user->verifyPassword($data['username'], $data['password'])) {
            return ["status" => "success", "message" => "Login successful"];
        }
        return ["status" => "error", "message" => "Invalid password"];
    }
    public function forgotPassword($data) {
        if(isset($data) && isset($data['email']) && $this->user->emailExists($data['email'])) {
            $token = $this->user->setResetToken($data['email']);
            $to = $data['email'];
            $subject = "Password Reset";
            $message = "<html><body>";
            $message .= "<p>Please reset your password by clicking this link:</p>";
            $message .= "<p><a href='http://localhost:8080/api/changePassword/reset?token=" . $token . "'>Click here to reset your password</a></p>";
            $message .= "</body></html>";
            $headers = array(
                'From' => 'Camagru <camagru.egerv@gmail.com>',
                'Reply-To' => 'Camagru <camagru.egerv@gmail.com>',
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/html; charset=UTF-8',
            );

            mail($to, $subject, $message, $headers);
            return true;
        }

        return false;
    }
    public function changeInfo($data) {
        if(!isset($_SESSION) || !isset($_SESSION['user_id']))
            return ["status" => "error", "message" => "Please login to change info"];

        if(!isset($data))
            return ["status" => "error", "message" => "No data provided"];

        return $this->user->changeInfo($data);
    }
    public function getPosts($page) {
        $offset = ($page - 1) * 10;
        $query = "SELECT * FROM posts ORDER BY creation_date DESC LIMIT 10 OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        return $stmt->execute() ? ["status" => "success", "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)] : ["status" => "error", "message" => "Failed to get posts"];
    }
    public function like($data) {

        if (!isset($_SESSION) || !isset($_SESSION['user_id'])) {
            return ["status" => "error", "message" => "Please login to like posts"];
        }

        if (!isset($data) || !isset($data['id']) || !is_numeric($data['like']))
            return ["status" => "error", "message" => "Missing data"];

        $query = $data['like'] ? "UPDATE posts SET likes = likes + 1 WHERE id = ?" : "UPDATE posts SET likes = likes - 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['id']);

        return $stmt->execute() ? ["status" => "success", "message" => "Post liked"] : ["status" => "error", "message" => "Failed to like post"];
    }
    public function comment($data) {
        if (!isset($_SESSION) || !isset($_SESSION['user_id'])) {
            return ["status" => "error", "message" => "Please login to comment"];
        }
        if (!isset($data) || !isset($data['id']))
            return ["status" => "error", "message" => "Missing data"];
        $query = "select * from comments where post_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['id']);
        return $stmt->execute() ? ["status" => "success", "comments" => $stmt->fetchAll(PDO::FETCH_ASSOC)] : ["status" => "error", "message" => "Failed fetch comments"];
    }
    private function sendCommentMail($id) {
        $query = "SELECT user_id FROM posts WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "SELECT email, notification FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $result['user_id']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result['notification'])
            return;

        $to = $result['email'];
        $subject = "New Comment";
        $message = "<html><body>";
        $message .= "<p>You have a new comment on your post</p>";
        $message .= "</body></html>";
        $headers = array(
            'From' => 'Camagru <camagru.egerv@gmail.com>',
            'Reply-To' => 'Camagru <camagru.egerv@gmail.com>',
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8',
        );

        mail($to, $subject, $message, $headers);
    }
    public function sendComment($data) {
        if (!isset($_SESSION) || !isset($_SESSION['user_id'])) {
            return ["status" => "error", "message" => "Please login to comment"];
        }
        if (!isset($data) || !isset($data['id']) || !isset($data['comment']))
            return ["status" => "error", "message" => "Missing data"];
        try {
            $query = "INSERT INTO comments (post_id, user_id, comment, username) VALUES (?, ?, ?, ?)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $data['id']);
            $stmt->bindParam(2, $_SESSION['user_id']);

            $comment = htmlspecialchars($data['comment'], ENT_QUOTES, 'UTF-8');

            $stmt->bindParam(3, $comment);
            $stmt->bindParam(4, $_SESSION['username']);
            
            $response = $stmt->execute() ? ["status" => "success", "message" => "Comment added", "username" => $_SESSION['username']] : ["status" => "error", "message" => "Failed to add comment"];
        } catch (PDOException $e) {
            $response = ["status" => "error", "message" => "Post couldnt be found"];
        }
        if ($response['status'] === "error")
            return $response;

        $this->sendCommentMail($data['id']);
        return $response;
    }

    private function mergeImg($img, $add, $path) {
        $decode = base64_decode(substr($img, strpos($img, ',') + 1));
        $img = imagecreatefromstring($decode);
        if (!$img)
            return false;
        for ($i = 0; $i < count($add); $i++) {
            $imgpath = dirname(__DIR__) . '/public' . parse_url($add[$i]->src)['path'];
            $image_info = getimagesize($imgpath);
            if (!$image_info)
                return false;
            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    $addon = imagecreatefromjpeg($imgpath);
                    break;
                case IMAGETYPE_PNG:
                    $addon = imagecreatefrompng($imgpath);
                    break;
                case IMAGETYPE_GIF:
                    $addon = imagecreatefromgif($imgpath);
                    break;
                default:
                    return false;
            }

            $scaled = imagescale($addon, $add[$i]->width, $add[$i]->height);
            if (!imagecopy($img, $scaled, $add[$i]->left, $add[$i]->top, 0, 0, $add[$i]->width, $add[$i]->height)) {
                imagedestroy($addon);
                return false;
            }
            imagedestroy($addon);
        }

        imagepng($img, $path, 9);
        return true;
    }

    public function publish($data) {
        $name = $_SESSION['username'] . '_' . time() . '.png';
        $path = dirname(__DIR__) . '/public/img/' . $name;
        $img = $this->mergeImg($data['dest'], json_decode($data['addons']), $path);
        if (!$img)
            return ["status" => "error", "message" => "Failed to merge images"];
        $query = "INSERT INTO posts (user_id, image_path) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $_SESSION['user_id']);
        $p = 'img/' . $name;
        $stmt->bindParam(2, $p);
        return $stmt->execute() ? ["status" => "success", "message" => "Post published"] : ["status" => "error", "message" => "Failed to publish post"];
    }

    public function delete($id) {
        $querry = "SELECT user_id, image_path FROM posts WHERE id = ?";
        $stmt = $this->conn->prepare($querry);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['user_id'] != $_SESSION['user_id'])
            return ["status" => "error", "message" => "You can only delete your own posts"];

        unlink(dirname(__DIR__) . '/public/' . $result['image_path']);

        $querry = "DELETE FROM posts WHERE id = ?";
        $stmt = $this->conn->prepare($querry);
        $stmt->bindParam(1, $id);

        return $stmt->execute() ? ["status" => "success", "message" => "Post deleted"] : ["status" => "error", "message" => "Failed to delete post"];
    }

    public function setNotifications() {
        $query = "SELECT notification from users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "UPDATE users SET notification = !notification WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $_SESSION['user_id']);
        $turn = $result['notification'] ? "off" : "on";

        return $stmt->execute() ? ["status" => "success", "message" => "Notification status is now " . $turn] : ["status" => "error", "message" => "Failed to change notification status"];
    }
}
