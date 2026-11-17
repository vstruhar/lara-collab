<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TaskAttachmentService
{
    public const DISK = 'attachments';

    public const MAX_KILOBYTES = 20 * 1024;

    /**
     * @var array<string, string>
     */
    private const CONTENT_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'bmp' => 'image/bmp',
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip' => 'application/zip',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
    ];

    /**
     * @var array<int, string>
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    /**
     * Office Open XML files are zip archives, so a content sniff often reports zip.
     *
     * @var array<int, string>
     */
    private const ZIP_DOCUMENTS = ['docx', 'xlsx', 'pptx'];

    public static function rule(): File
    {
        return File::types(self::extensions())
            ->extensions(self::extensions())
            ->max(self::MAX_KILOBYTES);
    }

    /**
     * @return array<int, string>
     */
    public static function extensions(): array
    {
        return array_keys(self::CONTENT_TYPES);
    }

    /**
     * @return array{user_id: int|string|null, name: string, path: string, thumb: string|null, type: string, size: int}
     */
    public function storeUploadedFile(Task $task, UploadedFile $file): array
    {
        $extension = $this->storedExtension($file);
        $filename = strtolower((string) Str::ulid()).'.'.$extension;
        $directory = "tasks/{$task->id}";
        $filepath = $directory.'/'.$filename;

        $stored = $file->storeAs($directory, $filename, ['disk' => self::DISK]);

        if ($stored === false) {
            throw ValidationException::withMessages([
                'attachments' => 'The file could not be stored.',
            ]);
        }

        return [
            'user_id' => auth()->id(),
            'name' => $this->displayName($file),
            'path' => $filepath,
            'thumb' => $this->storeThumbnail($file, $task, $filename, $extension),
            'type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => (int) $file->getSize(),
        ];
    }

    public function downloadResponse(Attachment $attachment): StreamedResponse
    {
        $location = $this->locate($attachment->path);

        if ($location === null) {
            abort(404);
        }

        [$disk, $path] = $location;

        return Storage::disk($disk)->download(
            $path,
            $this->downloadName($attachment->name, $path),
            $this->responseHeaders($path),
        );
    }

    public function thumbResponse(Attachment $attachment): StreamedResponse
    {
        if (! $this->isThumbPath((string) $attachment->thumb)) {
            abort(404);
        }

        $location = $this->locate($attachment->thumb);

        if ($location === null || ! $this->isThumbPath($location[1])) {
            abort(404);
        }

        [$disk, $path] = $location;

        return Storage::disk($disk)->response(
            $path,
            $this->downloadName($attachment->name, $path),
            $this->responseHeaders($path, false),
            'inline',
        );
    }

    public function delete(Attachment $attachment): void
    {
        $this->deleteStored($attachment->path);
        $this->deleteStored($attachment->thumb);
    }

    public function moveLegacyPublicFiles(): void
    {
        Attachment::query()->orderBy('id')->chunkById(100, function ($attachments) {
            foreach ($attachments as $attachment) {
                $path = $this->moveLegacyColumn($attachment->path);
                $thumb = $this->moveLegacyColumn($attachment->thumb);

                if ($path === $attachment->path && $thumb === $attachment->thumb) {
                    continue;
                }

                $attachment->path = $path;
                $attachment->thumb = $thumb;
                $attachment->save();
            }
        });

        foreach (Storage::disk('public')->allFiles('tasks') as $relative) {
            $this->movePublicFile($relative);
        }
    }

    public function locate(?string $stored): ?array
    {
        $relative = $this->relativePath($stored);

        if ($relative === null || ! str_starts_with($relative, 'tasks/')) {
            return null;
        }

        if (Storage::disk(self::DISK)->exists($relative)) {
            return [self::DISK, $relative];
        }

        if (Storage::disk('public')->exists($relative)) {
            return ['public', $relative];
        }

        return null;
    }

    public function relativePath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', trim($path));

        if (str_starts_with($normalized, '/storage/')) {
            $normalized = substr($normalized, strlen('/storage/'));
        }

        $normalized = ltrim($normalized, '/');

        if ($normalized === '' || str_contains($normalized, "\0")) {
            return null;
        }

        foreach (explode('/', $normalized) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return null;
            }
        }

        return $normalized;
    }

    public function isThumbPath(string $path): bool
    {
        $relative = $this->relativePath($path);

        if ($relative === null || ! preg_match('#^tasks/\d+/thumbs/#', $relative)) {
            return false;
        }

        return in_array($this->extensionFromPath($relative), self::IMAGE_EXTENSIONS, true);
    }

    public function storedExtension(UploadedFile $file): string
    {
        $guessed = strtolower($file->guessExtension() ?? '');
        $clientExtension = strtolower($file->getClientOriginalExtension());

        if ($guessed === 'zip' && in_array($clientExtension, self::ZIP_DOCUMENTS, true)) {
            return $clientExtension;
        }

        if (! in_array($guessed, self::extensions(), true)) {
            throw ValidationException::withMessages([
                'attachments' => 'This file type is not allowed.',
            ]);
        }

        return $guessed;
    }

    public function downloadName(string $originalName, string $storedPath): string
    {
        $extension = $this->extensionFromPath($storedPath);
        $base = pathinfo($this->displayNameFromString($originalName), PATHINFO_FILENAME);
        $base = trim(Str::limit($base, 180, ''));

        if ($base === '') {
            $base = 'attachment';
        }

        if ($extension === '') {
            return $base;
        }

        return $base.'.'.$extension;
    }

    private function displayName(UploadedFile $file): string
    {
        return Str::limit($this->displayNameFromString($file->getClientOriginalName()), 255, '');
    }

    private function displayNameFromString(string $name): string
    {
        $name = str_replace(["\0", "\r", "\n", '/', '\\', '"'], '', $name);
        $name = basename($name);

        return $name !== '' ? $name : 'attachment';
    }

    private function storeThumbnail(UploadedFile $file, Task $task, string $filename, string $extension): ?string
    {
        if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return null;
        }

        try {
            $thumbFilepath = "tasks/{$task->id}/thumbs/{$filename}";

            $encoded = ImageManager::usingDriver(GdDriver::class)
                ->decodeBinary($file->get())
                ->cover(100, 100)
                ->encodeUsingFileExtension(
                    $extension,
                    ...$this->thumbnailEncodeOptions($extension),
                );

            Storage::disk(self::DISK)->put($thumbFilepath, (string) $encoded);

            return $thumbFilepath;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @return array<string, int>
     */
    private function thumbnailEncodeOptions(string $extension): array
    {
        return match (strtolower($extension)) {
            'jpg', 'jpeg', 'webp' => ['quality' => 75],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    private function responseHeaders(string $path, bool $sandboxed = true): array
    {
        $extension = $this->extensionFromPath($path);

        $headers = [
            'Content-Type' => self::CONTENT_TYPES[$extension] ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];

        if ($sandboxed) {
            $headers['Content-Security-Policy'] = "default-src 'none'; sandbox";
        }

        return $headers;
    }

    private function extensionFromPath(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    private function moveLegacyColumn(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return $stored;
        }

        $relative = $this->relativePath($stored);

        if ($relative === null || ! str_starts_with($relative, 'tasks/')) {
            return $stored;
        }

        if (! $this->movePublicFile($relative)) {
            return $stored;
        }

        return $relative;
    }

    private function movePublicFile(string $relative): bool
    {
        $public = Storage::disk('public');
        $private = Storage::disk(self::DISK);

        if (! $public->exists($relative)) {
            return true;
        }

        if (! $private->exists($relative)) {
            $stream = $public->readStream($relative);

            if (! is_resource($stream)) {
                return false;
            }

            $written = $private->put($relative, $stream);
            fclose($stream);

            if ($written === false) {
                return false;
            }
        }

        $public->delete($relative);

        return true;
    }

    private function deleteStored(?string $stored): void
    {
        $relative = $this->relativePath($stored);

        if ($relative === null || ! str_starts_with($relative, 'tasks/')) {
            return;
        }

        Storage::disk(self::DISK)->delete($relative);
        Storage::disk('public')->delete($relative);
    }
}
