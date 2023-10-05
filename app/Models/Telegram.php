<?php

namespace App\Models;

class Telegram
{
    private static $access_token = '6369686736:AAEdxe_a7VkrhZSDcBYGnuRsB7T3a1lGe8U';
    private static $api = 'https://api.telegram.org/bot';

    public static function sendMessage($chat_id, $message, $params = '')
    {
        $url = self::$api.self::$access_token . '/sendMessage?chat_id=' . $chat_id . $params . '&text=' . urlencode($message);
        
        return self::getCurl($url);
    }

    public static function deleteMessage($chat_id, $message_id)
    {
        $url = self::$api.self::$access_token . '/deleteMessage?chat_id=' . $chat_id .'&message_id=' . $message_id;

        return self::getCurl($url);
    }

    public static function sendMessageR($chat_id, $message, $replyMarkup)
    {
        $url = self::$api.self::$access_token . '/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message).'&reply_markup=' . $replyMarkup;

        return self::getCurl($url);
    }


    private static function getCurl ($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $re = curl_exec($ch);
        curl_close($ch);

        return $re;
    }
}
