<?php
/**
 * Created by PhpStorm.
 * User: ragedwiz
 * Date: 13/11/16
 * Time: 4:07 PM
 */

namespace Sindria\Application\WebSockets;
use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;

class Chat implements Websocket {
    /**
     * @var Websocket\Endpoint
     */
    private $endpoint;
    private $ids;
    private $usernames = [];

    public function onStart(Websocket\Endpoint $endpoint) {
        $this->endpoint = $endpoint;
    }

    public function onHandshake(Request $request, Response $response) {
        // Interface method stub.
    }

    public function onOpen(int $clientId, $handshakeData) {
        $this->ids[$clientId] = [];

        $this->endpoint->send($clientId, json_encode([
            'type' => 'ASSIGN',
            'id' => $clientId,
            'count' => count($this->ids) - 1
        ]));
    }

    public function onData(int $clientId, Websocket\Message $msg) {
        $msg = yield $msg;
        $message = json_decode($msg, true);

        switch ($message['type']) {
            case 'REGISTER':
                if(!in_array($message['username'], $this->usernames)) {
                    $this->ids[$clientId]['username'] = $message['username'];
                    $this->usernames[] = $message['username'];
                    $this->endpoint->send(array_keys($this->ids), json_encode([
                        'type' => 'NOTIFICATION',
                        'notification-type' => 'USER_JOIN',
                        'username' => $this->ids[$clientId]['username'],
                        'time' => date('h:i A')
                    ]));
                } else {
                    $this->ids[$clientId]['username'] = $message['username'];
                }
                break;
            case 'MESSAGE':
                $messageContent = $message['content'];

                $this->endpoint->send(array_keys($this->ids), json_encode([
                    'type' => 'MESSAGE',
                    'content' => $messageContent,
                    'username' => $this->ids[$clientId]['username'],
                    'time' => date('h:i A')
                ]));
        }
    }

    public function onClose(int $clientId, int $code, string $reason) {
        $username = $this->ids[$clientId]['username'];

        unset($this->ids[$clientId]);

        $this->endpoint->send(array_keys($this->ids), json_encode([
            'type' => "NOTIFICATION",
            'notification-type' => 'USER_LEAVE',
            'username' => $username,
            'time' => date('h:i A')
        ]));
    }

    public function onStop() {
        // Interface method stub.
    }
}