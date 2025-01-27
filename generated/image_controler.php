<?php
class ImageController {
    private $conn;
    private $image;

    public function __construct($db) {
        $this->conn = $db;
        $this->image = new Image($db);
    }

    public function upload($file, $user_id) {
        $target_dir = "uploads/";
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $this->image->user_id = $user_id;
            $this->image->image_path = $target_file;
            $this->image->thumbnail_path = $this->createThumbnail($target_file);
            
            if($this->image->create()) {
                return ["status" => "success", "message" => "Image uploaded successfully"];
            }
        }
        return ["status" => "error", "message" => "Failed to upload image"];
    }

    public function getGallery($page = 1) {
        return $this->image->getAll($page);
    }

    private function createThumbnail($source_path) {
        $thumb_width = 150;
        $thumb_path = "thumbnails/" . basename($source_path);
        
        list($width, $height) = getimagesize($source_path);
        $ratio = $thumb_width / $width;
        $thumb_height = $height * $ratio;
        
        $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
        $source = imagecreatefromjpeg($source_path);
        
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, 
                          $thumb_width, $thumb_height, $width, $height);
        imagejpeg($thumb, $thumb_path);
        
        return $thumb_path;
    }
}