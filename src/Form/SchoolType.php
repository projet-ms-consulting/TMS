<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\School;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchoolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'choice_label' => function(Address $address) {
                    return $address->getNbStreet().' '.$address->getStreet().', '.$address->getZipCode().' '.$address->getCity();
                },
                'placeholder' => 'Chosissez une adresse',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $currentAddress = $options['data']->getAddress();

                    $qb = $er->createQueryBuilder('a');
                    $qb->leftJoin('a.school', 's')
                        ->where('s.address IS NULL');

                    if ($currentAddress) {
                        $qb->orWhere('a.id = :currentAddressId')
                            ->setParameter('currentAddressId', $currentAddress->getId());
                    }

                    return $qb;
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => School::class,
        ]);
    }
}
