<?php

namespace App\Form;

use App\Dto\TaskDTO;
use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @template-extends  AbstractType<int>
 */
class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description',TextareaType::class)
            ->add('title', TextType::class)
            ->add('activationDate',DateTimeType::class)
            ->add('deactivationDate',DateTimeType::class)
        ;
    }
    public function __toString(): string {
        /** @var string $parent */
        $parent = $this->getParent();
        return $parent;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskDto::class,
        ]);
    }
}
