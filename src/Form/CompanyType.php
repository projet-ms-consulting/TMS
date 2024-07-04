<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Company;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'entreprise',
            ])
            ->add('companyType', TextType::class, [
                'label' => 'Type d\'entreprise',
            ])
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'choice_label' => function (Address $address) {
                    return $address->getFullAddress();
                },
                'label' => 'Adresse',
            ])
            ->add('employeeNumber', TextType::class, [
                'label' => 'Nombre d\'employés'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
