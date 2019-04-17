<?php
/* Commented because the test cannot be passed on gitlab CI without real data */

namespace TheCodingMachine\Docapost;

use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UriFactory;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        \VCR\VCR::turnOn();
        \VCR\VCR::insertCassette('test-cassette.yml');
    }

    public function tearDown()
    {
        \VCR\VCR::turnOff();
    }

    public function testSign()
    {

        // Create transaction
        $transaction = new Transaction('UNEO-TEST', 'UNEO-TEST-DISTRIB', 'test');

        // Create document
        $doc1 = new Document('testContract1', __DIR__.'/testContract.pdf');
        // Add signature boxes to document
        $doc1->addSignatureBox(299, 94, 267, 54, 1);
        $doc1->addSignatureBox(299, 94, 267, 54, 2);
        // Create another document...
        $doc2 = new Document('testContract2', __DIR__.'/testContract.pdf');
        $doc2->addSignatureBox(299, 92, 267, 55, 1);

        // Add documents to transaction
        $transaction->setDocuments([$doc1, $doc2]);

        // Optional : set SMS or Email message
        /* $transaction->setCustomMessage("Pour valider votre signature renseignez le code suivant :\n{OTP}."); */
        // Optional : set attachments to transaction
        $attachment1 = new Document('testAttachment1', __DIR__.'/testAttachment.png');
        $attachment2 = new Document('testAttachment2', __DIR__.'/testAttachment.png');
        $transaction->setAttachments([$attachment1, $attachment2]);


        // Create Docapost client
        $client = Client::createTestClient(\getenv('DOCAPOST_USER'), \getenv('DOCAPOST_PASSWORD'),
            new \RicardoFiorani\GuzzlePsr18Adapter\Client([
                'http_errors' => false
            ]),
            new RequestFactory(),
            new UriFactory(),
            new StreamFactory());
        // Initiate transaction
        $transactionId = $client->initiate($transaction);
        $this->assertInternalType('string', $transactionId);

        // Add a single signatory
        $signatory = new Signatory('Foo', 'Bar', '+33619995558');
        $signatureId = $client->signatory($transactionId, $signatory);
        $this->assertInternalType('string', $signatureId);

        // Send Code
        $client->sendCode($signatureId);

        $result = $client->confirm($signatureId, '999999');
        $this->assertFalse($result);

        $result = $client->confirm($signatureId, '307088');
        $this->assertTrue($result);

        $stream = $client->getFinalDocStream('testContract1', $signatureId);
        $this->assertNotEmpty((string) $stream);

        // TODO: this test should pass when downloadFinalDoc is corrected.
        //$client->downloadFinalDoc('testContract1', $signatureId, __DIR__.'/fixtures/downloaded.pdf');
        //$this->assertFileExists(__DIR__.'/fixtures/downloaded.pdf');
        //\unlink(__DIR__.'/fixtures/downloaded.pdf');


        //echo "\n \n".'$transactionId: '.$transaction->getTransactionId()."\n";
        //echo "\n \n".'$signatureId: '.$signatureId."\n";
        //echo "\n \n";

    }
}
