<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class CompanyEmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', null, [
                'label' => 'Nom : ',
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas contenir plus de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('firstName', null, [
                'label' => 'Prénom : ',
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres.',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas contenir plus de {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('mailContact', EmailType::class, [
                'label' => 'Email de contact : ',
                'required' => false,
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle :',
                'choices' => [
                    'Chef d\'entreprise' => 'ROLE_ADMIN',
                    'Maître de stage' => 'ROLE_COMPANY_INTERNSHIP',
                    'Référent entreprise' => 'ROLE_COMPANY_REFERENT',
                ],
                'mapped' => false,
                'expanded' => false,
                'multiple' => false,
                'data' => isset($options['data']) && !empty($options['data']->getRoles()) ? $options['data']->getRoles()[0] : null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
