<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Person;
use App\Entity\School;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;



class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
            $builder = new DynamicFormBuilder($builder);

            $user = $builder->getData();
            $person = $user->getPerson();

            $builder
                ->add('email', ChoiceType::class,[
                    'choices' => [
                        $person->getMailPro() => 'Mail professionnel',
                        $person->getMailPerso() => 'Mail personnel',
                    ],
                    'expanded' => false,
                    'multiple' => false,
                    'label' => 'Choisissez l\'adresse mail',
                    'mapped' => false,
                    'attr' => ['class' => 'form-control'],
                ])
                    ->add('email', EmailType::class, [
                        'label' => 'Adresse email :',
                    'attr' => ['class' => 'form-control']

                    ])
                ->add('password', PasswordType::class, [
                    'label' => 'Mot de passe :',
                    'attr' => ['class' => 'form-control']
                ])
                ->add('roles', ChoiceType::class, [
                    'label' => 'Rôle :',
                    'placeholder' => 'Choisir un rôle',
                    'choices' => [
                        'Chef d\'entreprise' => 'ROLE_ADMIN',
                        'Stagiaire' => 'ROLE_TRAINEE',
                        'Référent école' => 'ROLE_SCHOOL_INTERNSHIP',
                        'Maître de stage' => 'ROLE_COMPANY_INTERNSHIP',
                        'Référent entreprise' => 'ROLE_COMPANY_REFERENT',
                    ],
                    'mapped' => false,
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => ['class' => 'form-control'],
                ]);
                if ($person->getlabelRole() === 'Stagiaire' && $user->getRoles() === 'ROLE_TRAINEE') {
                    $builder
                        ->add('startInternship', DateType::class, [
                            'label' => 'Date début de stage :',
                            'mapped' => false,
                            'attr' => ['class' => 'form-control']
                        ])
                        ->add('endInternship', DateType::class, [
                            'label' => 'Date fin de stage :',
                            'mapped' => false,
                            'attr' => ['class' => 'form-control']
                        ])
                        ->add('school', EntityType::class, [
                            'class' => School::class,
                            'label' => 'École : ',
                            'choice_label' => 'name',
                            'placeholder' => 'Choisir école',
                            'mapped' => false,
                            'attr' => ['class' => 'form-control']
                        ])
//                        ->add('schoolSupervisors', EntityType::class, [
//                            'class' => Person::class,
//                            'label' => 'Référent école : ',
//                            'choices' => $school->getPeople(),
//                            'choice_label' => 'fullName',
//                            'placeholder' => 'Référent école',
//                            'mapped' => false,
//                            'attr' => ['class' => 'form-control'],
//                        ])
                        ->add('company', EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise : ',
                        'choice_label' => 'name',
                        'placeholder' => 'Choisir une entreprise',
                        'mapped' => false,
                        'attr' => ['class' => 'form-control']
                        ])
//                        ->add('internshipSupervisors', EntityType::class, [
//                            'class' => Person::class,
//                            'label' => 'Maître de stage : ',
//                            'choices' => $person->getSchool()->getPeople(),
//                            'choice_label' => 'fullName',
//                            'placeholder' => 'Maître de stage : ',
//                            'mapped' => false,
//                            'attr' => ['class' => 'form-control']
//                        ])
                        ->add('cv', FileType::class, [
                            'label' => 'CV :',
                            'mapped' => false,
                            'required' => false,
                            'constraints' => [
                                new File([
                                    'mimeTypes' => [
                                        'application/pdf',
                                        'image/jpeg',
                                        'image/png',
                                    ],
                                    'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                                ]),
                            ],
                            'attr' => ['class' => 'form-control'],
                            ])
                        ->add('coverLetter', FileType::class, [
                            'label' => 'Lettre de motivation : ',
                            'mapped' => false,
                            'required' => false,
                            'constraints' => [
                                new File([
                                    'mimeTypes' => [
                                        'application/pdf',
                                        'image/jpeg',
                                        'image/png',
                                    ],
                                    'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                                ]),
                            ],
                            'attr' => ['class' => 'form-control'],
                        ])
                        ->add('internshipAgreement', FileType::class, [
                        'label' => 'Convention de stage : ',
                        'mapped' => false,
                        'required' => false,
                            'attr' => ['class' => 'form-control'],
                            'constraints' => [
                                new File([
                                    'mimeTypes' => [
                                        'application/pdf',
                                        'image/jpeg',
                                        'image/png',
                                    ],
                                    'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                                ]),
                            ],
                        ]);
                }


//                ->addDependent('startInternship', 'roles', function (DependentField $field, ?string $roles) {
//                    if ($roles == 'ROLE_TRAINEE') {
//                        $field->add(DateType::class, [
//                            'label' => 'Date début de stage : ',
//                            'mapped' => false,
//                            'attr' => ['class' => 'form-control']
//                        ]);
//                    }
//                })
//                ->addDependent('endInternship', 'roles', function (DependentField $field, ?string $roles) {
//                    if ($roles == 'ROLE_TRAINEE') {
//                        $field->add(DateType::class, [
//                            'label' => 'Date fin de stage : ',
//                            'mapped' => false,
//                            'attr' => ['class' => 'form-control']
//                        ]);
//                    }
//                })
//                ->addDependent('company', 'roles', function (DependentField $field, ?string $roles) {
//                    if ($roles == 'ROLE_TRAINEE') {
//                        $field->add(EntityType::class, [
//                            'class' => Company::class,
//                            'label' => 'Entreprise : ',
//                            'placeholder' => 'Choisir une entreprise',
//                            'choice_label' => 'name',
//                            'mapped' => false,
//                            'attr' => ['class' => 'form-control']
//                        ]);
//                    }
//                })
//                ->addDependent('internshipSupervisors', 'company', function (DependentField $field, ?Company $company) {
//                    if ($company) {
//                        $field->add(EntityType::class, [
//                            'class' => Person::class,
//                            'label' => 'Maître de stage : ',
//                            'placeholder' => 'Maître de stage : ',
//                            'choices' => $company->getPeople(),
//                            'choice_label' => 'fullName',
//                            'mapped' => false,
//                            'attr' => ['class' => 'form-control']
//                        ]);
//                    }
//                });

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'selected_person' => null,
        ]);
    }

}
