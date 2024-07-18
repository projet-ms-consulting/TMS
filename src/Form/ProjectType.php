<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du projet'])
            ->add('description')
            ->add('person', EntityType::class, [
                'class' => Person::class,
                'choice_label' => 'fullName',
                'label' => 'Participants',
                'multiple' => true,
            ])
            ->add('git', TextType::class, ['label' => 'Lien du git'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
