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

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;


    #[ORM\JoinTable(name: 'project_person')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'person_id', referencedColumnName: 'id')]
    private Collection $person;

    #[ORM\OneToMany(targetEntity: Links::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private Collection $links;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Company $company = null;

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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
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

    public function addPerson(Person $person): self
    {
        if (!$this->person->contains($person)) {
            $this->person->add($person);
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        $this->person->removeElement($person);

        return $this;
    }

    /**
     * @return Collection<int, Links>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Links $link): self
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setProject($this);
        }

        return $this;
    }

    public function removeLink(Links $link): self
    {
        if ($this->links->removeElement($link)) {
            // set the owning side to null (unless already changed)
            if ($link->getProject() === $this) {
                $link->setProject(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
