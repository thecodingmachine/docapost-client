<?php

namespace TheCodingMachine\Docapost;



use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    public function testSign()
    {
        $client = Client::createTestClient(\getenv('DOCAPOST_USER'), \getenv('DOCAPOST_PASSWORD'));
        $client->sign();
    }
}
