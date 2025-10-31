<?php

class FavoriteDAO {
    
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Adds a book to a user's favorites.
     * @param int $accountId
     * @param int $bookId
     * @return bool|string True on success, or an error message.
     */
    public function addFavorite($accountId, $bookId) {
        $sql = "INSERT INTO favorites (account_id, book_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $accountId, $bookId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            // It might fail if the favorite already exists (due to unique key)
            return $stmt->error;
        }
    }

    /**
     * Removes a book from a user's favorites.
     * @param int $accountId
     * @param int $bookId
     * @return bool True on success, false on failure.
     */
    public function removeFavorite($accountId, $bookId) {
        $sql = "DELETE FROM favorites WHERE account_id = ? AND book_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $accountId, $bookId);
        return $stmt->execute();
    }

    /**
     * Fetches all favorite books for a given user.
     * @param int $accountId
     * @return array An array of book associative arrays.
     */
    public function getFavoritesForUser($accountId) {
        $sql = "SELECT b.* FROM books b
                JOIN favorites f ON b.book_id = f.book_id
                WHERE f.account_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}