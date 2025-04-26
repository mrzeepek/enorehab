<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EbookSubscriberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EbookSubscriberRepository::class)]
#[ORM\Table(name: 'enorehab_ebook_subscribers')]
class EbookSubscriber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le nom est requis')]
    #[Assert\Length(min: 2, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères')]
    private string $name;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est requis')]
    #[Assert\Email(message: 'L\'email n\'est pas valide')]
    private string $email;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $download_date;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ip_address = null;

    #[ORM\Column(type: 'boolean')]
    private bool $consent = true;

    #[ORM\Column(type: 'boolean')]
    private bool $mail_list = true;

    public function __construct()
    {
        $this->download_date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getDownloadDate(): \DateTimeInterface
    {
        return $this->download_date;
    }

    public function setDownloadDate(\DateTimeInterface $download_date): self
    {
        $this->download_date = $download_date;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ip_address;
    }

    public function setIpAddress(?string $ip_address): self
    {
        $this->ip_address = $ip_address;
        return $this;
    }

    public function getConsent(): bool
    {
        return $this->consent;
    }

    public function setConsent(bool $consent): self
    {
        $this->consent = $consent;
        return $this;
    }

    public function getMailList(): bool
    {
        return $this->mail_list;
    }

    public function setMailList(bool $mail_list): self
    {
        $this->mail_list = $mail_list;
        return $this;
    }
}