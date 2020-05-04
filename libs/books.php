<?php
class Book {
	const ENDPOINT = 'https://api2.isbndb.com';

    public $titleLong;
	public $authors;
    public $isbn;
    public $edition;
    public $publisher;
    public $datePublished;

    public static function fromAPI(array $info): Book {
        if (!(isset($info['title_long']) && is_string($info['title_long']))) {
            throw new Error('API did not return valid title');
        }
        if (!(isset($info['isbn']) && is_string($info['isbn']))) {
            throw new Error('API did not return valid isbn');
        }
        if (!(
            isset($info['authors']) &&
            is_array($info['authors']) &&
            !array_filter($info['authors'], function($a) { return !is_string($a); })
        )) {
            throw new Error('API did not return valid authors');
        }

        if (!(isset($info['publisher']) && is_string($info['publisher']))) {
            throw new Error('API did not return valid publisher');
        }

        if (!(isset($info['edition']) && is_string($info['edition']))) {
            throw new Error('API did not return valid edition');
        }

        if (!(
            isset($info['date_published']) &&
            is_string($info['date_published']) &&
            !($datePublished = new \DateTime($info['date_published']))
        )) {
            throw new Error('API did not return valid published date');
        }

        $book = new static();

        $book->isbn = $info['isbn'];
        $book->authors = $info['authors'];
        $book->titleLong = $info['title_long'];
        $book->edition = $info['edition'];
        $book->publisher = $info['publisher'];
        $book->datePublished = $datePublished;

        return $book;
    }

    protected static function callAPI(string $endpoint, string $authorization, int $timeout = 4): array {
        try {
            $rest = curl_init();
            if (!$rest) {
                throw new Error('Cannot init cURL');
            }
            $options = [
                CURLOPT_URL => self::ENDPOINT . $endpoint,
                CURLOPT_HTTPHEADER => [
                    'Content-Type' => 'applcation/json',
                    'Authorization' => $authorization,
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
            ];
            if (!curl_setopt_array($rest, $options)) {
                throw new Error('Cannot set options to curl');
            }
            if (!($response = curl_exec($rest))) {
                throw new Error(curl_error($rest));
            }
        } finally {
            curl_close($rest);
        }

        if (!($response = json_decode($response, true))) {
            throw new Error('Cannot decode ISBN API response');
        }

        return $response;
    }

	public static function fromISBN(string $isbn, string $authorization, int $timeout = 4): Book {
        return static::fromAPI(
            static::callAPI(
                '/book/' . str_replace(['-', ' '], '', $isbn),
                $authorization,
                $timeout
            )
        );
	}
}
