<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['spaces'] = [
    'key'      => 'DO00PXQC8KAZXWQ6HJFG',
    'secret'   => 'YOUR_DO_SPACES_SECRET',
    'endpoint' => 'https://sbcasset.sgp1.digitaloceanspaces.com', // Change to your region
    'region'   => 'sgp1',
    'bucket'   => 'sbcasset',
    'folder'   => 'uploads/' // Optional folder prefix
];
?>