<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\ServiceMocks;

use IfCastle\ServiceManager\AsServiceMethod;

class ServiceLibrary
{
    private array $books = [
        [
            'title' => 'The Book',
            'author' => 'John Doe',
        ],
        [
            'title' => 'The Other Book',
            'author' => 'Jane Doe',
        ],
    ];

    #[AsServiceMethod]
    public function findBookByAuthor(string $authorName): array
    {
        return \array_filter($this->books, fn(array $book) => $book['author'] === $authorName);
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
        $this->books = \array_filter($this->books, fn(array $book) => $book['title'] !== $title);
    }
}
