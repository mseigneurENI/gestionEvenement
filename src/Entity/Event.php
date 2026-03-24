<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $beginDateEvent = null;

    #[ORM\Column]
    private ?\DateTime $endDate = null;

    #[ORM\Column]
    private ?\DateTime $limitDateRegistration = null;

    #[ORM\Column]
    private ?int $registrationMaxNb = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBeginDateEvent(): ?\DateTime
    {
        return $this->beginDateEvent;
    }

    public function setBeginDateEvent(\DateTime $beginDateEvent): static
    {
        $this->beginDateEvent = $beginDateEvent;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLimitDateRegistration(): ?\DateTime
    {
        return $this->limitDateRegistration;
    }

    public function setLimitDateRegistration(\DateTime $limitDateRegistration): static
    {
        $this->limitDateRegistration = $limitDateRegistration;

        return $this;
    }

    public function getRegistrationMaxNb(): ?int
    {
        return $this->registrationMaxNb;
    }

    public function setRegistrationMaxNb(int $registrationMaxNb): static
    {
        $this->registrationMaxNb = $registrationMaxNb;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }
}
