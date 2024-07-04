<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\School;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', null, [
                'label' => 'Nom : ',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom : ',
            ])
            ->add('startInternship', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date début de stage : ',
            ])
            ->add('endInternship', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date fin de stage : ',
            ])
            ->add('school', EntityType::class, [
                'class' => School::class,
                'label' => 'Ecole : ',
                'choice_label' => 'name',
                'placeholder' => 'Choisir une ecole',
            ])
            ->add('cv', FileType::class,[
                'label' => 'Ajouter un CV  : ',
                'mapped'=> false,
                'required' => false,
                'constraints'=> [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                    ])
                ]
            ])
            ->add('coverLetter', FileType::class,[
                'label' => 'Ajouter une lettre de motivation : ',
                'mapped'=> false,
                'required' => false,
                'constraints'=> [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                        ])
                ]
            ])
            ->add('internshipAgreement', FileType::class,[
                'label' => 'Ajouter une convention de stage : ',
                'mapped'=> false,
                'required' => false,
                'constraints'=> [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class, FileType::class
        ]);
    }
}
