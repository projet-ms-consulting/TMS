<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Person;
use App\Entity\School;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbStreet', null, ['attr' => ['class' => 'form-control']])
            ->add('street', null, ['attr' => ['class' => 'form-control']])
            ->add('zipCode', null, ['attr' => ['class' => 'form-control']])
            ->add('city', null, ['attr' => ['class' => 'form-control']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
