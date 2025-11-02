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
        $book['copies'] = $this->bookCopyDAO->getCopiesForBook($bookId);
        
        return $book;
    }

    /**
     * Searches for books. Read-only, no transaction.
     */
    public function searchBooks($searchTerm, $author, $genre, $year_from, $year_to, $status, $limit = 20, $offset = 0) {
        // Call the updated DAO method
        return $this->bookDAO->searchBooks($searchTerm, $author, $genre, $year_from, $year_to, $status, $limit, $offset);
    }
    
    /**
     * UPDATED: Adds a new book to the catalogue.
     * This now accepts comma-separated strings for authors and genres
     * and handles the logic of finding/creating them.
     */
    public function addBook($title, $isbn, $publisher, $year, $desc, $coverUrl, $authorNamesString, $genreNamesString) {
        $this->conn->begin_transaction();
        try {
            // 1. Create the main book entry
            $bookId = $this->bookDAO->createBook($title, $isbn, $publisher, $year, $desc, $coverUrl);

            // 2. Process Authors
            $authorIds = [];
            $authorNames = explode(',', $authorNamesString);
            foreach ($authorNames as $authorName) {
                $name = trim($authorName);
                if (empty($name)) continue;

                $author = $this->authorDAO->findAuthorByName($name);
                if ($author) {
                    $authorIds[] = $author['author_id'];
                } else {
                    $newAuthorId = $this->authorDAO->createAuthor($name);
                    $authorIds[] = $newAuthorId;
                }
            }
            // Remove duplicates
            $authorIds = array_unique($authorIds);

            // 3. Process Genres
            $genreIds = [];
            $genreNames = explode(',', $genreNamesString);
            foreach ($genreNames as $genreName) {
                $name = trim($genreName);
                if (empty($name)) continue;

                $genre = $this->genreDAO->findGenreByName($name);
                if ($genre) {
                    $genreIds[] = $genre['genre_id'];
                } else {
                    $newGenreId = $this->genreDAO->createGenre($name);
                    $genreIds[] = $newGenreId;
                }
            }
            // Remove duplicates
            $genreIds = array_unique($genreIds);

            // 4. Link authors and genres
            if (!empty($authorIds)) {
                $this->bookDAO->linkAuthorsToBook($bookId, $authorIds);
            }
            if (!empty($genreIds)) {
                $this->bookDAO->linkGenresToBook($bookId, $genreIds);
            }
            
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