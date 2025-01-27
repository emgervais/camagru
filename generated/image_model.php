<?php
class Image {
    private $conn;
    private $table = "images";

    public $id;
    public $user_id;
    public $image_path;
    public $thumbnail_path;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET user_id=:user_id, 
                     image_path=:image_path, 
                     thumbnail_path=:thumbnail_path";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":image_path", $this->image_path);
        $stmt->bindParam(":thumbnail_path", $this->thumbnail_path);

        return $stmt->execute();
    }

    public function getAll($page = 1, $per_page = 5) {
        $offset = ($page - 1) * $per_page;
        
        $query = "SELECT * FROM " . $this->table . " 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":limit", $per_page, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table . " 
                 WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    }
}