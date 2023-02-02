<?php

namespace App\Form;

use App\Entity\Answer;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content',FileType::class,[
                'data_class' => FilesystemOperator::class,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'extensions' => ['pdf','txt'],
                        'extensionsMessage' => 'Please upload .pdf or .txt extension file'
                    ])
    ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Answer::class,
        ]);
    }
}
