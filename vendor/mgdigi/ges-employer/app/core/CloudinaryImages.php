<?php

namespace App\Core;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class CloudinaryImages implements ImageUploaderInterface {

    private Cloudinary $cloudinary;

    public function __construct()
    {
        $config = require '../app/config/cloudinary.php';

        Configuration::instance([
            'cloud' => [
                'cloud_name' => $config['cloud_name'],
                'api_key'    => $config['api_key'],
                'api_secret' => $config['api_secret'],
            ],
            'url' => ['secure' => true]
        ]);

        $this->cloudinary = new Cloudinary(Configuration::instance());
    }

    public function upload(string $filePath): string
    {
        $response = $this->cloudinary->uploadApi()->upload($filePath);
        return $response['secure_url'] ?? '';
    }

    public function delete(string $publicId): bool
    {
        $response = $this->cloudinary->uploadApi()->destroy($publicId);
        return ($response['result'] ?? '') === 'ok';
    }
}
