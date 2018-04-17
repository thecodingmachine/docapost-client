<?php

namespace TheCodingMachine\Docapost;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

class Document
{
    /**
     * @var string
     */
    private $filePath;
    /**
     * @var string
     */
    private $docName;
    /**
     * @var array
     */
    private $signatureBoxes = [];
    /**
     * @var StreamInterface|null
     */
    private $fileStream;
    /**
     * @var string
     */
    private $signatureFields;

    /**
     * Document constructor.
     * @param string $docName
     * @param string $filePath
     * @param StreamInterface|null $fileStream
     */
    public function __construct(string $docName, string $filePath = '', StreamInterface $fileStream = null)
    {
        $this->filePath = $filePath;
        $this->docName = $docName;
        $this->fileStream = $fileStream ? $fileStream : new LazyOpenStream($filePath, 'r');
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @param int $page
     */
    public function addSignatureBox(int $x, int $y, int $width, int $height, int $page): void
    {
        array_push($this->signatureBoxes, "<box x=\"$x\" y=\"$y\" width=\"$width\" height=\"$height\" page=\"$page\" />");

        $fieldsXml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?><fields xmlns=\"http://www.contralia.fr/champsPdf\">";

        foreach ($this->signatureBoxes as $signatureBox) {
            $fieldsXml .= "<signatorySignature number=\"1\">";
            $fieldsXml .= $signatureBox;
            $fieldsXml .= "</signatorySignature>";
        }

        $fieldsXml .= "</fields>";
        $this->setSignatureFields($fieldsXml);
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getDocName(): string
    {
        return $this->docName;
    }

    /**
     * @return mixed[]
     */
    public function getSignatureBoxes(): array
    {
        return $this->signatureBoxes;
    }

    /**
     * @return StreamInterface|null
     */
    public function getFileStream(): ?StreamInterface
    {
        return $this->fileStream;
    }

    /**
     * @param Stream|null $fileStream
     */
    public function setFileStream(?Stream $fileStream): void
    {
        $this->fileStream = $fileStream;
    }

    /**
     * @param string $signatureFields
     */
    public function setSignatureFields(string $signatureFields): void
    {
        $this->signatureFields = $signatureFields;
    }

    /**
     * @return string
     */
    public function getSignatureFields(): string
    {
        return $this->signatureFields;
    }
}
