<?php

namespace LEDController\Interface;

use LEDController\Packet;
use LEDController\Response;

/**
 * Communication interface for different communication methods
 */
interface CommunicationInterface
{
    public function connect(): bool;
    public function disconnect(): void;
    public function send(Packet $packet): Response;
    public function isConnected(): bool;
}
