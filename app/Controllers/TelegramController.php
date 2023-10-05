<?php

namespace App\Controllers;

use App\Models\Telegram;
use App\Models\DB;

class TelegramController
{
    public static function index ($response = null) {
        $db = new DB();
        if(isset($response->message)) {
            $message = $response->message;
            if($message->text == '/start@yellowduck_testbot' || $message->text == '/gettasks@yellowduck_testbot') {
                $message->text = str_replace('@yellowduck_testbot', '', $message->text);
            }
            
            switch ($message->text) {
            	case '/start':
                    $query = "INSERT INTO members SET telegram_id = :telegram_id, username = :username, first_name = :first_name ON DUPLICATE KEY UPDATE username = :username, first_name = :first_name";
                    $data = [
                        'telegram_id' => $message->from->id,
                        'username' => $message->from->username?$message->from->username:null,
                        'first_name' => $message->from->first_name,
                    ];
                    $db->setData($query, $data);
            		Telegram::sendMessage($message->from->id, "Hello {$message->from->first_name}!\n\nWelcome to Yellow Duck!");

                    //invite to Trello Board
                    Telegram::sendMessage($message->from->id, "If your want connecting to Trello Board, send your email, please!");
            		break;

                case '/gettasks':
                    TrelloController::getActiveTasks($response->message->from->id);
                    break;
            }

            if(preg_match('/^\w.*@\w.*\.\w.*\w$/', $message->text)) {
                $query = "UPDATE IGNORE members SET email = :email, username = :username, first_name = :first_name WHERE telegram_id = :id";
                $data = [
                    'id' => $message->from->id,
                    'email' => $message->text,
                    'username' => $message->from->username?$message->from->username:null,
                    'first_name' => $message->from->first_name,
                ];

                $db->updateData($query, $data) 
                    ? TrelloController::connectMember($message->from, $message->text)
                    : Telegram::sendMessage($message->from->id, "This email already exist. Please, ask admin!");
            }
        }
    }

            

    public static function  getMessage() {
        $response = json_decode(file_get_contents('php://input'));
        self::index($response);
    }


    

}
