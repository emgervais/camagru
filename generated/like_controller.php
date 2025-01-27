<?php
class LikeController {
    private $conn;
    private $like;

    public function __construct($db) {
        $this->conn = $db;
        $this->like = new Like($db);
    }

    public function toggle($image_id, $user_id) {
        $this->like->user_id = $user_id;
        $this->like->image_id = $image_id;

        if($this->like->toggle()) {
            return ["status" => "success"];
        }
        return ["status" => "error", "message" => "Failed to toggle like"];
    }
}