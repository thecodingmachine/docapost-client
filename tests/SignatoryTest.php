<?php

namespace TheCodingMachine\Docapost;

use PHPUnit\Framework\TestCase;

class SignatoryTest extends TestCase
{
    /**
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function testConfirm()
    {
        // Create Docapost client
        $client = Client::createTestClient(\getenv('DOCAPOST_USER'), \getenv('DOCAPOST_PASSWORD'));

        // Optional : resend SMS code
        /*$client->sendCode('61696857', "Pour valider votre signature renseignez le code suivant :\n{OTP}.");*/

        // Confirm received code
        $result = $client->confirm('61679889', '229397');

        if ($result) {
            $client->terminate('2c949e2f627196ac016272c8b723056c');
            echo "\n \n".'Transaction terminated'."\n \n";
        } else {
            echo "\n \n".'INCORRECT SMS CODE'."\n \n";
        }

        // Test
        $this->assertTrue($result);
    }
}
