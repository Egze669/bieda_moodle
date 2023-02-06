<?php

namespace App\Dto;

use App\Entity\Task;
use Symfony\Component\Validator\Constraints as Assert;
class TaskDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 10,
        max: 100,
        minMessage: 'Your title should be at least {{ limit }} characters',
        maxMessage: 'Your title cannot be longer than {{ limit }} characters',
    )]
    public ?string $title = '';
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 15,
        minMessage: 'Your description should be at least {{ limit }} characters long',
    )]
    public ?string $description = '';

    /**
     * @var \DateTimeInterface|null
     */
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(
        new \DateTime(),
        message: 'Activation date should not earlier than the current date'
    )]
    #[Assert\Expression(
       "(this.activationDate < this.deactivationDate)",
        message: 'Activation date should not be the same as deactivation or past it',
   )]
    public ?\DateTimeInterface $activationDate = null;
    /**
     * @var \DateTimeInterface|null
     */
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(
        new \DateTime(),
        message: 'Deactivation date should not be earlier than today'
    )]
    #[Assert\Expression(
        "(this.deactivationDate > this.activationDate)",
        message: 'Deactivation date should not be the same as activation or earlier',
    )]
    public ?\DateTimeInterface $deactivationDate = null;
    public function updateTask(Task $task):void
    {
        $this->title = $task->getTitle();
        $this->description = $task->getDescription();
        $this->activationDate = $task->getActivationDate();
        $this->deactivationDate = $task->getDeactivationDate();
    }
}