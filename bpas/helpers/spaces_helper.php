<?php
if (!function_exists('upload_to_spaces')) {
    function upload_to_spaces($file_path, $file_name = null) {
        $CI =& get_instance();
        $CI->load->config('spaces');
        
        $s3 = new Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => $CI->config->item('spaces')['region'],
            'endpoint' => $CI->config->item('spaces')['endpoint'],
            'credentials' => [
                'key'    => $CI->config->item('spaces')['key'],
                'secret' => $CI->config->item('spaces')['secret'],
            ]
        ]);
        
        $file_name = $file_name ?: basename($file_path);
        $key = $CI->config->item('spaces')['folder'] . $file_name;
        
        try {
            $result = $s3->putObject([
                'Bucket' => $CI->config->item('spaces')['bucket'],
                'Key'    => $key,
                'Body'   => fopen($file_path, 'r'),
                'ACL'    => 'public-read'
            ]);
            
            return $result->get('ObjectURL');
        } catch (Aws\S3\Exception\S3Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('get_spaces_url')) {
    function get_spaces_url($file_name) {
        $CI =& get_instance();
        $CI->load->config('spaces');
        
        return $CI->config->item('spaces')['endpoint'] . '/' . 
               $CI->config->item('spaces')['bucket'] . '/' . 
               $CI->config->item('spaces')['folder'] . $file_name;
    }
}
?>