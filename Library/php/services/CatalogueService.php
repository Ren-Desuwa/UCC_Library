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
    /**
     * UPDATED: Updates an existing book's details, authors, and genres.
     * This is now a transactional operation.
     */
    public function updateBook($bookId, $title, $isbn, $publisher, $year, $desc, $coverUrl, $authorNamesString, $genreNamesString) {
        $this->conn->begin_transaction();
        try {
            // 1. Update the main book entry (and clear old links)
            // We pass coverUrl in case it was updated (e.g., from file upload)
            $this->bookDAO->updateBook($bookId, $title, $isbn, $publisher, $year, $desc, $coverUrl);

            // 2. Process Authors (Same logic as addBook)
            $authorIds = [];
            $authorNames = explode(',', $authorNamesString);
            foreach ($authorNames as $authorName) {
                $name = trim($authorName);
                if (empty($name)) continue;
                $author = $this->authorDAO->findAuthorByName($name);
                $authorIds[] = $author ? $author['author_id'] : $this->authorDAO->createAuthor($name);
            }

            // 3. Process Genres (Same logic as addBook)
            $genreIds = [];
            $genreNames = explode(',', $genreNamesString);
            foreach ($genreNames as $genreName) {
                $name = trim($genreName);
                if (empty($name)) continue;
                $genre = $this->genreDAO->findGenreByName($name);
                $genreIds[] = $genre ? $genre['genre_id'] : $this->genreDAO->createGenre($name);
            }

            // 4. Link new authors and genres
            if (!empty($authorIds)) {
                $this->bookDAO->linkAuthorsToBook($bookId, array_unique($authorIds));
            }
            if (!empty($genreIds)) {
                $this->bookDAO->linkGenresToBook($bookId, array_unique($genreIds));
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Service Error: Could not update book. " . $e->getMessage());
        }
    }

    /**
     * NEW: Archives a book.
     */
    public function archiveBook($bookId) {
        return $this->bookDAO->archiveBook($bookId);
    }

    /**
     * NEW: Unarchives (restores) a book.
     */
    public function unarchiveBook($bookId) {
        return $this->bookDAO->unarchiveBook($bookId);
    }
    
    /**
     * NEW: Gets all data needed for the edit form.
     */
    public function getBookForEdit($bookId) {
        return $this->bookDAO->getBookWithRelations($bookId);
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