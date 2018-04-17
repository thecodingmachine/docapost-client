<?php
///* Commented because the test cannot be passed on gitlab CI without real data */
//
//namespace TheCodingMachine\Docapost;
//
//use PHPUnit\Framework\TestCase;
//
//class ClientTest extends TestCase
//{
//    /**
//     * @throws \Exception
//     * @throws \Http\Client\Exception
//     */
//    public function testSign()
//    {
//        // Create transaction
//        $transaction = new Transaction('UNEO-TEST', 'UNEO-TEST-DISTRIB', 'test');
//
//        // Create single signatory
//        $signatory = new Signatory('Coucou', 'Toto', '+33123456789');
//        $transaction->setSignatory($signatory);
//
//        // Create document
//        $doc1 = new Document('testContract1', __DIR__.'/testContract.pdf');
//        // Add signature boxes to document
//        $doc1->addSignatureBox(299, 94, 267, 54, 1);
//        $doc1->addSignatureBox(299, 94, 267, 54, 2);
//        // Create another document...
//        $doc2 = new Document('testContract2', __DIR__.'/testContract.pdf');
//        $doc2->addSignatureBox(299, 92, 267, 55, 1);
//
//        // Add documents to transaction
//        $transaction->setDocuments([$doc1, $doc2]);
//
//        // Optional : set SMS or Email message
//        /* $transaction->setCustomMessage("Pour valider votre signature renseignez le code suivant :\n{OTP}."); */
//        // Optional : set attachments to transaction
//        $attachment1 = new Document('testAttachment1', __DIR__.'/testAttachment.png');
//        $attachment2 = new Document('testAttachment2', __DIR__.'/testAttachment.png');
//        $transaction->setAttachments([$attachment1, $attachment2]);
//
//        // Create Docapost client
//        $client = Client::createTestClient(\getenv('DOCAPOST_USER'), \getenv('DOCAPOST_PASSWORD'));
//        // Start signing transaction
//        $signatureId = $client->sign($transaction);
//
//        echo "\n \n".'$transactionId: '.$transaction->getTransactionId()."\n";
//        echo "\n \n".'$signatureId: '.$signatureId."\n";
//        echo "\n \n";
//
//        // Test
//        $this->assertTrue(is_string($signatureId));
//    }
//}
