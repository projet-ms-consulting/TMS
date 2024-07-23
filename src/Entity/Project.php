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


    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'projects')]
    private Collection $person;

    /**
     * @var Collection<int, Links>
     */
    #[ORM\OneToMany(targetEntity: Links::class, mappedBy: 'Project')]
    private Collection $links;

    public function __construct()
    {
        $this->person = new ArrayCollection();
        $this->links = new ArrayCollection();
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
     * @return Collection<int, Links>
     */
    public function getOtherLinks(): Collection
    {
        return $this->links;
    }

    public function addOtherLink(Links $link): static
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setProject($this);
        }

        return $this;
    }

    public function removeOtherLink(Links $link): static
    {
        if ($this->links->removeElement($link)) {
            // set the owning side to null (unless already changed)
            if ($link->getProject() === $this) {
                $link->setProject(null);
            }
        }

        return $this;
    }
}
