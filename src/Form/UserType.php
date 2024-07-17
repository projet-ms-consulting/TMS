<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Person;
use App\Entity\School;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;



class UserType extends AbstractType
{
//    private $user;
//    public function __construct(User $user)
//    {
//        $this->user = $user;
//    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
            $builder = new DynamicFormBuilder($builder);

//            $user = $builder->getData();
//            $person = $user->getPerson();

            $builder
//                ->add('emailChoice', ChoiceType::class,[
//                    'choices' => [
//                        $person->getMailPro() => 'Mail professionnel',
//                        $person->getMailPerso() => 'Mail personnel',
//                    ],
//                    'expanded' => false,
//                    'multiple' => false,
//                    'label' => 'Choisissez le type de mail à utiliser',
//                    'mapped' => false,
//                    'attr' => ['class' => 'form-control'],
//                ])
                ->add('password', PasswordType::class, [
                    'label' => 'Mot de passe :',
                    'attr' => ['class' => 'form-control']
                ])
                ->add('roles', ChoiceType::class, [
                    'label' => 'Rôle :',
                    'placeholder' => 'Choisir un rôle',
                    'choices' => [
                        'Super Admin' => 'ROLE_SUPER_ADMIN',
                        'Chef d\'entreprise' => 'ROLE_ADMIN',
                        'Stagiaire' => 'ROLE_TRAINEE',
                        'Référent école' => 'ROLE_SCHOOL_INTERNSHIP',
                        'Maître de stage' => 'ROLE_COMPANY_INTERNSHIP',
                        'Référent entreprise' => 'ROLE_COMPANY_REFERENT',
                    ],
                    'mapped' => false,
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => ['class' => 'form-control'],
                ])
        ->addDependent('startInternship', 'roles', function (DependentField $field, ?string $roles) {
                    if ($roles === 'ROLE_TRAINEE') {
                        $field->add(DateType::class, [
                            'label' => 'Date début de stage :',
                            'mapped' => false,
                            'attr' => ['class' => 'form-control']]);
                    }
                })
                ->addDependent('endInternship', 'roles', function (DependentField $field, ?string $roles) {
                    if ($roles === 'ROLE_TRAINEE') {
                        $field->add(DateType::class, [
                            'label' => 'Date fin de stage :',
                            'mapped' => false,
                            'attr' => ['class' => 'form-control']]);
                    }
                })
                ->addDependent('school', 'roles', function (DependentField $field, ?string $roles) {
                    if ($roles === 'ROLE_TRAINEE') {
                        $field->add(EntityType::class, [
                            'class' => School::class,
                            'label' => 'École : ',
                            'choice_label' => 'name',
                            'placeholder' => 'Choisir école',
                            'mapped' => false,
                            'attr' => ['class' => 'form-control']]);
                    }
                })
                ->addDependent('schoolSupervisors', 'school', function (DependentField $field, ?School $school) {
                    if ($school) {
                        $field->add(EntityType::class, [
                            'class' => Person::class,
                            'label' => 'Référent école : ',
                            'choices' => $school->getPeople(),
                            'choice_label' => 'fullName',
                            'placeholder' => 'Référent école',
                            'mapped' => false,
                            'attr' => ['class' => 'form-control']]);
                    }
                })
                ->addDependent('company', 'roles', function (DependentField $field, ?string $roles) {
                    if ($roles === 'ROLE_TRAINEE') {
                        $field->add(EntityType::class, [
                            'class' => Company::class,
                            'label' => 'Entreprise : ',
                            'choice_label' => 'name',
                            'mapped' => false,
                            'attr' => ['class' => 'form-control']]);
                    }
                })
                ->addDependent('internshipSupervisors', 'company', function (DependentField $field, ?Company $company) {
                    if ($company) {
                        $field->add(EntityType::class, [
                            'class' => Person::class,
                            'label' => 'Maître de stage : ',
                            'choices' => $company->getPerson(),
                            'choice_label' => 'fullName',
                            'placeholder' => 'Maître de stage',
                            'mapped' => false,
                            'attr' => ['class' => 'form-control']]);
                    }
                })
                ->addDependent('cv', 'roles', function (DependentField $field, ?string $roles) {
                    if ($roles === 'ROLE_TRAINEE') {
                        $field->add(FileType::class, [
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
                            'attr' => ['class' => 'form-control'],
                        ]);
                    }
                })
                ->addDependent('coverLetter', 'roles', function (DependentField $field, ?string $roles) {
                    if ($roles === 'ROLE_TRAINEE') {
                        $field->add(FileType::class, [
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
                            'attr' => ['class' => 'form-control'],
                        ]);
                    }
                })
                ->addDependent('internshipAgreement', 'roles', function (DependentField $field, ?string $roles) {
                    if ($roles === 'ROLE_TRAINEE') {
                        $field->add(FileType::class, [
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
                            'attr' => ['class' => 'form-control'],
                        ]);
                    }
                });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'selected_person' => null,
        ]);
    }

}
