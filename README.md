# Docapost Electronic Signature

Objects :
1. Client
2. Document
3. Signatory
4. Transaction

# Prerequisites

Add in composer.json (Temporary solution to retrieve docapost-client package)
```
  "repositories": [
    {
    "type": "vcs", 
    "url": "https://git.thecodingmachine.com/tcm-projects/docapost-client.git" 
    },
  ],
```

In your project :

```
composer require guzzlehttp/psr7
composer require ricardofiorani/guzzle-psr18-adapter
composer require http-interop/http-factory-guzzle
composer require thecodingmachine/docapost-client
``` 


```php
use TheCodingMachine\Docapost\Client;
use TheCodingMachine\Docapost\Document;
use TheCodingMachine\Docapost\Signatory;
use TheCodingMachine\Docapost\Transaction;
```

# Quick start

First, we need to create the $client object that can connect to Docapost:

```php
$client = Client::createTestClient(
    $docaPostUser,
    $docaPostPassword,
    new \RicardoFiorani\GuzzlePsr18Adapter\Client([
      'http_errors' => false
    ]),
    new \Http\Factory\Guzzle\RequestFactory(),
    new \Http\Factory\Guzzle\UriFactory(),
    new \Http\Factory\Guzzle\StreamFactory());
```

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

**Attention** : Param 'docName' for Document should be unique in one transaction, otherwise the uploaded file will be replaced by another upload file with same docName. 
```php
$doc1 = new Document('testContract1', __DIR__.'/testContract.pdf');
// Add signature boxes to document
$doc1->addSignatureBox(299, 94, 267, 54, 1);
$doc1->addSignatureBox(299, 94, 267, 54, 2);
// Create another document ...
$doc2 = new Document('testContract2', __DIR__.'/testContract.pdf');
$doc2->addSignatureBox(299, 92, 267, 55, 1);
```

Add documents to transaction :
```php
$transaction->setDocuments([$doc1, $doc2]);
```
**Optional** : Customize SMS or Email message. (*See default $customMessage in Transaction.php*)
```php
$transaction->setCustomMessage("Pour valider votre signature renseignez le code suivant :\n{OTP}.");
```

**Optional** : Set attachments to transaction. (Param 'docName' for Document should be unique in one transaction, otherwise the uploaded file will be replaced by another upload file with same docName.)
```php
$attachment1 = new Document('testAttachment1', __DIR__.'/testAttachment.png');
$attachment2 = new Document('testAttachment2', __DIR__.'/testAttachment.png');
$transaction->setAttachments([$attachment1, $attachment2]);
```
Create Docapost Client with $restTransactionUrl :
```php
$client = new Client('DOCAPOST_USER', 'DOCAPOST_PASSWORD', 'https://test.contralia.fr:443/Contralia/api/v2/');
```
Otherwise use default $restTransactionUrl TestClient or ProdClient depending on environment :
````php
$client = Client::createTestClient('DOCAPOST_USER', 'DOCAPOST_PASSWORD');
OR
$client = Client::createProdClient('DOCAPOST_USER', 'DOCAPOST_PASSWORD');
````

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

Download a signed document to local path :
```
$client->downloadFinalDoc('DOC_NAME', 'TRANSACTION_ID', 'FILE_PATH_TO_SAVE');
```
Or get stream of the signed document, and open it in browser directly :
```
$streamDoc = $client->getFinalDocStream('DOC_NAME', 'TRANSACTION_ID');
return (new Response())
        ->withHeader('Content-Type', 'application/pdf')
        ->withHeader('Content-Length', $streamDoc->getSize())
        ->withBody($streamDoc);
```

# Unit Test
```
./vendor/bin/phpunit ONE_TEST.php
```

1, Test Sign
- Activate codes in tests/ClientTest.php
- Change to a real phone number or email to receive code
- Execute follow command
```
./vendor/bin/phpunit tests/ClientTest.php 
```
Return : $transactionId, $signatureId

2, Test Confirm
- Activate codes in tests/SignatoryTest.php
- Add real data in codes with followed "Enter" params
- Execute followed command
```
./vendor/bin/phpunit tests/SignatoryTest.php 
```
Enter : $signatureId, $receivedCode, $transactionId

Return : "Transaction terminated" OR "INCORRECT SMS CODE"

3, Test FinalDoc
- Activate codes in tests/DocumentTest.php
- Add real data in codes with followed "Enter" params
- Execute follow command
```
./vendor/bin/phpunit tests/DocumentTest.php 
```
Enter : $docName, $transactionId, $filePathToSave
