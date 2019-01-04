<?php

namespace TheCodingMachine\Docapost;

use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;

class Client
{
    /**
     * @var string
     */
    private $userName;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $restTransactionUrl;
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;
    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * Client constructor.
     * @param string $userName
     * @param string $password
     * @param string $restTransactionUrl
     */
    public function __construct(string $userName, string $password, string $restTransactionUrl, ClientInterface $client, RequestFactoryInterface $requestFactory, UriFactoryInterface $uriFactory, StreamFactoryInterface $streamFactory)
    {
        $this->userName = $userName;
        $this->password = $password;
        $this->restTransactionUrl = $restTransactionUrl;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param string $userName
     * @param string $password
     * @return self
     */
    public static function createTestClient(string $userName, string $password, ClientInterface $client, RequestFactoryInterface $requestFactory, UriFactoryInterface $uriFactory, StreamFactoryInterface $streamFactory) : self
    {
        return new self($userName, $password, 'https://test.contralia.fr:443/Contralia/api/v2/', $client, $requestFactory, $uriFactory, $streamFactory);
    }

    /**
     * @param string $userName
     * @param string $password
     * @return self
     */
    public static function createProdClient(string $userName, string $password, ClientInterface $client, RequestFactoryInterface $requestFactory, UriFactoryInterface $uriFactory, StreamFactoryInterface $streamFactory) : self
    {
        return new self($userName, $password, 'https://www.contralia.fr:443/Contralia/api/v2/', $client, $requestFactory, $uriFactory, $streamFactory);
    }

    /**
     * @param string $uri
     * @return RequestInterface
     */
    private function getBaseRequest(string $uri = '') : RequestInterface
    {
        if (!empty($uri)) {
            $uri = $this->restTransactionUrl.$uri;
        } else {
            $uri = $this->restTransactionUrl;
        }
        $request = $this->requestFactory->createRequest('GET', $uri);

        $request = $request->withHeader('Authorization', 'Basic '.base64_encode($this->userName.':'.$this->password));
        return $request;
    }

    /**
     * @param string $uri
     * @param mixed[] $data
     * @return RequestInterface
     */
    private function getPostRequest(string $uri, array $data = []) : RequestInterface
    {
        $postStream = $this->streamFactory->createStream(http_build_query($data));
        $request = $this->getBaseRequest();
        $request = $request->withMethod('POST')
                            ->withUri($this->uriFactory->createUri($this->restTransactionUrl . $uri))
                            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
                            ->withBody($postStream);
        return $request;
    }

    /**
     * @param string $uri
     * @param mixed[] $multipartData
     * @return RequestInterface
     */
    private function getMultipartRequest(string $uri, array $multipartData) : RequestInterface
    {
        // Note: hard-coding boundary for unit test reproducibility.
        $body = new MultipartStream($multipartData, '48baba26213e9980d5cb854fec388a77121b2640');
        $request = $this->getBaseRequest();
        $request = $request->withMethod('POST')
                            ->withUri($this->uriFactory->createUri($this->restTransactionUrl . $uri))
                            ->withBody($body);
        return $request;
    }

    /**
     * @param Transaction $transaction
     * @return mixed
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function sign(Transaction $transaction)
    {
        // Initiate transaction
        $transactionId = $this->initiate($transaction);

        $transaction->setTransactionId($transactionId);

        // Upload attachments if exist
        if (!empty($transaction->getAttachments())) {
            $this->uploadAttachments($transaction);
        }
        // Upload documents to sign
        $this->uploadDocuments($transaction);

        // Sign with single signatory
        $signatureId = $this->signatory($transaction);

        // Send code via SMS or Email
        $this->sendCode($signatureId, $transaction->getCustomMessage());

        return $signatureId;
    }

    /**
     * @param Transaction $transaction
     * @return mixed
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function initiate(Transaction $transaction)
    {
        $initUri = $transaction->getOfferCode().'/transactions';

        $postData = [
            'organizationalUnitCode' => $transaction->getOrganizationalUnitCode(),
            'customRef' => $transaction->getCustomRef(),
            'signatoriesCount' => $transaction->getSignatoriesCount(),
        ];
        $request = $this->getPostRequest($initUri, $postData);
        $response = $this->client->sendRequest($request);

        $transactionIdXml = $response->getBody()->getContents();
        /* Retrieve string XML tag's attribute */
        $transactionIdXml = simplexml_load_string($transactionIdXml)->attributes()->id;
        $transactionID = json_decode(json_encode($transactionIdXml), true)[0];

        return $transactionID;
    }

