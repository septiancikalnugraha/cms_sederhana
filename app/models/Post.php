<?php
class Post {
    private $conn;
    private $table_name = "posts";

    public $id;
    public $title;
    public $slug;
    public $content;
    public $featured_image;
    public $status;
    public $author_id;
    public $category_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            // Generate slug from title
            $this->slug = $this->createSlug($this->title);

            $query = "INSERT INTO " . $this->table_name . "
                    (title, slug, content, category_id, author_id, status, featured_image, created_at)
                    VALUES
                    (:title, :slug, :content, :category_id, :author_id, :status, :featured_image, NOW())";

            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $this->title = htmlspecialchars(strip_tags($this->title));
            $this->content = $this->content; // Don't strip HTML for content
            $this->category_id = htmlspecialchars(strip_tags($this->category_id));
            $this->author_id = htmlspecialchars(strip_tags($this->author_id));
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->featured_image = htmlspecialchars(strip_tags($this->featured_image));

            // Bind values
            $stmt->bindParam(":title", $this->title);
            $stmt->bindParam(":slug", $this->slug);
            $stmt->bindParam(":content", $this->content);
            $stmt->bindParam(":category_id", $this->category_id);
            $stmt->bindParam(":author_id", $this->author_id);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":featured_image", $this->featured_image);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Gagal membuat post: " . $e->getMessage());
        }
    }

    public function read() {
        try {
            $query = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                    FROM " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN users u ON p.author_id = u.id
                    ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca daftar post: " . $e->getMessage());
        }
    }

    public function readOne() {
        try {
            $query = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                    FROM " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row) {
                $this->title = $row['title'];
                $this->slug = $row['slug'];
                $this->content = $row['content'];
                $this->category_id = $row['category_id'];
                $this->author_id = $row['author_id'];
                $this->status = $row['status'];
                $this->featured_image = $row['featured_image'];
                $this->created_at = $row['created_at'];
                $this->updated_at = $row['updated_at'];
            }

            return $row;
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca post: " . $e->getMessage());
        }
    }

    public function update() {
        try {
            // Generate new slug if title changed
            $this->slug = $this->createSlug($this->title);

            $query = "UPDATE " . $this->table_name . "
                    SET title = :title,
                        slug = :slug,
                        content = :content,
                        category_id = :category_id,
                        status = :status,
                        featured_image = :featured_image,
                        updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            // Sanitize input
            $this->title = htmlspecialchars(strip_tags($this->title));
            $this->content = $this->content; // Don't strip HTML for content
            $this->category_id = htmlspecialchars(strip_tags($this->category_id));
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->featured_image = htmlspecialchars(strip_tags($this->featured_image));
            $this->id = htmlspecialchars(strip_tags($this->id));

            // Bind values
            $stmt->bindParam(":title", $this->title);
            $stmt->bindParam(":slug", $this->slug);
            $stmt->bindParam(":content", $this->content);
            $stmt->bindParam(":category_id", $this->category_id);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":featured_image", $this->featured_image);
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Gagal memperbarui post: " . $e->getMessage());
        }
    }

    public function delete() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Gagal menghapus post: " . $e->getMessage());
        }
    }

    public function createSlug($string) {
        // Convert to lowercase
        $slug = strtolower($string);
        
        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        
        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');
        
        return $slug;
    }

    public function readPublished() {
        try {
            $query = "SELECT p.id, p.title, p.slug, p.content, p.featured_image, p.status, p.created_at, 
                            u.full_name as author_name, c.name as category_name
                    FROM " . $this->table_name . " p
                    LEFT JOIN users u ON p.author_id = u.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'published'
                    ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca post yang dipublikasikan: " . $e->getMessage());
        }
    }

    public function readBySlug($slug) {
        try {
            $query = "SELECT p.id, p.title, p.slug, p.content, p.featured_image, p.status, p.created_at, 
                            u.full_name as author_name, c.name as category_name, c.slug as category_slug
                    FROM " . $this->table_name . " p
                    LEFT JOIN users u ON p.author_id = u.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.slug = ? AND p.status = 'published'
                    LIMIT 0,1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $slug);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca post berdasarkan slug: " . $e->getMessage());
        }
    }

    public function search($keyword) {
        try {
            $query = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                    FROM " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.title LIKE :keyword OR p.content LIKE :keyword
                    ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $keyword = "%{$keyword}%";
            $stmt->bindParam(":keyword", $keyword);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Gagal mencari post: " . $e->getMessage());
        }
    }

    public function readByCategory($category_slug) {
        try {
            $query = "SELECT p.id, p.title, p.slug, p.content, p.featured_image, p.status, p.created_at, 
                            u.full_name as author_name, c.name as category_name, c.slug as category_slug
                    FROM " . $this->table_name . " p
                    LEFT JOIN users u ON p.author_id = u.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE c.slug = ? AND p.status = 'published'
                    ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);

            $category_slug = htmlspecialchars(strip_tags($category_slug));
            $stmt->bindParam(1, $category_slug);

            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca post berdasarkan kategori: " . $e->getMessage());
        }
    }

    public function getByCategory($category_id) {
        try {
            $query = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                    FROM " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.category_id = :category_id
                    ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":category_id", $category_id);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca post berdasarkan kategori: " . $e->getMessage());
        }
    }

    public function getByAuthor($author_id) {
        try {
            $query = "SELECT p.*, c.name as category_name, u.full_name as author_name 
                    FROM " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.author_id = :author_id
                    ORDER BY p.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":author_id", $author_id);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Gagal membaca post berdasarkan penulis: " . $e->getMessage());
        }
    }
} 