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
    private string $lastName;

    #[ORM\Column(length: 255)]
    private string $firstName;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startInternship = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endInternship = null;

    #[ORM\Column(nullable: true)]
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

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'person')]
    private Collection $projects;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'internshipSupervisors')]
    #[ORM\JoinColumn(name: 'internship_supervisor_id', referencedColumnName: 'id', nullable: true)]
    private ?Person $internshipSupervisor = null;

    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'internshipSupervisor')]
    private Collection $internshipSupervisors;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'schoolSupervisors')]
    #[ORM\JoinColumn(name: 'school_supervisor_id', referencedColumnName: 'id', nullable: true)]
    private ?Person $schoolSupervisor = null;

    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'schoolSupervisor')]
    private Collection $schoolSupervisors;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'managers')]
    #[ORM\JoinColumn(name: 'manager_id', referencedColumnName: 'id', nullable: true)]
    private ?Person $manager = null;

    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'manager')]
    private Collection $managers;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?School $school = null;

    #[ORM\ManyToOne(inversedBy: 'person')]
    private ?Company $company = null;

    #[ORM\OneToOne(mappedBy: 'person', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'companyReferents')]
    private ?Person $companyReferent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'companyReferent')]
    private Collection $companyReferents;


    public function __construct()
    {
        $this->socialNetworks = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->internshipSupervisors = new ArrayCollection();
        $this->schoolSupervisors = new ArrayCollection();
        $this->managers = new ArrayCollection();
        $this->companyReferents = new ArrayCollection();
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

    public function getInternshipSupervisor(): ?Person
    {
        return $this->internshipSupervisor;
    }

    public function setInternshipSupervisor(?Person $internshipSupervisor): static
    {
        $this->internshipSupervisor = $internshipSupervisor;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getInternshipSupervisors(): Collection
    {
        return $this->internshipSupervisors;
    }

    public function addInternshipSupervisor(self $internshipSupervisor): static
    {
        if (!$this->internshipSupervisors->contains($internshipSupervisor)) {
            $this->internshipSupervisors->add($internshipSupervisor);
            $internshipSupervisor->setInternshipSupervisor($this);
        }

        return $this;
    }

    public function removeInternshipSupervisor(self $internshipSupervisor): static
    {
        if ($this->internshipSupervisors->removeElement($internshipSupervisor)) {
            // set the owning side to null (unless already changed)
            if ($internshipSupervisor->getInternshipSupervisor() === $this) {
                $internshipSupervisor->setInternshipSupervisor(null);
            }
        }

        return $this;
    }

    public function getSchoolSupervisor(): ?Person
    {
        return $this->schoolSupervisor;
    }

    public function setSchoolSupervisor(?Person $schoolSupervisor): static
    {
        $this->schoolSupervisor = $schoolSupervisor;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSchoolSupervisors(): Collection
    {
        return $this->schoolSupervisors;
    }

    public function addSchoolSupervisor(self $schoolSupervisor): static
    {
        if (!$this->schoolSupervisors->contains($schoolSupervisor)) {
            $this->schoolSupervisors->add($schoolSupervisor);
            $schoolSupervisor->setSchoolSupervisor($this);
        }

        return $this;
    }

    public function removeSchoolSupervisor(self $schoolSupervisor): static
    {
        if ($this->schoolSupervisors->removeElement($schoolSupervisor)) {
            // set the owning side to null (unless already changed)
            if ($schoolSupervisor->getSchoolSupervisor() === $this) {
                $schoolSupervisor->setSchoolSupervisor(null);
            }
        }

        return $this;
    }

    public function getManager(): ?Person
    {
        return $this->manager;
    }

    public function setManager(?Person $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    public function addManager(self $manager): static
    {
        if (!$this->managers->contains($manager)) {
            $this->managers->add($manager);
            $manager->setManager($this);
        }

        return $this;
    }

    public function removeManager(self $manager): static
    {
        if ($this->managers->removeElement($manager)) {
            // set the owning side to null (unless already changed)
            if ($manager->getManager() === $this) {
                $manager->setManager(null);
            }
        }

        return $this;
    }


    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

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


    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    public function getCompanyReferent(): ?Person
    {
        return $this->companyReferent;
    }

    public function setCompanyReferent(?Person $companyReferent): static
    {
        $this->companyReferent = $companyReferent;

        return $this;
    }

    /**
     * @return Collection<int, person>
     */
    public function getCompanyReferents(): Collection
    {
        return $this->companyReferents;
    }

    public function addCompanyReferent(Person $companyReferent): static
    {
        if (!$this->companyReferents->contains($companyReferent)) {
            $this->companyReferents->add($companyReferent);
            $companyReferent->setCompanyReferent($this);
        }

        return $this;
    }

    public function removeCompanyReferent(Person $companyReferent): static
    {
        if ($this->companyReferents->removeElement($companyReferent)) {
            // set the owning side to null (unless already changed)
            if ($companyReferent->getCompanyReferent() === $this) {
                $companyReferent->setCompanyReferent(null);
            }
        }

        return $this;
    }


}
