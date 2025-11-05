<?php
require_once __DIR__ . '/../models/BookDAO.php';
require_once __DIR__ . '/../models/AuthorDAO.php';
require_once __DIR__ . '/../models/GenreDAO.php';
require_once __DIR__ . '/../models/BookCopyDAO.php';

class CatalogueService {
    private $conn;
    private $bookDAO;
    private $authorDAO;
    private $genreDAO;
    private $bookCopyDAO;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->bookDAO = new BookDAO($conn);
        $this->authorDAO = new AuthorDAO($conn);
        $this->genreDAO = new GenreDAO($conn);
        $this->bookCopyDAO = new BookCopyDAO($conn);
    }

    /**
     * Gets all data needed to display a single book page.
     * No transaction needed as this is a read-only operation.
     */
    public function getBookDetails($bookId) {
        $book = $this->bookDAO->getBookById($bookId);
        if (!$book) {
            throw new Exception("Book not found.");
        }
        
        $book['authors'] = $this->bookDAO->getAuthorsForBook($bookId);
        $book['genres'] = $this->bookDAO->getGenresForBook($bookId);
        $book['copies'] = $this->bookCopyDAO->getCopiesForBook($bookId); // (You'd add this method to BookCopyDAO)
        
        return $book;
    }

    /**
     * Searches for books. Read-only, no transaction.
     */
    public function searchBooks($searchTerm, $author, $genre, $limit = 20, $offset = 0) {
        // Call the updated DAO method
        return $this->bookDAO->searchBooks($searchTerm, $author, $genre, $limit, $offset);
    }
    
    /**
     * Adds a new book to the catalogue.
     * THIS is where the transaction logic from BookDAO belongs.
     */
    public function addBook($title, $isbn, $publisher, $year, $desc, $coverUrl, $authorIds, $genreIds) {
        $this->conn->begin_transaction();
        try {
            // Use the simpler DAO methods
            $bookId = $this->bookDAO->createBook($title, $isbn, $publisher, $year, $desc, $coverUrl);
            $this->bookDAO->linkAuthorsToBook($bookId, $authorIds);
            $this->bookDAO->linkGenresToBook($bookId, $genreIds);
            
            $this->conn->commit();
            return $bookId;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Service Error: Could not add book. " . $e->getMessage());
        }
    }
    
    /**
     * Updates an existing book's details.
     */
    public function updateBook($bookId, $title, $isbn, $publisher, $year, $desc) {
        // You would add a 'updateBook' method to your BookDAO
        return $this->bookDAO->updateBook($bookId, $title, $isbn, $publisher, $year, $desc);
    }
    
    /**
     * Gets all authors (e.g., for an admin dropdown).
     */
    public function getAllAuthors() {
        return $this->authorDAO->getAllAuthors();
    }
    
    /**
     * Gets all genres (e.g., for an admin dropdown).
     */
    public function getAllGenres() {
        return $this->genreDAO->getAllGenres();
    }
}