    /**
     * @param Transaction $transaction
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    private function uploadAttachments(Transaction $transaction): void
    {
        foreach ($transaction->getAttachments() as $attachment) {
            $multipartData = [
                [
                    'name' => 'file',
                    'contents' => $attachment->getFileStream(),
                    'filename' => $attachment->getDocName()
                ],
            ];

            $attachUri = 'transactions/'.$transaction->getTransactionId().'/attachment/'.$attachment->getDocName();
            $request = $this->getMultipartRequest($attachUri, $multipartData);
            $response = $this->client->sendRequest($request);
            if ($response->getStatusCode() >= 400) {
                throw new ClientException('Error while storing transations. Got status code '.$response->getStatusCode().'. Response: '.$response->getBody());
            }
        }
    }

    /**
     * @param Transaction $transaction
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function uploadDocuments(Transaction $transaction): void
    {
        /** @var Document $document */
        foreach ($transaction->getDocuments() as $document) {
            $multipartData = [
                [
                    'name' => 'file',
                    'contents' => $document->getFileStream(),
                    'filename' => $document->getDocName()
                ],
                [
                    'name' => 'name',
                    'contents' => $document->getDocName(),
                ],
                [
                    'name' => 'fields',
                    'contents' => $document->getSignatureFields(),
                ],
            ];

            $uploadUri = 'transactions/'.$transaction->getTransactionId().'/document';
            $request = $this->getMultipartRequest($uploadUri, $multipartData);
            $this->client->sendRequest($request);
        }
    }

    /**
     * @param Transaction $transaction
     * @return mixed
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function signatory(Transaction $transaction)
    {
        $signatory = $transaction->getSignatory();
        $signatoryUri = 'transactions/'.$transaction->getTransactionId().'/signatory/';
        $postData = [
            'firstname' => $signatory->getFirstName(),
            'lastname' => $signatory->getLastName(),
            'phone' => $signatory->getPhoneNumber(),
            'email' => $signatory->getEmail(),
            'fieldNumber' => 1,
        ];
        $request = $this->getPostRequest($signatoryUri, $postData);
        $response = $this->client->sendRequest($request);

        $signatureIdXml = $response->getBody()->getContents();
        /* Retrieve string XML tag's attribute */
        $signatureIdXml = simplexml_load_string($signatureIdXml)->attributes()->id;
        $signatureId = json_decode(json_encode($signatureIdXml), true)[0];

        return $signatureId;
    }

    /**
     * Send code via SMS or Email
     * @param string $signatureId
     * @param string $customMessage
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function sendCode(string $signatureId, string $customMessage): void
    {
        $genOtpUri = 'signatures/'.$signatureId.'/genOtp';
        /*
         * If a mobile phone number has been set, then the code is sent via SMS.
         * If the phone number in question is a landline number, then it is sent via voice message.
         * If no telephone number has been set, then the code is sent via e-mail.
         * If no e-mail address has been set, an error is generated.
         * */
        $postData = [
            'deliveryMode' => 'AUTO',
            'customMessage' => $customMessage,
        ];
        $request = $this->getPostRequest($genOtpUri, $postData);
        $this->client->sendRequest($request);
    }

    /**
     * @param string $signatureId
     * @param string $receivedCode
     * @return bool
     * @throws ClientException
     * @throws SignatureAlreadyPerformedException
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function confirm(string $signatureId, string $receivedCode): bool
    {
        $otpUri = 'signatures/'.$signatureId.'/otp';
        $postData = [
            'otp' => $receivedCode,
        ];
        $request = $this->getPostRequest($otpUri, $postData);
        $response = $this->client->sendRequest($request);

        $otpXml = $response->getBody()->getContents();

        if (strpos($otpXml, 'error')) {
            if (strpos($otpXml, 'INCORRECT_OTP_CODE')) {
                return false;
            } elseif (strpos($otpXml, 'SIGNATURE_ALREADY_DONE')) {
                throw new SignatureAlreadyPerformedException('SIGNATURE_ALREADY_DONE');
            } else {
                throw new ClientException($otpXml);
            }
        } else {
            return true;
        }
    }

    /**
     * @param string $transactionId
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function terminate(string $transactionId): void
    {
        $terminateUri = 'transactions/'.$transactionId.'/terminate';
        $request = $this->getPostRequest($terminateUri);
        $this->client->sendRequest($request);
    }

    /**
     * @param string $docName
     * @param string $transactionId
     * @param string $filePathToSave
     * @throws ClientException
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function downloadFinalDoc(string $docName, string $transactionId, string $filePathToSave = ''): void
    {
        if (empty($filePathToSave)) {
            $filePathToSave = sys_get_temp_dir() . "/finalDocs/";
            if (!is_dir($filePathToSave)) {
                mkdir($filePathToSave);
            }
        }
        $filePath = $filePathToSave.$docName.'_'.$transactionId.".pdf";

        $result = $this->getFinalDocStream($docName, $transactionId)->getContents();

        if (strpos($result, "DOCUMENT_NOT_FOUND")) {
            throw new ClientException($result);
        }
        $fp = fopen($filePath, 'w');
        fwrite($fp, $result);
        fclose($fp);
    }

    /**
     * @param string $docName
     * @param string $transactionId
     * @return StreamInterface
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    public function getFinalDocStream(string $docName, string $transactionId): StreamInterface
    {
        $finalDocUri = 'transactions/'.$transactionId.'/finalDoc?name='.$docName;
        $request = $this->getBaseRequest($finalDocUri);
        $response = $this->client->sendRequest($request);

        return $response->getBody();
    }
}
