<?php

namespace App\Form;

use App\Entity\Company;
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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('lastName', null, [
                'label' => 'Nom',
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas contenir plus de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas contenir plus de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('mailContact', EmailType::class, [
                'label' => 'Email de contact',
                'required' => false,
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'placeholder' => 'Choisir un rôle',
                'choices' => [
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                    'Chef d\'entreprise' => 'ROLE_ADMIN',
                    'Stagiaire' => 'ROLE_TRAINEE',
                    'Référent école' => 'ROLE_SCHOOL_INTERNSHIP',
                    'Maître de stage' => 'ROLE_COMPANY_INTERNSHIP',
                    'Référent entreprise' => 'ROLE_COMPANY_REFERENT',
                ],
                'mapped' => false,
                'expanded' => false,
                'multiple' => false,
                'data' => isset($options['data']) && !empty($options['data']->getRoles()) ? $options['data']->getRoles()[0] : null,
            ])
            // Si stagiaire, afficher champ date début de stage
            ->addDependent('startInternship', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_TRAINEE' == $roles) {
                    $field->add(DateType::class, [
                        'label' => 'Date début de stage',
                        'required' => false,
                        'constraints' => [
                            new Range([
                                'min' => (new \DateTimeImmutable())->modify('-4 year'),
                                'max' => (new \DateTimeImmutable())->modify('+1 year'),
                                'notInRangeMessage' => 'La date doit être entre le {{ min }} et le {{ max }}',
                            ]),
                        ],
                    ]);
                }
            })
            // Si stagiaire, afficher champ date fin de stage
            ->addDependent('endInternship', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_TRAINEE' == $roles) {
                    $field->add(DateType::class, [
                        'label' => 'Date fin de stage',
                        'required' => false,
                        'constraints' => [
                            new Range([
                                'min' => (new \DateTimeImmutable())->modify('-4 year')->modify('+1 day'),
                                'max' => (new \DateTimeImmutable())->modify('+1 year')->modify('+1 day'),
                                'notInRangeMessage' => 'La date doit être entre le {{ min }} et le {{ max }}',
                            ]),
                        ],
                    ]);
                }
            })
            // Si Référent entreprise, afficher le champ entreprise
            ->addDependent('companyReferent', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_COMPANY_REFERENT' == $roles) {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise',
                        'choice_label' => 'name',
                        'mapped' => false,
                        'required' => true,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                ->orderBy('c.name', 'ASC');
                        },
                    ]);
                }
            })
            // Si Chef d'entreprise, afficher le champ entreprise
            ->addDependent('manager', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_ADMIN' == $roles) {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise',
                        'placeholder' => 'Choisir une entreprise',
                        'choice_label' => 'name',
                        'mapped' => false,
                        'required' => true,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                ->orderBy('c.name', 'ASC');
                        },
                    ]);
                }
            })
            // Si Maître de stage afficher entreprise
            ->addDependent('internshipSupervisor', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_COMPANY_INTERNSHIP' == $roles) {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise',
                        'placeholder' => 'Choisir une entreprise',
                        'choice_label' => 'name',
                        'mapped' => false,
                        'required' => true,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                ->orderBy('c.name', 'ASC');
                        },
                    ]);
                }
            })
            // Si Référent école, afficher le champ école
            ->addDependent('school', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_SCHOOL_INTERNSHIP' == $roles) {
                    $field->add(EntityType::class, [
                        'class' => School::class,
                        'label' => 'Ecole',
                        'required' => true,
                        'placeholder' => 'Choisir une école',
                        'choice_label' => 'name',
                        'data' => isset($options['data']) && !empty($options['data']->getSchool()) ? $options['data']->getSchool() : null,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('s')
                                ->orderBy('s.name', 'ASC');
                        },
                    ]);
                }
            })
            // Si stagiaire, afficher le champ entreprise
            ->addDependent('stagiaireCompany', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_TRAINEE' == $roles) {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise',
                        'required' => true,
                        'choice_label' => 'name',
                        'placeholder' => 'Choisir une entreprise',
                        'mapped' => false,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                ->orderBy('c.name', 'ASC');
                        },
                    ]);
                }
            })
            // Si stagiaire et si entreprise, afficher référent entreprise (correspondant à l'entreprise sélectionnée)
            ->addDependent('stagiaireRefEntrep', 'stagiaireCompany', function (DependentField $field, ?Company $company) {
                if ($company) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'mapped' => false,
                        'required' => false,
                        'label' => 'Référent de l\'entreprise : ',
                        'placeholder' => 'Choisir référent de l\'entreprise',
                        'choice_label' => function (Person $person) {
                            return $person->getFullName();
                        },
                        'query_builder' => function (EntityRepository $er) use ($company) {
                            return $er->createQueryBuilder('p')
                                ->where('p.roles LIKE :role')
                                ->andWhere('p.company = :company')
                                ->setParameter('role', '%"ROLE_COMPANY_REFERENT"%')
                                ->setParameter('company', $company)
                                ->orderBy('p.id', 'ASC');
                        },
                    ]);
                }
            })

            // Si stagiaire et si entreprise, afficher le manager (correspondant à l'entreprise sélectionnée)
            ->addDependent('stagiaireManager', 'stagiaireCompany', function (DependentField $field, ?Company $company) {
                if ($company) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'mapped' => false,
                        'required' => false,
                        'label' => 'Chef de l\'entreprise : ',
                        'placeholder' => 'Choisir chef de l\'entreprise',
                        'choice_label' => function (Person $person) {
                            return $person->getFullName();
                        },
                        'query_builder' => function (EntityRepository $er) use ($company) {
                            return $er->createQueryBuilder('p')
                                ->where('p.roles LIKE :role')
                                ->andWhere('p.company = :company')
                                ->setParameter('role', '%"ROLE_ADMIN"%')
                                ->setParameter('company', $company)
                                ->orderBy('p.id', 'ASC');
                        },
                    ]);
                }
            })
        // Si stagiaire et si entreprise, afficher le maître de stage (correspondant à l'entreprise sélectionnée)
            ->addDependent('traineeSupervisor', 'stagiaireCompany', function (DependentField $field, ?Company $company) {
                if ($company) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'mapped' => false,
                        'required' => false,
                        'label' => 'Maître de stage : ',
                        'placeholder' => 'Choisir un maître de stage',
                        'choice_label' => function (Person $person) {
                            return $person->getFullName();
                        },
                        'query_builder' => function (EntityRepository $er) use ($company) {
                            return $er->createQueryBuilder('p')
                                ->where('p.roles LIKE :role')
                                ->andWhere('p.company = :company')
                                ->setParameter('role', '%"ROLE_COMPANY_INTERNSHIP"%')
                                ->setParameter('company', $company)
                                ->orderBy('p.id', 'ASC');
                        },
                    ]);
                }
            })
            // Si stagiaire, afficher le champ école
            ->addDependent('traineeSchool', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_TRAINEE' == $roles) {
                    $field->add(EntityType::class, [
                        'class' => School::class,
                        'label' => 'École',
                        'choice_label' => 'name',
                        'placeholder' => 'Choisir une école',
                        'mapped' => false,
                        'data' => isset($options['data']) && !empty($options['data']->getSchool()) ? $options['data']->getSchool() : null,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('s')
                                ->orderBy('s.name', 'ASC');
                        },
                    ]);
                }
            })
            // Si stagiaire et si école, afficher référent école (correspondant à l'école sélectionnée)
            ->addDependent('traineeRefSchool', 'traineeSchool', function (DependentField $field, ?School $school) {
                if ($school) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'mapped' => false,
                        'required' => false,
                        'label' => 'Référent de l\'école : ',
                        'placeholder' => 'Choisir un référent école',
                        'choice_label' => function (Person $person) {
                            return $person->getFullName();
                        },
                        'query_builder' => function (EntityRepository $er) use ($school) {
                            return $er->createQueryBuilder('p')
                                ->where('p.roles LIKE :role')
                                ->andWhere('p.school = :school')
                                ->setParameter('role', '%"ROLE_SCHOOL_INTERNSHIP"%')
                                ->setParameter('school', $school)
                                ->orderBy('p.id', 'ASC');
                        },
                    ]);
                }
            })
            // Si stagiaire, afficher le champ CV
            ->addDependent('cv', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_TRAINEE' == $roles) {
                    $field->add(FileType::class, [
                        'label' => 'CV :',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                    ]);
                }
            })
            // Si stagiaire, afficher le champ Lettre de Motivation
            ->addDependent('coverLetter', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_TRAINEE' == $roles) {
                    $field->add(FileType::class, [
                        'label' => 'Lettre de motivation :',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                    ]);
                }
            })
            // Si stagiaire, afficher le champ Lettre de Convention de stage
            ->addDependent('internshipAgreement', 'roles', function (DependentField $field, ?string $roles) {
                if ('ROLE_TRAINEE' == $roles) {
                    $field->add(FileType::class, [
                        'label' => 'Convention de stage :',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                    ]);
                }
            })

            ->add('checkUser', ChoiceType::class, [
                'label' => 'Créer un compte ?',
                'data' => false,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'mapped' => false,
            ])
            // Si checkUser = true, afficher le champ email
            ->addDependent('email', 'checkUser', function (DependentField $field, ?bool $checkUser) {
                if ($checkUser) {
                    $field->add(EmailType::class, [
                        'label' => 'Email de connexion : ',
                        'mapped' => false,
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
