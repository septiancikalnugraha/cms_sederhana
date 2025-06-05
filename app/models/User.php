<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $full_name;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (username, password, email, full_name, role)
                VALUES
                (:username, :password, :email, :full_name, :role)";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function checkUserExists() {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE username = :username OR email = :email";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Method to check if the user has one of the specified roles
    public function hasRole($allowedRoles) {
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        // Convert stored role to lowercase for case-insensitive comparison
        $userRole = strtolower($this->role);
        foreach ($allowedRoles as $role) {
            if ($userRole === strtolower(trim($role))) {
                return true;
            }
        }
        return false;
    }
} 