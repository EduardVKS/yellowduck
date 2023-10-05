<?php

namespace App\Controllers;

use App\Models\Telegram;
use App\Models\DB;

class TrelloController
{
    const KEY = 'b6990d82417b4aeeffffcac8a72ce15f';
    const TOKEN = 'ATTAe7a27e65a08b1c5baf82fc455fdd3853899ce891b4c1113478f98f576c7ee810D6540DF8';

    public static function  setWebhook() {
        $url = "https://api.trello.com/1/webhooks";
        $post = [
            'callbackURL' => 'https://yellowducktest.000webhostapp.com/trello/getmessage',
            'idModel' => '651e544bc3d0428e737f486b',
        ];
        self::sendCurl($url, 'POST', $post);
    }

    public static function  getMessage() {
        $response = json_decode(file_get_contents('php://input'));
        $response ? self::index($response) : null;
    }

    public static function index ($response) {
        $board = $response->model->name;
        $card = $response->action->data->card->name;

        $type = $response->action->display->translationKey;

        if($type == 'action_create_card') {

            $list = $response->action->data->list->name;
            $message = "$board\n\nCreated new Card\nName: $card\n\nLists: $list";
        } elseif ($type == 'action_renamed_card') {

            $list = $response->action->data->list->name;
            $message = "$board\n\nCard was edited\nNew name: $card\n\nLists: $list";
        } elseif ($type == 'action_move_card_from_list_to_list') {

            $move_from = $response->action->data->listBefore->name;
            $move_to = $response->action->data->listAfter->name;
            $message = "$board\n\nCard: $card\n\nMoved\nForm: $move_from\nTo: $move_to";
        }
        
        isset($message) ? Telegram::sendMessage('-1001846405860', $message) : null;
    }


    public static function connectMember ($member, $email) {
        $url = "https://api.trello.com/1/boards/651e544bc3d0428e737f486b/members";
        $post = [
            'email'=> $email,
        ];

        self::sendCurl($url, 'PUT', $post);
        self::searchMember($member, $email);
    }


    public static function searchMember ($member, $email) {
        $url = "https://api.trello.com/1/search/members";
        $post = [
            'query'=> $email,
        ];
        $re = self::sendCurl($url, 'GET', $post);

        if(!isset($re[0]) || !$re[0]->id) return;

        $db = new DB();
        $query = "UPDATE IGNORE members SET trello_id = :trello_id WHERE telegram_id = :id";
        $data = [
            'id' => $member->id,
            'trello_id' => $re[0]->id,
        ];
        $db->updateData($query, $data);

        Telegram::sendMessage($member->id, "You have joined to board!");
    }


    public static function getActiveTasks ($chat) {
        $url = "https://api.trello.com/1/lists/651e544c1eef085f5882867a/cards";
        $cards = self::sendCurl($url);

        $db = new DB();
        $query = "SELECT trello_id, members.first_name FROM members WHERE trello_id IS NOT NULL";
        $members = $db->getDataAll($query, [], 'FETCH_GROUP');

        $message = "Results\n";
        foreach($cards as $card) {
            $message .= "\nTask Name: {$card->name}\n";
            foreach ($card->idMembers as $member) {
                isset($members[$member]) ? $message .= $members[$member][0]."\n" : null;
            }
        }
        Telegram::sendMessage($chat, $message);
        
    }

    private static function sendCurl ($url, $method = 'GET', $query = []) {
        $query = array_merge($query, [
            'key' => self::KEY,
            'token' => self::TOKEN,
        ]);

        ($method == 'GET') ? $url .= '?' . http_build_query($query) : null;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Accept: application/json'));
        ($method == 'PUT') ? curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT") : null;
        ($method == 'POST' || $method == 'PUT') ? curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query)) : null;
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

}
