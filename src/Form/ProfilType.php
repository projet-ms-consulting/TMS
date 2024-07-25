<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => false,
                'mapped' => false,
                'toggle' => true,
                'toggle_container_classes' => ['text-blue-600'],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'mapped' => false,
                'required' => true,
                'data' => $options['data']->getPerson()->getFirstName(),
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'mapped' => false,
                'data' => $options['data']->getPerson()->getLastName(),
            ])
            ->add('cv', FileType::class, [
                'label' => 'Télécharger un fichier (PDF ou JPG)',
                'mapped' => false,
                'required' => false,
                                'constraints' => [
                                    new File([
                                        'maxSize' => '1024k',
                                        'mimeTypes' => [
                                             'application/pdf',
                                             'image/jpeg',
                                        ],
                                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF ou JPG valide.',
                                    ])
                                ],
            ])
            ->add('cvType', ChoiceType::class, [
                'label' => 'Type de fichier',
                'mapped' => false,
                'choices' => [
                    'CV' => 'CV',
                    'Lettre de motivation' => 'Lettre de motivation',
                    'Convention de stage' => 'Convention de stage',
                    'Autre document' => 'Autre document',
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
