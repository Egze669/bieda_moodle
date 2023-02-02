<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $autor = null;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Answer::class)]
    private Collection $answers;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $activationDate = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $deactivationDate = null;

    /**
     * @param $title
     * @param $description
     * @param $autor
     * @param $activationDate
     * @param $deactivationDate
     */
    public function __construct($title, $description , $autor, $activationDate, $deactivationDate)
    {
        $this->title = $title;
        $this->description = $description;
        $this->autor = $autor;
        $this->activationDate = $activationDate;
        $this->deactivationDate = $deactivationDate;
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAutor(): ?User
    {
        return $this->autor;
    }

    public function setAutor(?User $autor): self
    {
        $this->autor = $autor;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setTask($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getTask() === $this) {
                $answer->setTask(null);
            }
        }

        return $this;
    }

    public function getActivationDate(): ?\DateTimeInterface
    {
        return $this->activationDate;
    }

    public function setActivationDate(\DateTimeInterface $activationDate): self
    {
        $this->activationDate = $activationDate;

        return $this;
    }

    public function getDeactivationDate(): ?\DateTimeInterface
    {
        return $this->deactivationDate;
    }

    public function setDeactivationDate(\DateTimeInterface $deactivationDate): self
    {
        $this->deactivationDate = $deactivationDate;

        return $this;
    }
}
