<?php
class Like {
    private $conn;
    private $table = "likes";

    public $id;
    public $user_id;
    public $image_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function toggle() {
        if ($this->exists()) {
            return $this->delete();
        }
        return $this->create();
    }

    private function exists() {
        $query = "SELECT id FROM " . $this->table . " 
                 WHERE user_id = :user_id AND image_id = :image_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":image_id", $this->image_id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    private function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET user_id=:user_id, image_id=:image_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":image_id", $this->image_id);
        
        return $stmt->execute();
    }

    private function delete() {
        $query = "DELETE FROM " . $this->table . " 
                 WHERE user_id=:user_id AND image_id=:image_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":image_id", $this->image_id);
        
        return $stmt->execute();
    }
}