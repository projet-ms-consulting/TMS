<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;


    /**
     * @var Collection<int, Person>
     */
    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'internshipSupervisor')]
    private Collection $internshipSupervisor;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'schoolSupervisor')]
    private Collection $schoolSupervisor;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'manager')]
    private Collection $manager;

    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Person $person = null;

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: true)]
    private ?bool $everLoggedIn = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $passwordResetToken = null;

    #[ORM\Column]
    private ?bool $canLogin = null;

    public function __construct()
    {
        $this->internshipSupervisor = new ArrayCollection();
        $this->schoolSupervisor = new ArrayCollection();
        $this->manager = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
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
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    /**
     * @return Collection<int, Person>
     */
    public function getInternshipSupervisor(): Collection
    {
        return $this->internshipSupervisor;
    }

    public function addInternshipSupervisor(Person $person): static
    {
        if (!$this->internshipSupervisor->contains($person)) {
            $this->internshipSupervisor->add($person);
            $person->setInternshipSupervisor($this);
        }

        return $this;
    }

    public function removeInternshipSupervisor(Person $person): static
    {
        if ($this->internshipSupervisor->removeElement($person)) {
            // set the owning side to null (unless already changed)
            if ($person->getInternshipSupervisor() === $this) {
                $person->setInternshipSupervisor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getSchoolSupervisor(): Collection
    {
        return $this->schoolSupervisor;
    }

    public function addSchoolSupervisor(Person $schoolSupervisor): static
    {
        if (!$this->schoolSupervisor->contains($schoolSupervisor)) {
            $this->schoolSupervisor->add($schoolSupervisor);
            $schoolSupervisor->setSchoolSupervisor($this);
        }

        return $this;
    }

    public function removeSchoolSupervisor(Person $schoolSupervisor): static
    {
        if ($this->schoolSupervisor->removeElement($schoolSupervisor)) {
            // set the owning side to null (unless already changed)
            if ($schoolSupervisor->getSchoolSupervisor() === $this) {
                $schoolSupervisor->setSchoolSupervisor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getManager(): Collection
    {
        return $this->manager;
    }

    public function addManager(Person $manager): static
    {
        if (!$this->manager->contains($manager)) {
            $this->manager->add($manager);
            $manager->setManager($this);
        }

        return $this;
    }

    public function removeManager(Person $manager): static
    {
        if ($this->manager->removeElement($manager)) {
            // set the owning side to null (unless already changed)
            if ($manager->getManager() === $this) {
                $manager->setManager(null);
            }
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function isEverLoggedIn(): ?bool
    {
        return $this->everLoggedIn;
    }

    public function setEverLoggedIn(bool $everLoggedIn): static
    {
        $this->everLoggedIn = $everLoggedIn;

        return $this;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function setPasswordResetToken(?string $passwordResetToken): static
    {
        $this->passwordResetToken = $passwordResetToken;

        return $this;
    }

    public function getEmailChoice(): ?string
    {
        return $this->person->getEmailChoice();
    }

    public function isCanLogin(): ?bool
    {
        return $this->canLogin;
    }

    public function setCanLogin(bool $canLogin): static
    {
        $this->canLogin = $canLogin;

        return $this;
    }

}
