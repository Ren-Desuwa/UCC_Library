<?php

class BookDAO {
    
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Fetches a single book by its ID.
     * @param int $bookId
     * @return array|null An associative array of the book, or null if not found.
     */
    public function getBookById($bookId) {
        $sql = "SELECT * FROM books WHERE book_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Fetches all books (with pagination).
     * @param int $limit Number of books to fetch.
     * @param int $offset Number of books to skip.
     * @return array An array of associative arrays, each representing a book.
     */
    public function getAllBooks($limit = 10, $offset = 0) {
        $sql = "SELECT * FROM books ORDER BY title LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Searches for books by title (with pagination).
     * @param string $searchTerm
     * @param int $limit
     * @param int $offset
     * @return array An array of matching books.
     */
    public function searchBooksByTitle($searchTerm, $limit = 20, $offset = 0) {
        // --- THIS IS THE CORRECTED QUERY ---
        $sql = "SELECT * FROM books WHERE title LIKE ? ORDER BY title LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $likeTerm = "%" . $searchTerm . "%";
        
        // Bind 3 parameters now: string, int, int
        $stmt->bind_param("sii", $likeTerm, $limit, $offset); 
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Creates a new book record.
     * This transaction also links authors and genres.
     * @param string $title
     * @param string $isbn
     * @param string $publisher
     * @param int $yearPublished
     * @param string $description
     * @param string $coverUrl
     * @param array $authorIds An array of author IDs to link.
     * @param array $genreIds An array of genre IDs to link.
     * @return int|string The new book_id on success, or an error message on failure.
     */
    public function createBook($title, $isbn, $publisher, $yearPublished, $description, $coverUrl) {
        $sqlBook = "INSERT INTO books (title, isbn, publisher, year_published, description, cover_url) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmtBook = $this->conn->prepare($sqlBook);
        $stmtBook->bind_param("sssiss", $title, $isbn, $publisher, $yearPublished, $description, $coverUrl);
        if (!$stmtBook->execute()) {
             throw new Exception("DAO Error: Failed to create book: " . $stmtBook->error);
        }
        return $this->conn->insert_id;
    }

    /**
     * Fetches all authors for a given book.
     * @param int $bookId
     * @return array An array of author associative arrays.
     */
    public function getAuthorsForBook($bookId) {
        $sql = "SELECT a.* FROM authors a
                JOIN book_authors ba ON a.author_id = ba.author_id
                WHERE ba.book_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetches all genres for a given book.
     * @param int $bookId
     * @return array An array of genre associative arrays.
     */
    public function getGenresForBook($bookId) {
        $sql = "SELECT g.* FROM genres g
                JOIN book_genres bg ON g.genre_id = bg.genre_id
                WHERE bg.book_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Links a list of authors to a book.
     */
    public function linkAuthorsToBook($bookId, $authorIds) {
        $sqlAuthor = "INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)";
        $stmtAuthor = $this->conn->prepare($sqlAuthor);
        foreach ($authorIds as $authorId) {
            $stmtAuthor->bind_param("ii", $bookId, $authorId);
            $stmtAuthor->execute();
        }
    }
    
    /**
     * Links a list of genres to a book.
     */
    public function linkGenresToBook($bookId, $genreIds) {
        $sqlGenre = "INSERT INTO book_genres (book_id, genre_id) VALUES (?, ?)";
        $stmtGenre = $this->conn->prepare($sqlGenre);
        foreach ($genreIds as $genreId) {
            $stmtGenre->bind_param("ii", $bookId, $genreId);
            $stmtGenre->execute();
        }
    }

    /**
     * NEW: This method was missing for CatalogueService.
     * Updates the core details of a book.
     */
    public function updateBook($bookId, $title, $isbn, $publisher, $yearPublished, $description) {
        $sql = "UPDATE books SET title = ?, isbn = ?, publisher = ?, year_published = ?, description = ?
                WHERE book_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssisi", $title, $isbn, $publisher, $yearPublished, $description, $bookId);
        if (!$stmt->execute()) {
             throw new Exception("DAO Error: Failed to update book: "." - ".$stmt->error);
        }
        return $stmt->affected_rows > 0;
    }
}