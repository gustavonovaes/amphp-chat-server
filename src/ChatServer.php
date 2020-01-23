<?php

namespace App;

use Amp\Socket\Server;
use Amp\Socket\Socket;

use function Amp\asyncCall;

final class ChatServer
{
  private string $uri;

  /** @var Socket[] */
  private array $clients = [];

  public function __construct(string $uri)
  {
    $this->uri = $uri;
  }

  /**
   * Listen for new clients.
   *
   * @return void
   */
  public function listen()
  {
    asyncCall(function () {
      $server = Server::listen($this->uri);

      while (null !== $socket = yield $server->accept()) {
        $this->handleNewClient($socket);
      }
    });
  }

  /**
   * Perform the intial actions for the new client.
   * @param \Amp\Socket\Socket $socket
   *
   * @return void
   */
  public function handleNewClient(Socket $socket)
  {
    asyncCall(function () use ($socket) {
      $remoteAddress = $socket->getRemoteAddress()->toString();

      $clientId = $this->generateClientId($socket);

      $this->addClient($clientId, $socket);

      $this->broadcast("@ {$clientId} enter to the server");

      log("> Accepted new client: {$remoteAddress}");

      $buffer = '';

      // Reads until recieve a "\n"
      while (null !== $chunk = yield $socket->read()) {
        $buffer .= $chunk;

        // Deal with multiple "\n" in the buffer
        while (($pos = \strpos($buffer, "\n")) !== false) {
          $message = \substr($buffer, 0, $pos);
          $buffer = \substr($buffer, $pos + 1);

          $this->processIncomingMessage($clientId, $message);
        }
      }

      $this->removeClient($clientId);

      $this->broadcast("@ {$clientId} was disconnected from the server");

      log("> Client disconnected: {$remoteAddress}");
    });
  }

  /**
   * Processes the incoming message from the Client.
   * @param string $clientId
   * @param string $message
   *
   * @return void
   */
  private function processIncomingMessage(string $clientId, string $message): void
  {
    $this->broadcast("{$clientId} says: {$message}", [$clientId]);
  }

  /**
   * Generates a clientId from the socket.
   * @param [type] $socket
   *
   * @return string
   */
  private function generateClientId($socket): string
  {
    return $socket->getRemoteAddress()->toString();
  }

  /**
   * Add a client to the client list.
   * @param string $clientId
   * @param \Amp\Socket\Socket $socket
   * 
   * @throws \InvalidArgumentException When clientId already exists in list
   *
   * @return void
   */
  private function addClient(string $clientId, Socket $socket): void
  {
    if (isset($this->clients[$clientId])) {
      throw new \InvalidArgumentException("Client {$clientId} already exists!");
    }

    $this->clients[$clientId] = $socket;
  }

  /**
   * Removes a client from list.
   * @param string $clientId
   *
   * @return void
   */
  private function removeClient(string $clientId): void
  {
    unset($this->clients[$clientId]);
  }

  /**
   * Send a message to all registered clients
   * @param string $message
   * @param string[] $skipClientsList 
   *
   * @return void
   */
  private function broadcast(string $message, array $skipClientsList = []): void
  {
    $clientsId = array_keys($this->clients);
    $clientsIdForBroadcast = \array_diff($clientsId, $skipClientsList);

    foreach ($clientsIdForBroadcast as $clientId) {
      $this->clients[$clientId]->write($message . \PHP_EOL);
    }
  }
}
