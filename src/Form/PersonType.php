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
use Doctrine\ORM\EntityRepository;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $context = $options['context'];

        $builder
            ->add('lastName', null, [
                'label' => 'Nom : ',
                'attr' => ['class' => 'form-control']
            ])
            ->add('firstName', null, [
                'label' => 'Prénom : ',
                'attr' => ['class' => 'form-control']
            ])
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
            ])
            ->add('cv', FileType::class, [
                'label' => 'Ajouter un CV : ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                    ]),
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('coverLetter', FileType::class, [
                'label' => 'Ajouter une lettre de motivation : ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                    ]),
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('internshipAgreement', FileType::class, [
                'label' => 'Ajouter une convention de stage : ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                    ]),
                ],
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class, FileType::class
        ]);
    }
}
