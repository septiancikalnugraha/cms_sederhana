<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $description;
    public $slug;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new category
    public function create() {
        try {
            // Generate slug from name
            $this->slug = $this->createSlug($this->name);

            $query = "INSERT INTO " . $this->table_name . "
                    (name, description, slug, created_at)
                    VALUES
                    (:name, :description, :slug, NOW())";

            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->description = htmlspecialchars(strip_tags($this->description));

            // Bind values
            $stmt->bindParam(":name", $this->name);
            $stmt->bindParam(":description", $this->description);
            $stmt->bindParam(":slug", $this->slug);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Gagal membuat kategori: " . $e->getMessage());
        }
    }

    // Read all categories
    public function read() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca daftar kategori: " . $e->getMessage());
        }
    }

    // Read categories with post count
    public function readWithPostCount() {
        try {
            $query = "SELECT c.*, COUNT(p.id) as post_count 
                    FROM " . $this->table_name . " c
                    LEFT JOIN posts p ON c.id = p.category_id
                    GROUP BY c.id
                    ORDER BY c.name ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca daftar kategori dengan jumlah post: " . $e->getMessage());
        }
    }

    // Read a single category
    public function readOne() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam(":id", $this->id);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row) {
                $this->name = $row['name'];
                $this->description = $row['description'];
                $this->slug = $row['slug'];
                $this->created_at = $row['created_at'];
                $this->updated_at = $row['updated_at'];
            }

            return $row;
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca kategori: " . $e->getMessage());
        }
    }

    // Update a category
    public function update() {
        try {
            // Generate new slug if name changed
            $this->slug = $this->createSlug($this->name);

            $query = "UPDATE " . $this->table_name . "
                    SET name = :name,
                        description = :description,
                        slug = :slug,
                        updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->description = htmlspecialchars(strip_tags($this->description));
            $this->id = htmlspecialchars(strip_tags($this->id));

            // Bind values
            $stmt->bindParam(":name", $this->name);
            $stmt->bindParam(":description", $this->description);
            $stmt->bindParam(":slug", $this->slug);
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Gagal memperbarui kategori: " . $e->getMessage());
        }
    }

    // Delete a category
    public function delete() {
        try {
            // First check if there are any posts using this category
            $check_query = "SELECT COUNT(*) as count FROM posts WHERE category_id = :id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":id", $this->id);
            $check_stmt->execute();
            $row = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if($row['count'] > 0) {
                throw new Exception("Tidak dapat menghapus kategori yang memiliki post");
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Gagal menghapus kategori: " . $e->getMessage());
        }
    }

    // Create URL-friendly slug from category name
    private function createSlug($name) {
        // Convert to lowercase
        $slug = strtolower($name);
        
        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        
        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');
        
        return $slug;
    }

    // Check if category name already exists
    public function nameExists() {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE name = :name";
            if($this->id) {
                $query .= " AND id != :id";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":name", $this->name);
            if($this->id) {
                $stmt->bindParam(":id", $this->id);
            }
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Gagal memeriksa nama kategori: " . $e->getMessage());
        }
    }
}