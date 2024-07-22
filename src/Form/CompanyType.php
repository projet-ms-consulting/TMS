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
                'required' => false,
            ])
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'fullAddress',
                'placeholder' => 'Choisir une adresse existante',
                'required' => false,
                'label' => 'Adresse existante',
            ])
            ->add('nbStreet', TextType::class, [
                'mapped' => false,
                'label' => 'Numéro de rue',
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'mapped' => false,
                'label' => 'Rue',
                'required' => false,
            ])
            ->add('zipCode', TextType::class, [
                'mapped' => false,
                'label' => 'Code postal',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'mapped' => false,
                'label' => 'Ville',
                'required' => false,
            ])
            ->add('employeeNumber', TextType::class, [
                'label' => 'Nombre d\'employés',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}