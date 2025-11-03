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
     * NEW: A more comprehensive update function for a single copy.
     */
    public function updateCopyDetails($copyId, $status, $condition, $shelfLocation) {
        $sql = "UPDATE book_copies SET status = ?, `condition` = ?, shelf_location = ? WHERE copy_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $status, $condition, $shelfLocation, $copyId);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to update copy details: " . $stmt->error);
        }
        return $stmt->affected_rows > 0;
    }


    /**
     * NEW: Creates a new book copy.
     */
    public function createCopy($bookId, $condition, $shelfLocation) {
        $sql = "INSERT INTO book_copies (book_id, `condition`, shelf_location, status) VALUES (?, ?, ?, 'Available')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $bookId, $condition, $shelfLocation);
        if (!$stmt->execute()) {
            throw new Exception("DAO Error: Failed to create copy: " . $stmt->error);
        }
        return $this->conn->insert_id;
    }

    /**
     * NEW: Deletes a book copy.
     * This will fail if the copy is linked to a transaction, which is the desired behavior.
     */
    public function deleteCopy($copyId) {
        $sql = "DELETE FROM book_copies WHERE copy_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $copyId);
        if (!$stmt->execute()) {
            // Check for foreign key constraint violation
            if ($this->conn->errno == 1451) { 
                throw new Exception("Cannot delete this copy because it is linked to past transactions. Please set its status to 'Archived' or 'Maintenance' instead.");
            }
            throw new Exception("DAO Error: Failed to delete copy: " . $stmt->error);
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