<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\School;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraineeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startInternship', DateType::class, [
                    'widget' => 'single_text',
                    'label' => 'Date début de stage : ',
                    'attr' => ['class' => 'form-control']
                ])
            ->add('endInternship', DateType::class, [
                        'widget' => 'single_text',
                        'label' => 'Date fin de stage : ',
                        'attr' => ['class' => 'form-control']
                    ])
            ->add('school', EntityType::class, [
                        'class' => School::class,
                        'label' => 'École : ',
                        'choice_label' => 'name',
                        'placeholder' => 'Choisir une école',
                        'attr' => ['class' => 'form-control']
                    ]);
//            ->add('schoolSupervisor', EntityType::class, [
//                'class' => Person::class,
//                'label' => 'Responsable école : ',
//                'choice_label' => 'person.schoolSupervisor.firstName',
//                'placeholder' => 'Choisir une école',
//                'attr' => ['class' => 'form-control']
//            ]);

//            ->add('internshipAgreement', FileType::class, [
//                        'label' => 'Ajouter une convention de stage : ',
//                        'mapped' => false,
//                        'required' => false,
//                        'constraints' => [
//                            new File([
//                                'mimeTypes' => [
//                                    'application/pdf',
//                                    'image/jpeg',
//                                    'image/png',
//                                ],
//                                'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
//                            ]),
//                        ],
//                        'attr' => ['class' => 'form-control']
//                    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'selected_person' => null,
        ]);
    }
}
