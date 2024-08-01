<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbStreet', null, [
                'label' => 'Numéro de Rue',
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'data' => $options['data'] ? $options['data']->getNbStreet() : '',
            ])
            ->add('street', null, [
                'label' => 'Voirie',
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'data' => $options['data'] ? $options['data']->getStreet() : '',
            ])
            ->add('zipCode', null, [
                'label' => 'Code postal',
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'data' => $options['data'] ? $options['data']->getZipCode() : '',
            ])
            ->add('city', null, [
                'label' => 'Ville',
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'data' => $options['data'] ? $options['data']->getCity() : '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
