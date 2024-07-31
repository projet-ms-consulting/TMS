<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyType = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $employeeNumber = null;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    private ?Address $address = null;

    /**
     * @var Collection<int, person>
     */
    #[ORM\OneToMany(targetEntity: person::class, mappedBy: 'company')]
    private Collection $person;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'company', orphanRemoval: true)]
    #[ORM\Column(nullable: true)]
    private Collection $projects;

    public function __construct()
    {
        $this->person = new ArrayCollection();
        $this->projects = new ArrayCollection();
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

    public function getCompanyType(): ?string
    {
        return $this->companyType;
    }

    public function setCompanyType(?string $companyType): static
    {
        $this->companyType = $companyType;

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

    public function getEmployeeNumber(): ?string
    {
        return $this->employeeNumber;
    }

    public function setEmployeeNumber(?string $employeeNumber): static
    {
        $this->employeeNumber = $employeeNumber;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, person>
     */
    public function getPerson(): Collection
    {
        return $this->person;
    }

    public function addPerson(person $person): static
    {
        if (!$this->person->contains($person)) {
            $this->person->add($person);
            $person->setCompany($this);
        }

        return $this;
    }

    public function removePerson(person $person): static
    {
        if ($this->person->removeElement($person)) {
            // set the owning side to null (unless already changed)
            if ($person->getCompany() === $this) {
                $person->setCompany(null);
            }
        }

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
            $project->setCompany($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getCompany() === $this) {
                $project->setCompany(null);
            }
        }

        return $this;
    }
}
