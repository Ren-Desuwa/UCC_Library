<?php
class BookCopyDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getCopyById($copyId) {
        $sql = "SELECT * FROM book_copies WHERE copy_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $copyId);
        $stmt->execute();
        $result = $stmt->get_result();
        $copy = $result->fetch_assoc();
        if (!$copy) {
            throw new Exception("Book copy with ID $copyId not found.");
        }
        return $copy;
    }

    public function updateCopyStatus($copyId, $status) {
        $sql = "UPDATE book_copies SET status = ? WHERE copy_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $copyId);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to update book copy status: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }

    /**
     * NEW: This method was missing for the CatalogueService.
     * Fetches all copies for a specific book.
     */
    public function getCopiesForBook($bookId) {
        $sql = "SELECT * FROM book_copies WHERE book_id = ? ORDER BY copy_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}