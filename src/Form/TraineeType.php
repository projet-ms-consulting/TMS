<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Files;
use App\Entity\Person;
use App\Entity\School;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class TraineeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('lastName', null, [
                'label' => 'Nom : ',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres.'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas contenir plus de {{ limit }} caractères',
                    ])
                ],
            ])
            ->add('firstName', null, [
                'label' => 'Prénom : ',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres.'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas contenir plus de {{ limit }} caractères',
                    ])
                ],
            ])
            ->add('mailContact', EmailType::class, [
                'label' => 'Email de contact : ',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startInternship', DateType::class, [
                'label' => 'Date début de stage',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Range([
                        'min' => (new \DateTimeImmutable())->modify('-3 months'),
                        'max' => (new \DateTimeImmutable())->modify('+1 year'),
                        'notInRangeMessage' => 'La date doit être entre le {{ min }} et le {{ max }}',
                    ]),
                ],
            ])
            ->add('endInternship', null, [
                'label' => 'Date fin de stage',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Range([
                        'min' => (new \DateTimeImmutable()),
                        'max' => (new \DateTimeImmutable())->modify('+1 year'),
                        'notInRangeMessage' => 'La date doit être entre le {{ min }} et le {{ max }}',
                    ]),
                ],
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'label' => 'Entreprise : ',
                'choice_label' => 'name',
                'placeholder' => 'Choisir une entreprise',
                'attr' => ['class' => 'form-control'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->addDependent('manager', 'company', function (DependentField $field, ?Company $company) {
                if ($company) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'choice_label' => function (Person $person) {
                            return $person->getFullName();
                        },
                        'label' => 'Chef de l\'entreprise : ',
                        'query_builder' => function (EntityRepository $er) use ($company) {
                            return $er->createQueryBuilder('p')
                                ->innerJoin('p.user', 'u')
                                ->where('u.roles LIKE :role')
                                ->andWhere('p.company = :company')
                                ->setParameter('role', '%"ROLE_ADMIN"%')
                                ->setParameter('company', $company)
                                ->orderBy('p.id', 'ASC');
                        },
                        'attr' => ['class' => 'form-control'],
                    ]);
                }
            })
            ->addDependent('internshipSupervisor', 'company', function (DependentField $field, ?Company $company) {
                if ($company) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'choice_label' => function (Person $person) {
                            return $person->getFullName();
                        },
                        'label' => 'Maître de stage : ',
                        'query_builder' => function (EntityRepository $er) use ($company) {
                            return $er->createQueryBuilder('p')
                                ->innerJoin('p.user', 'u')
                                ->where('u.roles LIKE :role')
                                ->andWhere('p.company = :company')
                                ->setParameter('role', '%"ROLE_COMPANY_INTERNSHIP"%')
                                ->setParameter('company', $company)
                                ->orderBy('p.id', 'ASC');
                        },
                        'attr' => ['class' => 'form-control'],
                    ]);
                }
            })
            ->addDependent('companyReferent', 'company', function (DependentField $field, ?Company $company) {
                if ($company) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'choice_label' => function (Person $person) {
                            return $person->getFullName();
                        },
                        'label' => 'Référent de l\'entreprise : ',
                        'query_builder' => function (EntityRepository $er) use ($company) {
                            return $er->createQueryBuilder('p')
                                ->innerJoin('p.user', 'u')
                                ->where('u.roles LIKE :role')
                                ->andWhere('p.company = :company')
                                ->setParameter('role', '%"ROLE_COMPANY_REFERENT"%')
                                ->setParameter('company', $company)
                                ->orderBy('p.id', 'ASC');
                        },
                        'attr' => ['class' => 'form-control'],
                    ]);
                }
            })
            ->add('school', EntityType::class, [
                'class' => School::class,
                'label' => 'École',
                'choice_label' => 'name',
                'placeholder' => 'Choisir une école',
                'attr' => ['class' => 'form-control'],
            ])
            ->addDependent('schoolSupervisor', 'school', function (DependentField $field, ?School $school) {
                if ($school) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'choice_label' => function (Person $person) {
                            return $person->getFullName();
                        },
                        'label' => 'Référent de l\'école : ',
                        'query_builder' => function (EntityRepository $er) use ($school) {
                            return $er->createQueryBuilder('p')
                                ->innerJoin('p.user', 'u')
                                ->where('u.roles LIKE :role')
                                ->andWhere('p.school = :school')
                                ->setParameter('role', '%"ROLE_SCHOOL_INTERNSHIP"%')
                                ->setParameter('school', $school)
                                ->orderBy('p.id', 'ASC');
                        },
                        'attr' => ['class' => 'form-control'],
                    ]);
                }
            })
            ->add('modifCv', ChoiceType::class, [
                'label' => 'Modifier/Ajouter CV : ',
                'choices' => [
                    'Non' => false,
                    'Oui' => true,
                ],
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->addDependent('cv', 'modifCv', function (DependentField $field, ?bool $modifCv) {
                if ($modifCv) {
                    $field->add(FileType::class, [
                        'label' => 'CV : ',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez sélectionner un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                        'attr' => ['class' => 'form-control'],
                    ]);
                }
            })
            ->add('modifCoverLetter', ChoiceType::class, [
                'label' => 'Modifier/Ajouter une lettre de motivation : ',
                'choices' => [
                    'Non' => false,
                    'Oui' => true,
                ],
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->addDependent('coverLetter', 'modifCoverLetter', function (DependentField $field, ?bool $modifCoverLetter) {
                if ($modifCoverLetter) {
                    $field->add(FileType::class, [
                        'label' => 'Lettre de motivation : ',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez sélectionner un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                        'attr' => ['class' => 'form-control'],
                    ]);
                }
            })
            ->add('modifInternshipAgreement', ChoiceType::class, [
                'label' => 'Modifier/Ajouter une convention de stage : ',
                'choices' => [
                    'Non' => false,
                    'Oui' => true,
                ],
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
            ])
            ->addDependent('internshipAgreement', 'modifInternshipAgreement', function (DependentField $field, ?bool $modifInternshipAgreement) {
                if ($modifInternshipAgreement) {
                    $field->add(FileType::class, [
                        'label' => 'Convention de stage : ',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez sélectionner un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                        'attr' => ['class' => 'form-control'],
                    ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
