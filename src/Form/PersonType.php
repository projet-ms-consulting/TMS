<?php
namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', null, [
                'label' => 'Nom : ',
                'attr' => ['class' => 'form-control']
            ])
            ->add('firstName', null, [
                'label' => 'Prénom : ',
                'attr' => ['class' => 'form-control']
            ])
            ->add('mailPerso', EmailType::class, [
                'label' => 'Email personnelle : ',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('mailPro', EmailType::class, [
                'label' => 'Email professionnelle : ',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('labelRole', ChoiceType::class, [
                'label' => 'Rôle :',
                'placeholder' => 'Choisir un rôle',
                'choices' => [
                    'Super Admin' =>  'Super Admin',
                    'Chef d\'entreprise' => 'Manager',
                    'Stagiaire' => 'Stagiaire',
                    'Référent école' => 'Référent école',
                    'Maître de stage' => 'Maître de stage',
                    'Référent entreprise' => 'Chef d\'entreprise',
                ],
                'mapped' => false,
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'selected_person' => null,
        ]);
    }
}
