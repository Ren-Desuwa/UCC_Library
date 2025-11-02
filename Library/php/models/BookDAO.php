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
     * UPDATED: Searches for books by specific criteria, including availability status.
     * @param string $searchTerm (for title)
     * @param string $author (for author name)
     * @param string $genre (for genre name)
     * @param string $year_from
     * @param string $year_to
     * @param string $status (e.g., "available")
     * @param int $limit
     * @param int $offset
     * @return array An array of matching books.
     */
    public function searchBooks($searchTerm, $author, $genre, $year_from, $year_to, $status, $limit = 20, $offset = 0) {
        
        // Base query
        $sql = "SELECT 
                    b.*, 
                    GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ') AS author_names,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genre_names,
                    SUM(CASE WHEN c.status = 'Available' THEN 1 ELSE 0 END) AS available_copies_count
                FROM books b
                LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.author_id
                LEFT JOIN book_genres bg ON b.book_id = bg.book_id
                LEFT JOIN genres g ON bg.genre_id = g.genre_id
                LEFT JOIN book_copies c ON b.book_id = c.book_id"; // <-- NEW JOIN
        
        $whereClauses = [];
        $havingClauses = [];
        $params = [];
        $types = "";

        // --- WHERE (Book Table) ---
        if (!empty($searchTerm)) {
            $whereClauses[] = "b.title LIKE ?";
            $params[] = "%" . $searchTerm . "%";
            $types .= "s";
        }
        
        if (!empty($year_from) && !empty($year_to)) {
            $whereClauses[] = "b.year_published BETWEEN ? AND ?";
            $params[] = $year_from;
            $params[] = $year_to;
            $types .= "ii";
        } else if (!empty($year_from)) {
            $whereClauses[] = "b.year_published >= ?";
            $params[] = $year_from;
            $types .= "i";
        } else if (!empty($year_to)) {
            $whereClauses[] = "b.year_published <= ?";
            $params[] = $year_to;
            $types .= "i";
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        $sql .= " GROUP BY b.book_id"; // Group first

        // --- HAVING (Aggregated Columns) ---
        if (!empty($author)) {
            $havingClauses[] = "author_names LIKE ?";
            $params[] = "%" . $author . "%";
            $types .= "s";
        }
        if (!empty($genre)) {
            $havingClauses[] = "genre_names LIKE ?";
            $params[] = "%" . $genre . "%";
            $types .= "s";
        }
        // NEW: Filter by availability
        if (!empty($status) && ($status == 'available' || $status == 'true')) {
            $havingClauses[] = "available_copies_count > 0";
        } else if (!empty($status) && ($status == 'unavailable' || $status == 'false')) {
            $havingClauses[] = "available_copies_count = 0";
        }


        if (!empty($havingClauses)) {
            $sql .= " HAVING " . implode(" AND ", $havingClauses); // Then filter groups
        }
        
        $sql .= " ORDER BY b.title LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
                
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
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