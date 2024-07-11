<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email : '
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe : ',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Role : ',
                'multiple' => true,
                'expanded' => false,
                'choices' => [
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                    'Manager' => 'ROLE_ADMIN',
                    'Stagiaire' => 'ROLE_TRAINEE',
                    'Responsable école' => 'ROLE_SCHOOL_INTERNSHIP',
                    'Responsable de stage' => 'ROLE_COMPANY_INTERNSHIP',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'selected_person' => null,
        ]);
    }
}
