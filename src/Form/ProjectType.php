<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Person;
use App\Entity\Project;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('name', TextType::class, ['label' => 'Nom du projet'])
            ->add('description')
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'name',
                'label' => 'Entreprise',
                'placeholder' => 'Sélectionnez une entreprise',
                'required' => true,
            ])
            ->addDependent('participant', 'company', function (DependentField $field, ?Company $company) {
                if ($company) {
                    $field->add(EntityType::class, [
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
                    ]);
                }
            })
            ->add('linkGit', TextType::class, [
                'label' => 'Lien git',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^https:\/\/github\.com(\/[a-zA-Z0-9\-_])+(\/[a-zA-Z0-9\-_])+$/',
                        'message' => 'Le lien git n\'est pas valide.',
                    ]),
                ],
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
