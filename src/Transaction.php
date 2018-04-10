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
     * @var Signatory[]
     */
    private $signatorys = [];
    /**
     * @var Signatory
     */
    private $signatory;
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
    public function __construct(string $offerCode, string $organizationalUnitCode, string $customRef, int $signatoriesCount = 1)
    {
        $this->offerCode = $offerCode;
        $this->organizationalUnitCode = $organizationalUnitCode;
        if (strlen($customRef) > 32) {
            throw new ClientException('Transaction customRef should have maximum 32 characters');
        }
        $this->customRef = $customRef.'_'.uniqid();
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
     * @param Signatory $signatory
     */
    public function addSignatory(Signatory $signatory): void
    {
        // TODO Multiple signatories
//        array_push($this->signatorys, $signatory);
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
     * @return Signatory[]
     */
    public function getSignatorys(): array
    {
        return $this->signatorys;
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

    /**
     * @return Signatory
     */
    public function getSignatory(): Signatory
    {
        return $this->signatory;
    }

    /**
     * @param Signatory $signatory
     */
    public function setSignatory(Signatory $signatory): void
    {
        $this->signatory = $signatory;
    }

    /**
     * @return string
     */
    public function getCustomMessage(): string
    {
        return $this->customMessage;
    }

    /**
     * @param string $customMessage
     */
    public function setCustomMessage(string $customMessage): void
    {
        if (!empty($customMessage)) {
            $this->customMessage = $customMessage;
        }
    }
}
