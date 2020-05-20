<?php
class Book {
    const GOOGLE_BOOKS_ENDPOINT = "https://www.googleapis.com/books/v1";

    public $title;
    public $subtitle;
    public $authors;
    public $isbn10;
    public $edition;
    public $publisher;
    public $datePublished;

    public static function fromGoogleBooksAPIv1(array $info): Book {
        if(!isset($info['items']) || !isset($info['items'][0]) || !isset($info['items'][0]['volumeInfo'])) {
            throw new Error('No book found');
        }
        $volumeInfo = $info['items'][0]['volumeInfo'];

        if(!isset($volumeInfo['industryIdentifiers']) || !is_array($volumeInfo['industryIdentifiers'])) {
            throw new Error('No identifier');
        }
        foreach($volumeInfo['industryIdentifiers'] as $id) {
            if(!isset($id['type'])) {
                throw new Error('Invalid identifier type');
            }
            if(!isset($id['identifier'])) {
                throw new Error("No identifier for type {$id['type']}");
            }
            switch($id['type']) {
                case 'ISBN_10':
                    $isbn10 = $id['identifier'];
                    break;
                case 'ISBN_13':
                    $isbn13 = $id['identifier'];
                    break;
            }
        }
        if(!isset($isbn10,$isbn13)) {
            throw new Error('No identifier');
        }

        if(!isset($volumeInfo['publisher']) || !is_string($volumeInfo['publisher'])) {
            throw new Error('Invalid publisher');
        }
        if(!isset($volumeInfo['publishedDate'])) {
            throw new Error('Invalid publishing date');
        }
        if(
            !($datePublished = DateTimeImmutable::createFromFormat('Y-m-d', $volumeInfo['publishedDate'])) &&
            !($datePublished = DateTimeImmutable::createFromFormat('Y-m', $volumeInfo['publishedDate'])) &&
            !($datePublished = DateTimeImmutable::createFromFormat('Y', $volumeInfo['publishedDate']))
        ) {
            throw new Error("Invalid date: {$volumeInfo['publishedDate']}");
        }

        if(!isset($volumeInfo['title']) || !is_string($volumeInfo['title'])) {
            throw new Error('Invalid title');
        }

        if(isset($volumeInfo['subtitle'])) {
            if(!is_string($volumeInfo['subtitle'])) {
                throw new Error('Invalid subtitle');
            }
        }

        if(
            !isset($volumeInfo['authors'])
            || !is_array($volumeInfo['authors'])
            || array_filter($volumeInfo['authors'], function($author) { return !is_string($author); })
        ) {
            throw new Error('Invalid authors');
        }

        $book = new static();

        if(isset($isbn10))
            $book->isbn10 = $isbn10;
        if(isset($isbn13))
            $book->isbn13 = $isbn13;
        
        $book->authors = $volumeInfo['authors'];
        $book->title = $volumeInfo['title'];
        $book->subtitle = $volumeInfo['subtitle'] ?? '';
        $book->publisher = $volumeInfo['publisher'];
        $book->datePublished = $datePublished;

        return $book;
    }

    protected static function callGoogleBooksAPIv1(string $endpoint, array $arguments, string $key, int $timeout = 4): array {
        try {
            $arguments['key'] = $key;
            $url = self::GOOGLE_BOOKS_ENDPOINT . $endpoint . ($arguments ? ('?' . http_build_query($arguments)) : '');

            $rest = curl_init();
            if (!$rest) {
                throw new Error('Cannot init cURL');
            }

            $options = [
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => [
                    'Content-Type' => 'applcation/json',
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_ENCODING => 'gzip',
                CURLOPT_FAILONERROR => true,
            ];
            if (!curl_setopt_array($rest, $options)) {
                throw new Error('Cannot set options to curl');
            }
            if (!($response = curl_exec($rest))) {
                throw new Error(curl_error($rest), curl_errno($rest));
            }
        } finally {
            curl_close($rest);
        }

        if (!($response = json_decode($response, true))) {
            throw new Error('Cannot decode ISBN API response');
        }

        return $response;
    }

    public static function fromGoogleBookAPIv1ByISBN(string $isbn, string $key, int $timeout = 4): Book {
        $isbn = str_replace(['-', ' '], '', $isbn);
        if(!is_numeric($isbn)) {
            throw new Error("Invalid ISBN [$isbn]");
        }
        return static::fromGoogleBooksAPIv1(static::callGoogleBooksAPIv1('/volumes', ['q' => 'isbn:' . rawurlencode($isbn)], $key, $timeout));
    }

    public function __toString(): string {
        $authors = join(', ', array_slice($this->authors, 0, 2));
        if(count($this->authors) > 2) {
            $authors .= ' & al.';
        }
        $str = sprintf("%s; %s%s, %s (%s)", $authors, $this->title, $this->subtitle ? (' - ' . $this->subtitle) : '', $this->publisher, $this->datePublished->format('Y'));
        $isbn = $this->isbn10 ? $this->isbn10 : ($this->isbn13 ? $this->isbn13 : '');
        if($isbn) {
            $str .= " ISBN: {$isbn}";
        }
        return $str;
    }
}
