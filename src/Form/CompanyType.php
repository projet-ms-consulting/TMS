<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Company;
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

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dynamicBuilder = new DynamicFormBuilder($builder);

        $company = $options['data'] ?? null;
        $hasAddress = $company && $company->getAddress();

        $dynamicBuilder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'entreprise',
//                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s\-]+$/',
                        'message' => 'Le nom de l\'entreprise n\'est pas valide.',
                    ]),
                ],
            ])
            ->add('companyType', TextType::class, [
                'label' => 'Type d\'entreprise',
//                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('checkAddress', ChoiceType::class, [
                'label' => 'Avez-vous déjà créé une adresse ?',
//                'attr' => ['class' => 'form-control'],
                'mapped' => false,
                'data' => $hasAddress,
                'choices' => [
                    'Je ne veux pas d\'adresse' => null,
                    'Oui' => true,
                    'Non' => false,
                ],
            ])
            ->addDependent('address', 'checkAddress', function (DependentField $field, ?bool $checkAddress) {
                if (true === $checkAddress) {
                    $field->add(EntityType::class, [
                        'class' => Address::class,
                        'label' => 'Adresse',
//                        'attr' => ['class' => 'form-control'],
                        'choice_label' => function (Address $address) {
                            return $address->getFullAddress();
                        },
                        'placeholder' => 'Choisissez une adresse',
                        'required' => true,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('a')
                                ->orderBy('a.city', 'ASC');
                        },
                    ]);
                }
            })
            ->addDependent('nbStreetNewAddress', 'checkAddress', function (DependentField $field, ?bool $checkAddress) {
                if (false === $checkAddress) {
                    $field->add(TextType::class, [
                        'label' => 'Numéro de Rue',
//                        'attr' => ['class' => 'form-control'],
                        'mapped' => false,
                        'required' => true,
                        'constraints' => [
                            new Regex([
                                'pattern' => '/^\d+[a-zA-Z]?$/',
                                'message' => 'Le numéro de rue n\'est pas valide.',
                            ]),
                        ],
                    ]);
                }
            })
            ->addDependent('streetNewAddress', 'checkAddress', function (DependentField $field, ?bool $checkAddress) {
                if (false === $checkAddress) {
                    $field->add(TextType::class, [
                        'label' => 'Voirie',
//                        'attr' => ['class' => 'form-control'],
                        'mapped' => false,
                        'required' => true,
                        'constraints' => [
                            new Regex([
                                'pattern' => '/^[a-zA-Z0-9\s\-]+$/',
                                'message' => 'Le nom de la voirie n\'est pas valide.',
                            ]),
                        ],
                    ]);
                }
            })
            ->addDependent('zipCodeNewAddress', 'checkAddress', function (DependentField $field, ?bool $checkAddress) {
                if (false === $checkAddress) {
                    $field->add(TextType::class, [
                        'label' => 'Code postal',
//                        'attr' => ['class' => 'form-control'],
                        'mapped' => false,
                        'required' => true,
//                        'attr' => [
//                            'min' => 10000,
//                            'max' => 99999,
//                        ],
                        'constraints' => [
                            new Regex([
                                'pattern' => '/^\d{5}$/',
                                'message' => 'Le code postal n\'est pas valide.',
                            ]),
                        ],
                    ]);
                }
            })
            ->addDependent('cityNewAddress', 'checkAddress', function (DependentField $field, ?bool $checkAddress) {
                if (false === $checkAddress) {
                    $field->add(TextType::class, [
                        'label' => 'Ville',
//                        'attr' => ['class' => 'form-control'],
                        'mapped' => false,
                        'required' => true,
                        'constraints' => [
                            new Regex([
                                'pattern' => '/^[a-zA-Z\s\-]+$/',
                                'message' => 'La ville n\'est pas valide.',
                            ]),
                        ],
                    ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}

