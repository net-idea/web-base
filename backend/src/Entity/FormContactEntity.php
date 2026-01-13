<?php

declare(strict_types=1);

namespace NetIdea\WebBase\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Mime\Address;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base entity for contact form submissions
 */
#[ORM\MappedSuperclass]
class FormContactEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'string', length: 160)]
    #[Assert\NotBlank(message: 'Please enter your name.')]
    #[Assert\Length(max: 120, maxMessage: 'Please use at most {{ limit }} characters.')]
    protected string $name = '';

    #[ORM\Column(type: 'string', length: 200)]
    #[Assert\NotBlank(message: 'Please enter your email address.')]
    #[Assert\Email(message: 'Please enter a valid email address.')]
    #[Assert\Length(max: 200, maxMessage: 'Please use at most {{ limit }} characters.')]
    protected string $emailAddress = '';

    // Not persisted; convenience for emails
    protected ?Address $email = null;

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    #[Assert\Length(max: 40, maxMessage: 'Please use at most {{ limit }} characters.')]
    protected string $phone = '';

    #[ORM\Column(type: 'boolean')]
    #[Assert\IsTrue(message: 'Please agree to the data processing.')]
    protected bool $consent = false;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Please enter a message.')]
    #[
        Assert\Length(
            min: 10,
            max: 5000,
            minMessage: 'Please enter at least {{ limit }} characters.',
            maxMessage: 'Please use at most {{ limit }} characters.',
        ),
    ]
    protected string $message = '';

    #[ORM\Column(type: 'boolean')]
    protected bool $copy = true;

    // Honeypot field; not persisted
    protected string $emailrep = '';

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setName($name): self
    {
        $this->name = (string) $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmail(Address $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): Address
    {
        if (!$this->email) {
            $this->email = new Address($this->emailAddress, $this->name);
        }

        return $this->email;
    }

    public function setPhone($phone): self
    {
        $this->phone = (string) $phone;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setConsent(bool $consent): self
    {
        $this->consent = $consent;

        return $this;
    }

    public function getConsent(): bool
    {
        return $this->consent;
    }

    public function setMessage($message): self
    {
        $this->message = (string) $message;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setCopy(bool $copy): self
    {
        $this->copy = $copy;

        return $this;
    }

    public function getCopy(): bool
    {
        return $this->copy;
    }

    public function setEmailrep($emailrep): self
    {
        $this->emailrep = (string) $emailrep;

        return $this;
    }

    public function getEmailrep(): string
    {
        return $this->emailrep;
    }

    /**
     * Returns the message formatted as safe HTML with line breaks.
     */
    public function getMessageHtml(): string
    {
        return nl2br(htmlentities($this->message, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'));
    }
}
