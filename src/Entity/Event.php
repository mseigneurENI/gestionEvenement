<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez saisir un nom.')]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuillez choisir une date de début.')]
    #[Assert\GreaterThan('today', message: 'La date de début doit être plus tard qu\'aujourd\'hui.')]
    private ?\DateTime $beginDateEvent = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuillez choisir une date de fin.')]
    #[Assert\GreaterThan(propertyPath: 'beginDateEvent', message: 'La date de fin doit être après la date de début.')]
    private ?\DateTime $endDate = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuillez choisir une date limite d\'inscription.')]
    #[Assert\LessThan(propertyPath: 'beginDateEvent', message: 'La date limite d\'inscription doit être avant la date de début de l\'événement.')]
    private ?\DateTime $limitDateRegistration = null;

    #[ORM\Column]
    private ?int $registrationMaxNb = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Place $place = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'events')]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'organisedEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organiser = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cancellationReason = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

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

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getOrganiser(): ?User
    {
        return $this->organiser;
    }

    public function setOrganiser(?User $organiser): static
    {
        $this->organiser = $organiser;

        return $this;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function setCancellationReason(string $cancellationReason): static
    {
        $this->cancellationReason = $cancellationReason;

        return $this;
    }

}
