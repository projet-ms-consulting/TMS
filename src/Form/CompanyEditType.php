<?php

namespace App\Form;
use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class CompanyEditType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder

            ->add('name', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s\-]+$/',
                        'message' => 'Le nom de l\'entreprise n\'est pas valide.',
                    ]),
                ],
            ])
            ->add('companyType', TextType::class, [
                'label' => 'Type d\'entreprise',
                'required' => false,
            ])

            ->add('nbStreet', TextType::class, [
                'label' => 'Numéro de rue',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[0-9\s\-]+$/',
                        'message' => 'Le nom de la voirie n\'est pas valide.',
                    ]),
                ],
            ])
            ->add('street', TextType::class, [
                'label' => 'Nom de rue',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s\-]+$/',
                        'message' => 'Le nom de la voirie n\'est pas valide.',
                    ]),
                ],
            ])

            ->add('zipCode', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[0-9\s\-]+$/',
                        'message' => 'Le code postal n\'est pas valide.',
                    ]),
                ],
            ])

            ->add('city', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s\-]+$/',
                        'message' => 'La ville n\'est pas valide.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}

