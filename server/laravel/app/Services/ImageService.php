<?php

namespace App\Services;

use App\Contracts\ImageServiceInterface;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles all image file I/O operations against the private storage disk.
 *
 * SRP: Solely responsible for storing, replacing, deleting and streaming
 *      image files. It has no knowledge of HTTP routing or Eloquent models.
 *
 * OCP: New disk strategies (S3, Cloudinary) are added by creating a new
 *      implementation of ImageServiceInterface without modifying this class.
 *
 * LSP: Fully substitutable for ImageServiceInterface in any consumer.
 */
class ImageService implements ImageServiceInterface
{
    /**
     * The Laravel filesystem disk name to use for all image operations.
     *
     * @var string
     */
    private const DISK = 'private';

    /**
     * The base directory inside the disk root where all images are stored.
     *
     * @var string
     */
    private const BASE_DIR = 'images';

    /**
     * Allowed MIME types for uploaded images.
     *
     * @var list<string>
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    /**
     * {@inheritdoc}
     */
    public function upload(UploadedFile $file, string $folder, int $entityId): string
    {
        $extension    = $file->getClientOriginalExtension() ?: $file->extension();
        $relativePath = self::BASE_DIR . '/' . $folder . '/' . $entityId . '.' . $extension;

        Storage::disk(self::DISK)->putFileAs(
            self::BASE_DIR . '/' . $folder,
            $file,
            $entityId . '.' . $extension
        );

        return $relativePath;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(?string $relativePath): bool
    {
        if ($relativePath === null || !Storage::disk(self::DISK)->exists($relativePath)) {
            return true;
        }

        return Storage::disk(self::DISK)->delete($relativePath);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(UploadedFile $file, string $folder, int $entityId, ?string $oldPath): string
    {
        $this->delete($oldPath);

        return $this->upload($file, $folder, $entityId);
    }

    /**
     * {@inheritdoc}
     */
    public function stream(string $relativePath): Response
    {
        if (!Storage::disk(self::DISK)->exists($relativePath)) {
            throw new NotFoundHttpException('Image not found.');
        }

        $absolutePath = Storage::disk(self::DISK)->path($relativePath);
        $mimeType     = Storage::disk(self::DISK)->mimeType($relativePath);

        return response()->file($absolutePath, [
            'Content-Type'  => $mimeType ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(?string $relativePath): bool
    {
        if ($relativePath === null) {
            return false;
        }

        return Storage::disk(self::DISK)->exists($relativePath);
    }
}
