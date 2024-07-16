<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password')
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'mapped' => false,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'mapped' => false,
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
                    'Autre document ' => 'Autre document ',
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