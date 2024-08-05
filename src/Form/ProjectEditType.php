<?php

namespace App\Form;

use App\Entity\Links;
use App\Entity\Person;
use App\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['data']->getCompany() ?? null;
        $builder
            ->add('name')
            ->add('description')
            ->add('participant', EntityType::class, [
                'class' => Person::class,
                'choice_label' => function (Person $person) {
                    return $person->getFullName();
                },
                'label' => 'Participants',
                'multiple' => true,
                'expanded' => true, // Transforme en checkboxes
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('p')
                        ->innerJoin('p.user', 'u')
                        ->where('u.roles LIKE :role')
                        ->andWhere('p.company = :company')
                        ->setParameter('role', '%"ROLE_TRAINEE"%')
                        ->setParameter('company', $company)
                        ->orderBy('p.id', 'ASC');
                },
            ])
            ->add('links', EntityType::class, [
                'class' => Links::class,
                'choice_label' => 'label',
                'label' => 'Liens',
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('l')
                        ->where('l.project = :project')
                        ->setParameter('project', $options['data'])
                        ->orderBy('l.id', 'ASC');
                },
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
