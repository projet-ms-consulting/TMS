<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Person;
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
            ->add('mailPerso', EmailType::class, [
                'label' => 'Email personnelle : ',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('mailPro', EmailType::class, [
                'label' => 'Email professionnelle : ',
                'required' => false,
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
            ->addDependent('refEntrepCompany', 'labelRole', function (DependentField $field, ?int $labelRole) {
                if ($labelRole == 6) {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'label' => 'Entreprise',
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
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'label' => 'Référent entreprise',
                        'choice_label' => 'fullName',
                        'mapped' => false,
                    ]);
                }
            })
            ->add('checkUser', ChoiceType::class, [
                'label' => 'peut ce connecter ?',
                'data' => true,
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'mapped' => false,
            ])
            ->addDependent('user', 'checkUser', function (DependentField $field, ?bool $checkUser) {
                if ($checkUser === true) {
                    $field->add(PasswordType::class, [
                        'label' => 'Mot de passe : ',
                        'attr' => ['class' => 'form-control'],
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
