<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('address', AddressType::class, [
                'data' => $options['data']->getAddress(),
                'mapped' => false,
                'label' => 'Adresse',
            ])
            ->add('employeeNumber', TextType::class, [
                'label' => 'Nombre d\'employés',
                'data' => $options['data']->getEmployeeNumber(),
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[0-9]+$/',
                        'message' => 'Veuillez entrer un nombre',
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
