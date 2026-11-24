<?php

function resolveBookCoverImage(array $postData): string
{
    if (!empty($_FILES['cover_file']['name']) && is_uploaded_file($_FILES['cover_file']['tmp_name'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $_FILES['cover_file']['tmp_name']) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new InvalidArgumentException('Upload a JPG, PNG, WEBP, GIF, or SVG cover image.');
        }

        $uploadDirectory = __DIR__ . '/assets/books/uploads';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('Unable to create the uploads folder for book covers.');
        }

        $extension = strtolower(pathinfo($_FILES['cover_file']['name'], PATHINFO_EXTENSION));
        if ($extension === '') {
            if ($mimeType === 'image/jpeg') {
                $extension = 'jpg';
            } elseif ($mimeType === 'image/png') {
                $extension = 'png';
            } elseif ($mimeType === 'image/webp') {
                $extension = 'webp';
            } elseif ($mimeType === 'image/gif') {
                $extension = 'gif';
            } elseif ($mimeType === 'image/svg+xml') {
                $extension = 'svg';
            } else {
                $extension = 'png';
            }
        }

        $fileName = 'custom-book-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination = $uploadDirectory . '/' . $fileName;

        if (!move_uploaded_file($_FILES['cover_file']['tmp_name'], $destination)) {
            throw new RuntimeException('The uploaded cover image could not be saved.');
        }

        return 'assets/books/uploads/' . $fileName;
    }

    $coverImage = trim((string) ($postData['cover_image'] ?? ''));
    $coverImage = str_replace('\\', '/', $coverImage);

    if ($coverImage === '') {
        return 'assets/books/default-book.svg';
    }

    if (strpos($coverImage, '../') !== false || $coverImage[0] === '/' || strpos($coverImage, 'assets/') !== 0) {
        return 'assets/books/default-book.svg';
    }

    return $coverImage;
}

function sendCsvDownload(string $filename, array $headers, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');

    $output = fopen('php://output', 'wb');
    if ($output === false) {
        throw new RuntimeException('Unable to create the requested export.');
    }

    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, $headers, ',', '"', '\\');
    foreach ($rows as $row) {
        $safeRow = array_map(static function ($value) {
            if (is_string($value) && preg_match('/^[\t\r ]*[=+\-@]/', $value)) {
                return "'" . $value;
            }

            return $value;
        }, $row);
        fputcsv($output, $safeRow, ',', '"', '\\');
    }

    fclose($output);
    exit;
}
