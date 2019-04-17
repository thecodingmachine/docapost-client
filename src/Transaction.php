<?php

namespace TheCodingMachine\Docapost;

class Transaction
{
    /**
     * @var string
     */
    private $transactionId;
    /**
     * @var string
     */
    private $offerCode;
    /**
     * @var string
     */
    private $organizationalUnitCode;
    /**
     * @var string
     */
    private $customRef;
    /**
     * @var Document[]
     */
    private $documents = [];
    /**
     * @var Document[]
     */
    private $attachments = [];
    /**
     * @var int
     */
    private $signatoriesCount;
    /**
     * @var string
     */
    private $customMessage = "Pour valider votre signature renseignez le code suivant :\n{OTP}.";

    /**
     * Transaction constructor.
     * @param string $offerCode
     * @param string $organizationalUnitCode
     * @param string $customRef
     * @param int $signatoriesCount
     * @throws ClientException
     */
    public function __construct(string $offerCode, string $organizationalUnitCode, ?string $customRef = null, int $signatoriesCount = 1)
    {
        $this->offerCode = $offerCode;
        $this->organizationalUnitCode = $organizationalUnitCode;
        if ($customRef !== null && \strlen($customRef) > 32) {
            throw new ClientException('Transaction customRef should have maximum 32 characters');
        }
        $this->customRef = $customRef ?: uniqid('trans', true);
        $this->signatoriesCount = $signatoriesCount;
    }

    /**
     * @param Document $document
     */
    public function addDocument(Document $document): void
    {
        array_push($this->documents, $document);
    }

    /**
     * @param Document[] $documents
     */
    public function setDocuments(array $documents): void
    {
        $this->documents = $documents;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId(string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @param mixed[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getOfferCode(): string
    {
        return $this->offerCode;
    }

    /**
     * @return string
     */
    public function getOrganizationalUnitCode(): string
    {
        return $this->organizationalUnitCode;
    }

    /**
     * @return string
     */
    public function getCustomRef(): string
    {
        return $this->customRef;
    }

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @return Document[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }


    /**
     * @return int
     */
    public function getSignatoriesCount(): int
    {
        return $this->signatoriesCount;
    }

    /**
     * @param int $signatoriesCount
     */
    public function setSignatoriesCount(int $signatoriesCount): void
    {
        $this->signatoriesCount = $signatoriesCount;
    }

}
