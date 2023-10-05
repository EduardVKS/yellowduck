<?php

spl_autoload_register(function($class_name) {
    $path_to_class = str_replace('\\', '/', $class_name);
    include $path_to_class . ".php";
});


$path = isset($_SERVER['REDIRECT_URL'])?$_SERVER['REDIRECT_URL']:'/';

new App\Models\DB(require 'config_db.php');

switch ($path) {
	case '/':
		App\Models\Telegram::sendMessage('886391816', 'tesy');
		break;

	case '/telegram/getmessage':
		App\Controllers\TelegramController::getMessage();
		break;

	case '/trello/getmessage':
		App\Controllers\TrelloController::getMessage($_POST);
		break;

	case '/trello/setwebhook':
		App\Controllers\TrelloController::setWebhook();
		break;

		case '/trello/connectMember':
		App\Controllers\TrelloController::connectMember();
		break;
	
	default:
		die('404 - this page not found');
		break;
}



