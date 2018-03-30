<?php
/**
 * Created by PhpStorm.
 * User: nan
 * Date: 29/03/2018
 * Time: 17:46
 */

namespace TheCodingMachine\Docapost;


use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    /**
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function testFinalDocs()
    {
        // Create Docapost client
        $client = Client::createTestClient(\getenv('DOCAPOST_USER'), \getenv('DOCAPOST_PASSWORD'));

        $client->getFinalDoc('testContract1_5abd20f349504', '2c949e2f627196ac016272c8b723056c', __DIR__.'/');
    }
}
