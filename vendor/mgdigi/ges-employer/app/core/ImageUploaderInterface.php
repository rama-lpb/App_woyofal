<?php
namespace App\Core;

interface ImageUploaderInterface {
    public function upload(string $filePath): string;
    public function delete(string $publicId): bool;
}
