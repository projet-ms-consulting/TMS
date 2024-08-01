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



    #[ORM\OneToMany(targetEntity: Links::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private Collection $links;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Company $company = null;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'projects')]
    private Collection $Participant;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->Participant = new ArrayCollection();
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
     * @return Collection<int, Links>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink($url, $label): self
    {
        $this->links[] = [
            'url' => $url,
            'label' => $label,
        ];

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

    /**
     * @return Collection<int, Person>
     */
    public function getParticipant(): Collection
    {
        return $this->Participant;
    }

    public function addParticipant(Person $participant): static
    {
        if (!$this->Participant->contains($participant)) {
            $this->Participant->add($participant);
        }

        return $this;
    }

    public function removeParticipant(Person $participant): static
    {
        $this->Participant->removeElement($participant);

        return $this;
    }
}
