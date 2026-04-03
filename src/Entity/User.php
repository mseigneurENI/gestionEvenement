<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'This username already exists. Please find another one.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Veuillez saisir un pseudo')]
    #[Assert\Length(min: 3, max: 180, minMessage: 'Votre pseudo doit comporter entre {{ min }} et {{ max }} caractères.')]
    #[Assert\Regex(pattern: '/[@<>]/', message: 'Votre pseudo ne doit pas contenir de symbole @, < ou >', match: false)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuillez saisir un mot de passe')]
    #[Assert\Length(min: 6, minMessage: 'Votre mot de passe doit comporter {{ min }} caractères minimum.')]
//    #[Assert\Regex('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/',  message: 'Votre mot de passe doit comporter au moins six caractères, une majuscule, un chiffre et un caractère spécial.')]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre prénom')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Votre prénom doit comporter {{ min }} caractères minimum.', maxMessage: 'Votre prénom doit comporter {{ max }} caractères maximum.')]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez saisir votre nom')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Votre nom doit comporter {{ min }} caractères minimum.', maxMessage: 'Votre nom doit comporter {{ max }} caractères maximum.')]
    private ?string $lastname = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Regex('/^\d{10}$/', message: 'Votre numéro de téléphone.')]
    private ?string $phoneNb = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email(message: '{{value}} n\'est pas un mail valide.')]
    #[Assert\Length(max: 255, maxMessage: 'Votre adresse mail doit comporter moins de {{ limit }} caractères.')]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    private Collection $events;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'organiser')]
    private Collection $organisedEvents;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->organisedEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhoneNb(): ?string
    {
        return $this->phoneNb;
    }

    public function setPhoneNb(?string $phoneNb): static
    {
        $this->phoneNb = $phoneNb;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

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
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->addParticipant($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getOrganisedEvents(): Collection
    {
        return $this->organisedEvents;
    }

    public function addOrganisedEvent(Event $organisedEvent): static
    {
        if (!$this->organisedEvents->contains($organisedEvent)) {
            $this->organisedEvents->add($organisedEvent);
            $organisedEvent->setOrganiser($this);
        }

        return $this;
    }

    public function removeOrganisedEvent(Event $organisedEvent): static
    {
        if ($this->organisedEvents->removeElement($organisedEvent)) {
            // set the owning side to null (unless already changed)
            if ($organisedEvent->getOrganiser() === $this) {
                $organisedEvent->setOrganiser(null);
            }
        }

        return $this;
    }
}
