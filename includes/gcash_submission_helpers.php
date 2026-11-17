<?php
// Shared helpers for private GCash proof storage and configurable recipient details.

function gcash_get_payment_settings(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT RecipientName, MobileNumber, Instructions FROM GcashPaymentSettings WHERE SettingsID = 1 LIMIT 1");
    return $stmt->fetch() ?: [];
}

/**
 * Store validated raster images outside the public web root. Set
 * NEWYOU_PRIVATE_UPLOAD_DIR to an absolute, writable directory if needed.
 * Returns only the server-generated basename stored in the database.
 */
function gcash_private_upload_dir(): string
{
    $configured = getenv('NEWYOU_PRIVATE_UPLOAD_DIR');
    $directory = $configured
        ? rtrim($configured, DIRECTORY_SEPARATOR)
        : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'newyou_private' . DIRECTORY_SEPARATOR . 'gcash_proofs';

    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('The private proof upload directory is unavailable. Please contact the gym.');
    }
    $realDirectory = realpath($directory);
    if ($realDirectory === false) {
        throw new RuntimeException('The private proof upload directory is unavailable.');
    }

    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $realDocumentRoot = $documentRoot !== '' ? realpath($documentRoot) : false;
    if ($realDocumentRoot && ($realDirectory === $realDocumentRoot || strpos($realDirectory, $realDocumentRoot . DIRECTORY_SEPARATOR) === 0)) {
        throw new RuntimeException('Proof uploads must be stored outside the public web root. Configure NEWYOU_PRIVATE_UPLOAD_DIR.');
    }
    return $realDirectory;
}

function gcash_store_proof(array $file): string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Upload a valid proof image. If the upload failed, try a smaller image.');
    }
    if ((int) $file['size'] < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Proof image must be larger than 0 bytes and no more than 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only genuine JPG, PNG, or WebP image files are accepted.');
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if (!$imageInfo || (int) $imageInfo[0] < 1 || (int) $imageInfo[1] < 1
        || (int) $imageInfo[0] > 8000 || (int) $imageInfo[1] > 8000) {
        throw new RuntimeException('The uploaded file is not a supported image or its dimensions are too large.');
    }
    $expectedType = ['image/jpeg' => IMAGETYPE_JPEG, 'image/png' => IMAGETYPE_PNG, 'image/webp' => IMAGETYPE_WEBP];
    if (($imageInfo[2] ?? null) !== $expectedType[$mime]) {
        throw new RuntimeException('The image content does not match its detected file type.');
    }
    if (function_exists('imagecreatefromstring')) {
        $decoded = @imagecreatefromstring(file_get_contents($file['tmp_name']));
        if ($decoded === false) {
            throw new RuntimeException('The uploaded image could not be decoded.');
        }
        imagedestroy($decoded);
    }

    $filename = bin2hex(random_bytes(24)) . '.' . $allowed[$mime];
    $destination = gcash_private_upload_dir() . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('The proof image could not be saved. Please try again.');
    }
    @chmod($destination, 0600);
    return $filename;
}

function gcash_delete_proof(string $filename): void
{
    if (!preg_match('/^[a-f0-9]{48}\.(jpg|png|webp)$/', $filename)) {
        return;
    }
    $path = gcash_private_upload_dir() . DIRECTORY_SEPARATOR . $filename;
    if (is_file($path)) {
        @unlink($path);
    }
}
