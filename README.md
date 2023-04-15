> # :warning: WARNING: This repository is not actively maintained !
> 
> If you are interested in contributing, please reach out to us !
>
> If you consider using this package in production, you may have some upgrades to perform.


# Current Implementations

Implemented objects :
1. Client
2. Document
3. Signatory
4. Transaction

# Install

```
  composer require thecodingmachine/docapost-client
```

You will also need a PSR 7 implementation, we recommend :
```
composer require guzzlehttp/psr7
composer require ricardofiorani/guzzle-psr18-adapter
composer require http-interop/http-factory-guzzle
``` 

# Quick start

```php
use TheCodingMachine\Docapost\Client;
use TheCodingMachine\Docapost\Document;
use TheCodingMachine\Docapost\Signatory;
use TheCodingMachine\Docapost\Transaction;
```

First, we need to create the $client object that can connect to Docapost:

```php
$client = Client::createClient(
    'YOUR_DOCAPOST_USERNAME',
    'YOUR_DOCAPOST_PASSWORD',
    new \RicardoFiorani\GuzzlePsr18Adapter\Client([
      'http_errors' => false
    ]),
    new \Http\Factory\Guzzle\RequestFactory(),
    new \Http\Factory\Guzzle\UriFactory(),
    new \Http\Factory\Guzzle\StreamFactory());
```

Create a transaction :
```php
$transaction = new Transaction(
    'YOUR_DOCAPOST_OFFER_CODE',
    'YOUR_DOCAPOST_ORGANIZATION_UNIT_CODE',
    null,  // Optional, will be generated
    1,     // Optional, nb of signartories
);
```

Prepare documents with signature boxes :

**Warning** : Param 'docName' for Document should be unique in one transaction, otherwise the uploaded file will be replaced by another upload file with same docName. 
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

Initiate the transaction :
```php
$transactionId = $client->initiate($transaction);
```

Add a signatory :
```php
$signatory = new Signatory('Coucou', 'Toto', '+33123456789');
$signatureId = $client->signatory('TRANSACTION_ID', $signatory, 'SIGNATORY_POSITION');
```

Send the code :
```php
$client->sendCode('SIGNATURE_ID')
```

**Optional** :
```php
// Resend SMS to the signatory if first SMS was non received
$client->sendCode('SIGNATURE_ID')
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
