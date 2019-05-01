<?php
namespace MyApp;
require __DIR__ . '/vendor/autoload.php';
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $pars = [];
    protected $moves = [];
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        foreach ($this->clients as $client) {
            if ($conn->resourceId !== $client->resourceId) {
                // The sender is not the receiver, send to each client connected
                $client->send($conn->resourceId);
            }
        }
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->pars as $value) {
            if ($value == $msg) {
                $from->send("He has already playing");
                unset($msg);
            }
        }

        foreach ($this->clients as $client) {
            if ($msg == $client->resourceId) {
                $this->pars[] = $msg;
                $this->pars[] = $from->resourceId;
                $client->send("You've just connected to - ".$from->resourceId.":"."player1:1");
                $from->send("You've just connected to - ".$msg.":"."player:0");

                $this->moves = [
                $from->resourceId => 1,
                $msg => 0
                ];
            }
        }
        // if ($msg == "checkMove") {
        //     if ($this->moves[$from->resourceId]) {
        //         $from->send("true");
        //     }
        //     else{
        //         $from->send("false");
        //     }
        // }
            $arr = explode(":", $msg);
            if (0 <= $arr[0] && $arr[0] <= 8) {
                foreach ($this->clients as $client) {
                    if ($arr[1] == $client->resourceId) {
                       $client->send($arr[0]);
                       // $this->moves[$from->resourceId] = 0;
                       // $this->moves[$client->resourceId] = 1;
                    }
                }
            }
            if ($arr[0] == "gameOver") {
                if (($key = array_search($arr[1], $this->pars)) !== false) {
                    unset($this->pars[$key]);
                }
                if (($key = array_search($from->resourceId, $this->pars)) !== false) {
                    unset($this->pars[$key]);
                }
            }

    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        if (($key = array_search($conn->resourceId, $this->pars)) !== false) {
            unset($this->pars[$key]);
        }
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}