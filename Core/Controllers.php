<?php /** @noinspection PhpUnused */

namespace Core;

use Core\Methods\SendMessage;
use GuzzleHttp\Client;

/**
 * Here are the main methods for development
 */
trait Controllers
{
    protected string $token = 'your_token';
    protected int $chat_id = -872746594;
    protected int $user_id = 511703056;
    protected string $directory = __DIR__ . "/..";
    protected string $core_directory = __DIR__;
    protected string $storage = __DIR__ . "/../storage/";
    protected string $file_data = __DIR__ . "/../storage/data.json";
    protected string $file_message = __DIR__ . "/../storage/message.txt";
    protected string $file_birthdays = __DIR__ . "/../storage/birthdays.json";

    // also you can add additional protected constants and use it everywhere simply by importing them
    protected string $GPT = "another_token";

    public function writeLogFile(string | array $str, string $file = "message.txt", bool $overwrite = false) : void
    {
        $log_file_name = $this->storage . "$file";
        $now = date("Y-m-d H:i:s");
        if ($overwrite) file_put_contents($log_file_name, '');
        file_put_contents($log_file_name, $now . " " . print_r($str, true) .  "\r\n", FILE_APPEND);
    }

    public function saveDataToJson(array $data, string $file_name = "data.json", bool $overwrite = false) : void
    {
        $file_link = $this->storage . "$file_name";
        $file_content = json_decode(file_get_contents($file_link)) ?? [];
        (!$overwrite) ? array_push($file_content, $data) : $file_content = $data;
        file_put_contents($file_link, json_encode($file_content));
    }

    public function saveFile(bool $withLog = false, string $logPath = 'data.json') : array
    {
        $request = json_decode(file_get_contents('php://input'), true);

        // RECEIVE FILE
        if (!empty($request["message"]["photo"])) {

            $file = [
                "file_id" => $request["message"]["photo"][3]["file_id"],
            ];

            $response = $this->client([
                    'verify' => false,
                ])
                ->post('https://api.telegram.org/bot'
                    . $this->token . "/getFile?"
                    . http_build_query($file), [
                'form_params' => $request
                ]);

            $result = $response->getBody()->getContents();

            // записываем ответ в формате PHP массива
            $dataResult = json_decode($result, true);
            // записываем URL необходимого изображения
            $fileUrl = $dataResult["result"]["file_path"];
            // формируем полный URL до файла
            $photoPathTG = "https://api.telegram.org/file/bot" . $this->token . "/" . $fileUrl;

            if ($withLog) {
                $this->writeLogFile($photoPathTG, $withLog, true);
                $this->saveDataToJson($request, $logPath);
            }
            
            // забираем название файла
            $newFilePath = $this->storage . "img/" . explode("/", $fileUrl)[1];
            // сохраняем файл на серсер
            file_put_contents($newFilePath, file_get_contents($photoPathTG));
        }
        return $request;
    }

    public function client(array $ClientOptionsArray = []) : object
    {
        return new Client($ClientOptionsArray);
    }

    /** @noinspection PhpNoReturnAttributeCanBeAddedInspection */
    public function dd(array $request, bool $disable_notification = true, bool $allow_sending_without_reply = true) : void
    {
        $response = new SendMessage;
        $response
            ->chat_id($request['message']['chat']['id'] 
                ?? $request['callback_query']['message']['chat']['id']
                ?? $request['callback_query']['from']['id']
                ?? $request['inline_query']['from']['id']
                ?? null)
            ->text("<code>" . json_encode($request) . "</code>")
            ->disable_notification($disable_notification)
            ->allow_sending_without_reply($allow_sending_without_reply)
            ->send();
        die();
    }
}
