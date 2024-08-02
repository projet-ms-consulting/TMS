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
            ])
            ->addDependent('companyReferent', 'company', function (DependentField $field, ?Company $company) {
                if (null != $company) {
                    // Obtenez toutes les personnes associées à l'entreprise
                    $allPersons = $company->getPerson()->toArray();

                    // Filtrez pour ne garder que celles avec le rôle ROLE_COMPANY_REFERENT
                    $filteredPersons = array_filter($allPersons, function ($person) {
                        return in_array('ROLE_COMPANY_REFERENT', $person->getRoles());
                    });
                    usort($filteredPersons, function ($a, $b) {
                        return strcmp($a->getlastName(), $b->getlastName());
                    });

                    if (0 == count($filteredPersons)) {
                        $field->add(ChoiceType::class, [
                            'label' => 'Référent entreprise',
                            'choices' => [
                                'Aucun référent entreprise trouvé' => null,
                            ],
                            'mapped' => false,
                            'attr' => ['class' => 'form-control'],
                        ]);
                    } else {
                        $field->add(ChoiceType::class, [
                            'label' => 'Référent entreprise',
                            'choices' => array_combine(
                                array_map(function ($person) { return $person->getFullName(); }, $filteredPersons),
                                array_map(function ($person) { return $person->getId(); }, $filteredPersons)
                            ),
                            'choice_label' => function ($choice, $key, $value) {
                                return $key;
                            },
                            'mapped' => false,
                            'attr' => ['class' => 'form-control'],
                            'placeholder' => 'Choisir référent entreprise',
                        ]);
                    }
                }
            })
            ->addDependent('manager', 'company', function (DependentField $field, ?Company $company) {
                if (null != $company) {
                    // Obtenez toutes les personnes associées à l'entreprise
                    $allPersons = $company->getPerson()->toArray();

                    // Filtrez pour ne garder que celles avec le rôle ROLE_ADMIN
                    $filteredPersons = array_filter($allPersons, function ($person) {
                        return in_array('ROLE_ADMIN', $person->getRoles());
                    });
                    usort($filteredPersons, function ($a, $b) {
                        return strcmp($a->getlastName(), $b->getlastName());
                    });
                    if (0 == count($filteredPersons)) {
                        $field->add(ChoiceType::class, [
                            'label' => 'Chef d\'entreprise : ',
                            'choices' => [
                                'Aucun chef d\'entreprise trouvé' => null,
                            ],
                            'mapped' => false,
                            'attr' => ['class' => 'form-control'],
                        ]);
                    } else {
                        $field->add(ChoiceType::class, [
                            'label' => 'Chef d\'entreprise : ',
                            'choices' => array_combine(
                                array_map(function ($person) { return $person->getFullName(); }, $filteredPersons),
                                array_map(function ($person) { return $person->getId(); }, $filteredPersons)
                            ),
                            'choice_label' => function ($choice, $key, $value) {
                                return $key;
                            },
                            'mapped' => false,
                            'attr' => ['class' => 'form-control'],
                            'placeholder' => 'Choisir chef d\'entreprise',
                        ]);
                    }
                }
            })
            ->addDependent('internshipSupervisor', 'company', function (DependentField $field, ?Company $company) {
                if (null != $company) {
                    // Obtenez toutes les personnes associées à l'entreprise
                    $allPersons = $company->getPerson()->toArray();

                    // Filtrez pour ne garder que celles avec le rôle ROLE_COMPANY_INTERNSHIP
                    $filteredPersons = array_filter($allPersons, function ($person) {
                        return in_array('ROLE_COMPANY_INTERNSHIP', $person->getRoles());
                    });
                    usort($filteredPersons, function ($a, $b) {
                        return strcmp($a->getlastName(), $b->getlastName());
                    });

                    if (0 == count($filteredPersons)) {
                        $field->add(ChoiceType::class, [
                            'label' => 'Maître de stage : ',
                            'choices' => [
                                'Aucun maître de stage trouvé' => null,
                            ],
                            'mapped' => false,
                            'attr' => ['class' => 'form-control'],
                        ]);
                    } else {
                        $field->add(ChoiceType::class, [
                            'label' => 'Maître de stage :',
                            'choices' => array_combine(
                                array_map(function ($person) { return $person->getFullName(); }, $filteredPersons),
                                array_map(function ($person) { return $person->getId(); }, $filteredPersons)
                            ),
                            'choice_label' => function ($choice, $key, $value) {
                                // Since the choices are now the person's ID, the label is the person's full name which is the key in this context
                                return $key;
                            },
                            'mapped' => false,
                            'attr' => ['class' => 'form-control'],
                            'placeholder' => 'Choisir maître de stage',
                        ]);
                    }
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
                if (null != $school) {
                    // Obtenez toutes les personnes associées à l'école
                    $allPersons = $school->getPeople()->toArray();

                    // Filtrez pour ne garder que celles avec le rôle ROLE_COMPANY_INTERNSHIP
                    $filteredPersons = array_filter($allPersons, function ($person) {
                        return in_array('ROLE_SCHOOL_INTERNSHIP', $person->getRoles());
                    });
                    // Trier les personnes filtrées par nom complet par ordre alphabétique
                    usort($filteredPersons, function ($a, $b) {
                        return strcmp($a->getFullName(), $b->getFullName());
                    });

                    if (0 == count($filteredPersons)) {
                        $field->add(ChoiceType::class, [
                            'label' => 'Référent école',
                            'choices' => [
                                'Aucun référent école trouvé' => null,
                            ],
                            'mapped' => false,
                            'attr' => ['class' => 'form-control'],
                        ]);
                    } else {
                        $field->add(ChoiceType::class, [
                            'label' => 'Référent école',
                            'choices' => array_combine(
                                array_map(function ($person) { return $person->getFullName(); }, $filteredPersons),
                                array_map(function ($person) { return $person->getId(); }, $filteredPersons)
                            ),
                            'choice_label' => function ($choice, $key, $value) {
                                // Puisque les choix sont désormais l'id de la personne, l'étiquette est le nom complet de la personne, ce qui est la clé dans ce contexte.
                                return $key;
                            },
                            'mapped' => false,
                            'placeholder' => 'Choisir référent école',
                            'attr' => ['class' => 'form-control'],
                        ]);
                    }
                }
            })
            ->add('cv', FileType::class, [
                'label' => 'CV : ',
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
                'attr' => ['class' => 'form-control'],
            ])
            ->add('coverLetter', FileType::class, [
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
                'attr' => ['class' => 'form-control'],
            ])
            ->add('internshipAgreement', FileType::class, [
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
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'company' => null,
        ]);
    }
}
