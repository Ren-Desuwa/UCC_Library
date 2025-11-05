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
            return $stmt->error;
        }
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