<?php
include 'Telegram.php';

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Telegram_lib
{

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function sendmsg($bot_token = false, $chat_id = false, $msg)
    {
        $telegram = new Telegram($bot_token);
        $chat_id = $chat_id;
        $content = array('chat_id' => $chat_id, 'text' => $msg, 'parse_mode' => 'html');
        return $telegram->sendMessage($content);
    }
    public function sendlocation($bot_token = false, $chat_id = false, $lat, $long)
    {
        $telegram = new Telegram($bot_token);
        $chat_id = $chat_id;
        $content = ['chat_id' => $chat_id, 'latitude' => $lat, 'longitude' => $long];
        return $telegram->sendLocation($content);
    }
    public function sendimg($bot_token = false, $chat_id = false, $img_path, $caption = false)
    {
        $telegram = new Telegram($bot_token);
        $chat_id = $chat_id;
        $img = curl_file_create('test.png', 'image/png');
        $content = ['chat_id' => $chat_id, 'photo' => new CURLFile(realpath($img_path)), 'caption' => $caption];
        return $telegram->sendPhoto($content);
    }
    public function sendaudio($bot_token = false, $chat_id = false, $audio_path, $caption = false)
    {
        $telegram = new Telegram($bot_token);
        $chat_id = $chat_id;
        $content = ['chat_id' => $chat_id, 'audio' => new CURLFile(realpath($audio_path)), 'caption' => $caption];
        return $telegram->sendAudio($content);
    }
    public function senddoc($bot_token = false, $chat_id = false, $doc_path, $caption = false)
    {
        $telegram = new Telegram($bot_token);
        $chat_id = $chat_id;
        $content = ['chat_id' => $chat_id, 'document' => new CURLFile(realpath($doc_path)), 'caption' => $caption];
        return $telegram->sendDocument($content);
    }
    public function sendvenue($bot_token = false, $chat_id = false, $lat, $long, $title, $address)
    {
        $telegram = new Telegram($bot_token);
        $chat_id = $chat_id;
        $content = ['chat_id' => $chat_id, 'latitude' => $lat, "longitude" => $long, "title" => $title, "address" => $address];
        return $telegram->sendVenue($content);
    }
    public function sendcontact($bot_token = false, $chat_id = false, $phone, $f_name, $l_name = false)
    {
        $telegram = new Telegram($bot_token);
        $chat_id = $chat_id;
        $content = ['chat_id' => $chat_id, 'phone_number' => $phone, "first_name" => $f_name, "last_name" => $l_name];
        return $telegram->sendContact($content);
    }
	
	public function getupdates($bot_token = false)
    {
        $telegram = new Telegram($bot_token);
        return $telegram->getUpdates();
    }
	
}
