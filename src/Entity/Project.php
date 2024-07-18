<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $git = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'projects')]
    private Collection $person;

    /**
     * @var Collection<int, OtherLinks>
     */
    #[ORM\OneToMany(targetEntity: OtherLinks::class, mappedBy: 'Project')]
    private Collection $otherLinks;

    public function __construct()
    {
        $this->person = new ArrayCollection();
        $this->otherLinks = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getGit(): ?string
    {
        return $this->git;
    }

    public function setGit(?string $git): static
    {
        $this->git = $git;

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
     * @return Collection<int, Person>
     */
    public function getPerson(): Collection
    {
        return $this->person;
    }

    public function addPerson(Person $person): static
    {
        if (!$this->person->contains($person)) {
            $this->person->add($person);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        $this->person->removeElement($person);

        return $this;
    }

    /**
     * @return Collection<int, OtherLinks>
     */
    public function getOtherLinks(): Collection
    {
        return $this->otherLinks;
    }

    public function addOtherLink(OtherLinks $otherLink): static
    {
        if (!$this->otherLinks->contains($otherLink)) {
            $this->otherLinks->add($otherLink);
            $otherLink->setProject($this);
        }

        return $this;
    }

    public function removeOtherLink(OtherLinks $otherLink): static
    {
        if ($this->otherLinks->removeElement($otherLink)) {
            // set the owning side to null (unless already changed)
            if ($otherLink->getProject() === $this) {
                $otherLink->setProject(null);
            }
        }

        return $this;
    }
}
