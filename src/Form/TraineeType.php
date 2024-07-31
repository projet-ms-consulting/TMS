<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Person;
use App\Entity\School;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TraineeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', null, [
                'label' => 'Nom : ',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('firstName', null, [
                'label' => 'Prénom : ',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('mailContact', EmailType::class, [
                'label' => 'Email de contact : ',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startInternship', DateType::class, [
                'label' => 'Date début de stage',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endInternship', null, [
                'label' => 'Date fin de stage',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'label' => 'Entreprise : ',
                'choice_label' => 'name',
                'placeholder' => 'Choisir une entreprise',
                'attr' => ['class' => 'form-control'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->add('companyReferent', EntityType::class, [
                'class' => Person::class,
                'label' => 'Référent entreprise : ',
                'choice_label' => 'fullName',
                'placeholder' => 'Choisir un référent entreprise',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('manager', EntityType::class, [
                'class' => Person::class,
                'label' => 'Chef d\'entreprise : ',
                'choice_label' => 'fullName',
                'placeholder' => 'Choisir un chef entreprise',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('internshipSupervisor', EntityType::class, [
                'class' => Person::class,
                'label' => 'Maître de stage : ',
                'choice_label' => 'fullName',
                'placeholder' => 'Choisir un maître de stage',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('school', EntityType::class, [
                'class' => School::class,
                'label' => 'École',
                'choice_label' => 'name',
                'placeholder' => 'Choisir une école',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('schoolSupervisor', EntityType::class, [
                'class' => Person::class,
                'label' => 'Référent école',
                'choice_label' => 'fullName',
                'placeholder' => 'Choisir référent école',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('cv', FileType::class, [
                'label' => 'CV : ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png', ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('coverLetter', FileType::class, [
                'label' => 'Lettre de motivation :',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png', ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('internshipAgreement', FileType::class, [
                'label' => 'Convention de stage :',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png', ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, JPEG ou PNG valide',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'school' => null,
            'company' => null,
        ]);
    }
}
