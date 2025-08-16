<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Spaces {
    private $s3Client;
    
    public function __construct() {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => 'your-space-region', // e.g., 'nyc3'
            'endpoint' => 'https://your-space-region.digitaloceanspaces.com',
            'credentials' => [
                'key'    => 'YOUR_SPACES_KEY',
                'secret' => 'YOUR_SPACES_SECRET',
            ],
        ]);
    }
    
    public function uploadFile($bucket, $key, $sourceFile) {
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'SourceFile' => $sourceFile,
                'ACL'    => 'public-read' // if you want the file to be public
            ]);
            return $result['ObjectURL'];
        } catch (AwsException $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
    
    public function deleteFile($bucket, $key) {
        try {
            $result = $this->s3Client->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $key
            ]);
            return true;
        } catch (AwsException $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
    
    // Add more methods as needed (getFile, listFiles, etc.)
}