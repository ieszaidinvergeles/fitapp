<?php

namespace App\Contracts;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

/**
 * Contract that defines all image storage operations for the application.
 *
 * DIP: Controllers and other consumers depend on this interface, not on any
 *      concrete implementation. Swapping storage drivers (local, S3, etc.)
 *      requires changing only the binding in AppServiceProvider.
 *
 * ISP: Declares only the operations required by image-owning controllers.
 *      No unrelated methods are present.
 */
interface ImageServiceInterface
{
    /**
     * Stores an uploaded image file in the private disk under the given folder,
     * using the entity's primary key as the filename.
     *
     * @param  UploadedFile  $file      The validated uploaded file.
     * @param  string        $folder    The subdirectory within images/ (e.g. 'exercises').
     * @param  int           $entityId  The entity's primary key, used as the filename.
     * @return string                   The stored relative path (e.g. 'images/exercises/42.jpg').
     */
    public function upload(UploadedFile $file, string $folder, int $entityId): string;

    /**
     * Deletes an image from the private disk.
     * Silently returns true if the path is null or the file does not exist.
     *
     * @param  string|null  $relativePath  The relative path returned by upload().
     * @return bool
     */
    public function delete(?string $relativePath): bool;

    /**
     * Replaces an existing image with a new upload.
     * Deletes the old file first (if present), then stores the new one.
     *
     * @param  UploadedFile  $file      The new validated uploaded file.
     * @param  string        $folder    The subdirectory within images/.
     * @param  int           $entityId  The entity's primary key.
     * @param  string|null   $oldPath   The current relative path to delete, or null.
     * @return string                   The new relative path.
     */
    public function replace(UploadedFile $file, string $folder, int $entityId, ?string $oldPath): string;

    /**
     * Returns an HTTP response that streams the image file from the private disk.
     * Sets Cache-Control: private so browsers cache locally but shared proxies do not.
     *
     * @param  string  $relativePath  The relative path stored in the database.
     * @return Response
     */
    public function stream(string $relativePath): Response;

    /**
     * Determines whether a file exists at the given relative path on the private disk.
     *
     * @param  string|null  $relativePath
     * @return bool
     */
    public function exists(?string $relativePath): bool;
}
