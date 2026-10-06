<?php

namespace CMSCore\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Path
{
    protected string $disk;

    public function __construct(string $disk)
    {
        $this->disk = $disk;
    }

    /**
     * constructs a new Path object with disk
     *
     * Example Usage:
     * Path::disk('public')->exists();
     */
    public static function disk(string $disk): Path
    {
        return new self($disk);
    }

    public function isAbsolute(string $path): bool
    {
        return
            str_starts_with($path, DIRECTORY_SEPARATOR) ||
            preg_match('/^[a-zA-Z]:[\\\\\/]/', $path);
    }

    public function isURL(string $path): bool
    {
        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check if the URL provided belongs to the current site
     */
    public function isValidSiteURL(string $url): bool
    {
        if (! $this->isURL($url)) {
            return false;
        }

        // Get host from the URL
        $urlHost = parse_url($url, PHP_URL_HOST);

        // Get current app host
        $appHost = parse_url(url('/'), PHP_URL_HOST);

        // Check if belongs to current site
        return $urlHost === $appHost;
    }

    public function getAbsolute(string $path): string
    {
        if ($this->isAbsolute($path)) {
            return $path;
        }

        return Storage::disk($this->disk)->path($path);
    }

    public function getRelative(string $path): string
    {
        if (! $this->isAbsolute($path)) {
            return $path;
        }

        $diskRoot = rtrim(Storage::disk($this->disk)->path(''), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return ltrim(Str::after($path, $diskRoot), '/');
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($this->getRelative($path));
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($this->getRelative($path));
    }

    public function put(string $path, $contents): bool
    {
        return Storage::disk($this->disk)->put($this->getRelative($path), $contents);
    }

    public function getExtension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    public function getUrl(string $path, bool $absolute = true): string
    {
        $url = Storage::disk($this->disk)->url($this->getRelative($path));

        if ($absolute) {
            return url($url);
        }

        return $url;
    }

    public function extractPathFromUrl(string $input, bool $relative = true): string
    {
        // "http://127.0.0.1:8000/storage/a8d59a07-7995-4157-81bd-dd17e4062536/"
        $diskRoot = Storage::disk($this->disk)->url('/');
        // "/storage/a8d59a07-7995-4157-81bd-dd17e4062536/"
        $diskRootRelative = parse_url($diskRoot, PHP_URL_PATH);

        // If it's a URL, extract the path after /storage
        // http://127.0.0.1:8000/storage/a8d59a07-7995-4157-81bd-dd17e4062536/16/4.svg
        if ($this->isURL($input)) {
            // 16/4.svg
            $path = Str::after(parse_url($input, PHP_URL_PATH), $diskRootRelative);

        } else {
            $path = ltrim($input, '/');
        }

        return $relative ? $path : Storage::disk($this->disk)->path($path);
    }
}
