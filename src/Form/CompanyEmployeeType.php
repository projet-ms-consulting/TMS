<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Person;
use App\Entity\Project;
use App\Entity\School;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres.'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas contenir plus de {{ limit }} caractères',
                    ])
                ],
            ])
            ->add('firstName', null, [
                'label' => 'Prénom : ',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Le nom ne doit contenir que des lettres.'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne doit pas contenir plus de {{ limit }} caractères',
                    ])
                ],
            ])
            ->add('mailContact', EmailType::class, [
                'label' => 'Email de contact : ',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle :',
                'placeholder' => 'Choisir un rôle',
                'choices' => [
                    'Chef d\'entreprise' => 'ROLE_ADMIN',
                    'Maître de stage' => 'ROLE_COMPANY_INTERNSHIP',
                    'Référent entreprise' => 'ROLE_COMPANY_REFERENT',
                ],
                'mapped' => false,
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' => 'form-control'],
                'data' => isset($options['data']) && !empty($options['data']->getRoles()) ? $options['data']->getRoles()[0] : null,
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'label' => 'Entreprise : ',
                'placeholder' => 'Entreprise',
                'choice_label' => 'name',
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
