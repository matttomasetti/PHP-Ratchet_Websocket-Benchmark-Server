<?php
namespace Benchmark;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * This class contains the custom logic for the
 * websocket server.
 * @package Benchmark
 */
class Server implements MessageComponentInterface {


    /**
     * @var \SplObjectStorage object that stores all connect clients
     */
    protected $clients;


    /**
     * Server constructor.
     * Initializes the clients member property
     */
    public function __construct() {

        // object store for connected clients
        $this->clients = new \SplObjectStorage;
    }


    /**
     * Event triggered whenever a client connects to the websocket
     * @param ConnectionInterface $conn The newly connected client
     * @return void
     */
    public function onOpen(ConnectionInterface $conn): void {

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";

        // send newly connected client initial timestamp
        $this->notify($conn, 0);
    }


    /**
     * Event triggered whenever the server receives an incoming message from a client
     * @param ConnectionInterface $from The client the incoming message is from
     * @param string $msg The incoming message
     * @return void
     */
    public function onMessage(ConnectionInterface $from, $msg): void {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        // decode incoming message into an associative array
        $incoming_message = json_decode($msg, true);

        // notify client with event for message with count "c"
        $this->notify($from, $incoming_message["c"]);
    }


    /**
     * Event triggered whenever a client disconnects from the websocket server
     * @param ConnectionInterface $conn The client that has disconnected
     * @return void
     */
    public function onClose(ConnectionInterface $conn): void {

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }


    /**
     * Event triggered whenever an exception is thrown in the websocket server
     * @param ConnectionInterface $conn The connected clients that triggered the exception
     * @param \Exception $e The exception being thrown
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }


    /**
     * Gets the current unix timestamp of the server
     * @return int The current unix timestamp
     */
    private function getTimestamp(): int{
        return time();
    }


    /**
     * Creates a JSON string containing the message count and the current timestamp
     * @param int $c The message count
     * @return string A JSON string containing the message count and the current timestamp
     */
    private function getEvent(int $c): string{

        //create an event array for the time that message "c" is received by the server
        $event = [
            "c" => $c,
            "ts" => $this->getTimestamp(),
        ];

        // convert the array to a string
        return json_encode($event);
    }


    /**
     * Send a connected client an event JSON string
     * @param ConnectionInterface $conn The client connection the outgoing message is for
     * @param int $c The message count
     * @return void
     */
    private function notify(ConnectionInterface $conn, int $c): void{

        //send the given connection the event timestamp for message "c"
        $conn->send($this->getEvent($c));
    }
}