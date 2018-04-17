<?php

namespace TheCodingMachine\Docapost;

/**
 * Class Signatory
 * @package TheCodingMachine\Docapost
 */
class Signatory
{
    /**
     * @var string
     */
    private $firstName;
    /**
     * @var string
     */
    private $lastName;
    /**
     * @var null|string
     */
    private $phoneNumber;
    /**
     * @var null|string
     */
    private $email;

    /**
     * Signatory constructor.
     * @param string $firstName
     * @param string $lastName
     * @param string|null $phoneNumber
     * @param string|null $email
     * @throws ClientException
     */
    public function __construct(string $firstName, string $lastName, string $phoneNumber = null, string $email = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phoneNumber = $phoneNumber;
        $this->email = $email;

        if (empty($this->phoneNumber) && empty($this->email)) {
            throw new ClientException('Phone number or email must be set');
        }
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return null|string
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param null|string $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}
