<?php

class AuthorDAO {
    
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Creates a new author.
     * @param string $name The author's name.
     * @return int|string The new author_id or an error message.
     */
    public function createAuthor($name) {
        $sql = "INSERT INTO authors (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            // Handle unique constraint violation (author already exists)
            if ($this->conn->errno == 1062) {
                $existing = $this->findAuthorByName($name);
                return $existing['author_id'];
            }
            throw new Exception("DAO Error: Failed to create author: " . $stmt->error);
        }
    }
    
    /**
     * NEW: Finds an author by their exact name.
     * @param string $name
     * @return array|null
     */
    public function findAuthorByName($name) {
        $sql = "SELECT * FROM authors WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Fetches an author by their ID.
     * @param int $authorId
     * @return array|null
     */
    public function getAuthorById($authorId) {
        $sql = "SELECT * FROM authors WHERE author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $authorId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Fetches all authors.
     * @return array
     */
    public function getAllAuthors() {
        $sql = "SELECT * FROM authors ORDER BY name";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}