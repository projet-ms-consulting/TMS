<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
                'expanded' => true, // Transforme en checkboxes
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('JSON_CONTAINS(p.roles, :role) = 1')
                        ->setParameter('role', json_encode('ROLE_TRAINEE'));
                },
            ])
            ->add('links', CollectionType::class, [
                'entry_type' => LinksType::class,
                'allow_add' => true,
                'by_reference' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
