<?php

namespace TheCodingMachine\Docapost;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Psr\Http\Message\RequestInterface;

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
     * Client constructor.
     * @param string $userName
     * @param string $password
     */
    public function __construct(string $userName, string $password, string $restTransactionUrl)
    {
        $this->userName = $userName;
        $this->password = $password;
        $this->restTransactionUrl = $restTransactionUrl;
    }

    public static function createTestClient(string $userName, string $password) : self
    {
        return new self($userName, $password, 'https://test.contralia.fr:443/Contralia/api/v2/');
    }

    public static function createProdClient(string $userName, string $password) : self
    {
        return new self($userName, $password, 'https://www.contralia.fr:443/Contralia/api/v2/');
    }

    public function sync()
    {
        $client = HttpClientDiscovery::find();

        $request = $this->getBaseRequest();

        $uriFactory = UriFactoryDiscovery::find();

        $request = $request->withUri($uriFactory->createUri($this->restTransactionUrl.'/'));

        $response = $client->sendRequest($request);

    }

    private function getBaseRequest() : RequestInterface
    {
        $messageFactory = MessageFactoryDiscovery::find();
        $request = $messageFactory->createRequest('GET');

        $request = $request->withHeader('Authorization', 'Basic '.base64_encode($this->userName.':'.$this->password));
        return $request;
    }
}