#Main

Transaction :
1. Documents
2. Signatory
3. Attachments

```php
use TheCodingMachine\DocaPost\Client;
use TheCodingMachine\DocaPost\Transaction;
use TheCodingMachine\DocaPost\Document;
use TheCodingMachine\DocaPost\Signatory;

// Init transaction
$transaction = new Transaction();

// Set Signatory in filled document
$document = new Document($filePath, $docName);
$document->addSignatorySignature(x, y, width, height, page);

// Upload file
$transaction->addDocument($document);

// Add attachment
$transaction->addAttachment($attachmentId, $filePath);

// Add Signatory
$signatory = new Signatory($firstName, $lastName, $phoneNumber, $email);
$transaction->addSignatory($signatory);

// Sign Transaction
$client = new Client($docaPostUserName, $docaPostPassword);
$signatureIds = $client->sign($transaction);
// The sign method returns an array of signature identifiers. There is one identifier per signature added.
$firstSignatureId = $signatureIds[0];
```

Optional :

```php
// Resend SMS if first SMS was non received
$client->resend($signatureId);
```

```php
// Confirm Transaction
$response = $client->confirm($signatureId, $codeSms);
If ($response->success()) {
    $terminatedTransaction = $response->getTerminatedTransaction();
    $terminatedTransaction->getDocument($docName)->save($path);
} else {
    // return INCORRECT SMS CODE
}
```


