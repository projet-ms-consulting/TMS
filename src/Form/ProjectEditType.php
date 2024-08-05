<?php

namespace App\Form;

use App\Entity\Company;
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
        $company = $options['company'];
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
            ->add('link', EntityType::class, [
                'class' => Links::class,
                'choice_label' => function (Links $links) {
                    return $links->getLink();
                },
                'label' => 'Lien du projet',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'company' => Project::class->getCompany(),
        ]);
    }
}
