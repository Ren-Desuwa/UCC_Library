<?php

class GenreDAO {
    
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Creates a new genre.
     * @param string $name The genre's name.
     * @return int|string The new genre_id or an error message.
     */
    public function createGenre($name) {
        $sql = "INSERT INTO genres (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            // Handle unique constraint violation (genre already exists)
            if ($this->conn->errno == 1062) { 
                $existing = $this->findGenreByName($name);
                return $existing['genre_id'];
            }
            throw new Exception("DAO Error: Failed to create genre: " . $stmt->error);
        }
    }
    
    /**
     * NEW: Finds a genre by its exact name.
     * @param string $name
     * @return array|null
     */
    public function findGenreByName($name) {
        $sql = "SELECT * FROM genres WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Fetches a genre by its ID.
     * @param int $genreId
     * @return array|null
     */
    public function getGenreById($genreId) {
        $sql = "SELECT * FROM genres WHERE genre_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $genreId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Fetches all genres.
     * @return array
     */
    public function getAllGenres() {
        $sql = "SELECT * FROM genres ORDER BY name";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}