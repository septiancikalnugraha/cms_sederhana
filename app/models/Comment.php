<?php
class Comment {
    private $conn;
    private $table_name = "comments";

    public $id;
    public $post_id;
    public $name;
    public $email;
    public $content;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (post_id, name, email, content, status, created_at)
                VALUES
                (:post_id, :name, :email, :content, :status, :created_at)";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->status = 'pending'; // Default status
        $this->created_at = date('Y-m-d H:i:s');

        // Bind values
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":created_at", $this->created_at);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readByPost($post_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE post_id = :post_id AND status = 'approved'
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();

        return $stmt;
    }

    public function readAll() {
        $query = "SELECT c.*, p.title as post_title 
                FROM " . $this->table_name . " c
                LEFT JOIN posts p ON c.post_id = p.id
                ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
} 