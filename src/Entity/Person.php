<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startInternship = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endInternship = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, SocialNetwork>
     */
    #[ORM\OneToMany(targetEntity: SocialNetwork::class, mappedBy: 'person', orphanRemoval: true)]
    private Collection $socialNetworks;

    /**
     * @var Collection<int, Files>
     */
    #[ORM\OneToMany(targetEntity: Files::class, mappedBy: 'person', orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToOne(mappedBy: 'person', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'person')]
    private Collection $projects;

    #[ORM\OneToOne(inversedBy: 'person', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Address $address = null;

    #[ORM\ManyToOne(inversedBy: 'internshipSupervisor')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $intershipSupervisor = null;

    #[ORM\ManyToOne(inversedBy: 'schoolSupervisor')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $schoolSupervisor = null;

    #[ORM\ManyToOne(inversedBy: 'manager')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $manager = null;

    public function __construct()
    {
        $this->socialNetworks = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getStartInternship(): ?\DateTimeInterface
    {
        return $this->startInternship;
    }

    public function setStartInternship(\DateTimeInterface $startInternship): static
    {
        $this->startInternship = $startInternship;

        return $this;
    }

    public function getEndInternship(): ?\DateTimeInterface
    {
        return $this->endInternship;
    }

    public function setEndInternship(?\DateTimeInterface $endInternship): static
    {
        $this->endInternship = $endInternship;

        return $this;
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
     * @return Collection<int, SocialNetwork>
     */
    public function getSocialNetworks(): Collection
    {
        return $this->socialNetworks;
    }

    public function addSocialNetwork(SocialNetwork $socialNetwork): static
    {
        if (!$this->socialNetworks->contains($socialNetwork)) {
            $this->socialNetworks->add($socialNetwork);
            $socialNetwork->setPerson($this);
        }

        return $this;
    }

    public function removeSocialNetwork(SocialNetwork $socialNetwork): static
    {
        if ($this->socialNetworks->removeElement($socialNetwork)) {
            // set the owning side to null (unless already changed)
            if ($socialNetwork->getPerson() === $this) {
                $socialNetwork->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Files>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(Files $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setPerson($this);
        }

        return $this;
    }

    public function removeFile(Files $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getPerson() === $this) {
                $file->setPerson(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        // set the owning side of the relation if necessary
        if ($user->getPerson() !== $this) {
            $user->setPerson($this);
        }

        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addPerson($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            $project->removePerson($this);
        }

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getIntershipSupervisor(): ?User
    {
        return $this->intershipSupervisor;
    }

    public function setIntershipSupervisor(?User $intershipSupervisor): static
    {
        $this->intershipSupervisor = $intershipSupervisor;

        return $this;
    }

    public function getSchoolSupervisor(): ?User
    {
        return $this->schoolSupervisor;
    }

    public function setSchoolSupervisor(?User $schoolSupervisor): static
    {
        $this->schoolSupervisor = $schoolSupervisor;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): static
    {
        $this->manager = $manager;

        return $this;
    }
}
