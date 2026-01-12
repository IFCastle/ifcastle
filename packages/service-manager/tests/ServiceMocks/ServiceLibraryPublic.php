<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\ServiceMocks;

use IfCastle\ServiceManager\AsPublicService;
use IfCastle\ServiceManager\AsServiceMethod;

#[AsPublicService]
class ServiceLibraryPublic
{
    private array $books = [
        [
            'title' => 'The Book2',
            'author' => 'John Doe2',
        ],
        [
            'title' => 'The Other Book2',
            'author' => 'Jane Doe2',
        ],
    ];

    #[AsServiceMethod]
    public function findBookByAuthor(string $authorName): array
    {
        return \array_filter($this->books, fn($book) => $book['author'] === $authorName);
    }

    #[AsServiceMethod]
    public function addBook(array $book): void
    {
        // Add a book to the library
        $this->books[] = $book;
    }

    #[AsServiceMethod]
    public function getBooks(): array
    {
        return $this->books;
    }

    #[AsServiceMethod]
    public function removeBook(string $title): void
    {
        // Remove a book from the library
        $this->books = \array_filter($this->books, fn($book) => $book['title'] !== $title);
    }
}
