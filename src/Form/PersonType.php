<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Person;
use App\Entity\School;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('lastName', null, [
                'label' => 'Nom : ',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('firstName', null, [
                'label' => 'Prénom : ',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('labelRole', ChoiceType::class, [
                'label' => 'Rôle :',
                'placeholder' => 'Choisir un rôle',
                'choices' => [
                    'Super Admin' => 1,
                    'Chef d\'entreprise' => 2,
                    'Stagiaire' => 3,
                    'Référent école' => 4,
                    'Maître de stage' => 5,
                    'Référent entreprise' => 6,
                ],
                'mapped' => false,
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->addDependent('refCompany', 'labelRole', function (DependentField $field, ?int $labelRole) {
                if ($labelRole == 6) {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise',
                        'choice_label' => 'name',
                        'mapped' => false,
                    ]);
                }
            })
            ->addDependent('chefCompany', 'labelRole', function (DependentField $field, ?int $labelRole) {
                if ($labelRole == 2) {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise',
                        'choice_label' => 'name',
                        'mapped' => false,
                    ]);
                }
            })
            ->addDependent('maitreStage', 'labelRole', function (DependentField $field, ?int $labelRole) {
                if ($labelRole == 5) {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise',
                        'choice_label' => 'name',
                        'mapped' => false,
                    ]);
                }
            })
            ->addDependent('refEcole', 'labelRole', function (DependentField $field, ?int $labelRole) {
                if ($labelRole == 4) {
                    $field->add(EntityType::class, [
                        'class' => School::class,
                        'label' => 'Ecole',
                        'choice_label' => 'name',
                        'mapped' => false,
                    ]);
                }
            })
            ->addDependent('stagiaireCompany', 'labelRole', function (DependentField $field, ?int $labelRole) {
                if ($labelRole == 3) {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise',
                        'choice_label' => 'name',
                        'placeholder' => 'Choisir une entreprise',
                        'mapped' => false,
                    ]);
                }
            })
            ->addDependent('stagiaireRefEntrep', 'stagiaireCompany', function (DependentField $field, ?Company $company) {
                if ($company != null) {
                    // Obtenez toutes les personnes associées à l'entreprise
                    $allPersons = $company->getPerson()->toArray();

                    // Filtrez pour ne garder que celles avec le rôle ROLE_COMPANY_INTERNSHIP
                    $filteredPersons = array_filter($allPersons, function ($person) {
                        return in_array('ROLE_COMPANY_INTERNSHIP', $person->getRoles());
                    });

                    if (count($filteredPersons) == 0) {
                        $field->add(ChoiceType::class, [
                            'label' => 'Référent entreprise',
                            'choices' => [
                                'Aucun référent entreprise trouvé' => null,
                            ],
                            'mapped' => false,
                        ]);
                    } else {
                        $field->add(ChoiceType::class, [
                            'label' => 'Référent entreprise',
                            'choices' => array_combine(
                                array_map(function($person) { return $person->getFullName(); }, $filteredPersons),
                                array_map(function($person) { return $person->getId(); }, $filteredPersons)
                            ),
                            'choice_label' => function ($choice, $key, $value) {
                                // Since the choices are now the person's ID, the label is the person's full name which is the key in this context
                                return $key;
                            },
                            'mapped' => false,
                        ]);
                    }
                }
            })
            ->add('checkUser', ChoiceType::class, [
                'label' => 'peut ce connecter ?',
                'data' => false,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'mapped' => false,
            ])
            ->addDependent('email', 'checkUser', function (DependentField $field, ?bool $checkUser) {
                if ($checkUser) {
                    $field->add(EmailType::class, [
                        'label' => 'Email : ',
                        'attr' => ['class' => 'form-control'],
                        'mapped' => false,
                    ]);
                }
            })
            ->addDependent('password', 'checkUser', function (DependentField $field, ?bool $checkUser) {
                if ($checkUser) {
                    $field->add(PasswordType::class, [
                        'label' => 'Mot de passe',
                        'help' => 'laissez vide, pour créer un mot de passe aléatoire',
                        'attr' => ['placeholder' => 'laissez vide, pour créer un mot de passe aléatoire'],
                        'required' => false,
                        'mapped' => false,
                    ]);
                }
            })
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'selected_person' => null,
        ]);
    }
}
