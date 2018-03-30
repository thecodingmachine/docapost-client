# Docapost Electronic Signature
Objects :
1. Client
2. Document
3. Signatory
4. Transaction

# Prerequisites
```php
use TheCodingMachine\Docapost\Client;
use TheCodingMachine\Docapost\Document;
use TheCodingMachine\Docapost\Signatory;
use TheCodingMachine\Docapost\Transaction;
```

# Quick start
Create a transaction :
```php
$transaction = new Transaction('UNEO-TEST', 'UNEO-TEST-DISTRIB', 'test');
```

Create single signatory :
```php
$signatory = new Signatory('Coucou', 'Toto', '+33123456789');
$transaction->setSignatory($signatory);
```
Prepare documents with signature boxes :
```php
$doc1 = new Document(__DIR__.'/testContract.pdf', 'testContract1');
// Add signature boxes to document
$doc1->addSignatureBox(299, 94, 267, 54, 1);
$doc1->addSignatureBox(299, 94, 267, 54, 2);
// Create another document ...
$doc2 = new Document(__DIR__.'/testContract.pdf', 'testContract2');
$doc2->addSignatureBox(299, 92, 267, 55, 1);
```

Add documents to transaction :
```php
$transaction->setDocuments([$doc1, $doc2]);
```
**Optional** : Customize SMS or Email message. (*See default $customMessage in Transaction.php*)
```php
/* $transaction->setCustomMessage("Pour valider votre signature renseignez le code suivant :\n{OTP}."); */
```
**Optional** : Set attachments to transaction
```
$attachment1 = new Document(__DIR__.'/testAttachment.png', 'testAttachment1');
$attachment2 = new Document(__DIR__.'/testAttachment.png', 'testAttachment2');
$transaction->setAttachments([$attachment1, $attachment2]);
```
Create Docapost TestClient :
```php
$client = Client::createTestClient('DOCAPOST_USER', 'DOCAPOST_PASSWORD');
```
**Optional** : Create Docapost ProdClient instead of TestClient :
```php
$client = Client::createProdClient('DOCAPOST_USER', 'DOCAPOST_PASSWORD');
```
Start signing transaction :
```php
$signatureId = $client->sign($transaction);
```

**Optional** :
```php
// Resend SMS to the signatory if first SMS was non received
$client->sendCode($signatureId);
```

Confirm transaction with received code :
```php
$result = $client->confirm('SIGNATURE_ID', 'RECEIVED_CODE');

if ($result) {
    $client->terminate('TRANSACTION_ID');
} else {
    // Do with INCORRECT SMS CODE 
}
```

Get a signed document
```
$client->getFinalDoc('DOC_NAME', 'TRANSACTION_ID', 'FILE_PATH_TO_SAVE');
```

# Unit Test
```
./vendor/bin/phpunit ONE_TEST.php
```

1, Test Sign
```
./vendor/bin/phpunit tests/ClientTest.php 
```
Return : $transactionId, $signatureId

2, Test Confirm
```
./vendor/bin/phpunit tests/SignatoryTest.php 
```
Enter : $signatureId, $receivedCode, $transactionId

Return : "Transaction terminated" OR "INCORRECT SMS CODE"

3, Test FinalDoc
```
./vendor/bin/phpunit tests/DocumentTest.php 
```
Enter : $docName, $transactionId, $filePathToSave
