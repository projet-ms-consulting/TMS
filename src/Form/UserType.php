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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                    'Manager' => 'ROLE_ADMIN',
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
            ->addDependent('startInternship','roles',  function (DependentField $field, ? string $roles){
                if ($roles === 'ROLE_TRAINEE')  {
                    $field->add( DateType::class, [
                        'mapped' => false,
                        'attr' => ['class' => 'form-control']]);
                }
            })
            ->addDependent('endInternship','roles',  function (DependentField $field, ? string $roles) {
            if ($roles === 'ROLE_TRAINEE') {
                $field->add(DateType::class, [
                    'mapped' => false,
                    'attr' => ['class' => 'form-control']]);
            }
        })
            ->addDependent('school','roles',  function (DependentField $field, ? string $roles) {
                if ($roles === 'ROLE_TRAINEE') {
                    $field->add(EntityType::class, [
                        'class' => School::class,
                        'choice_label' => 'name',
                        'mapped' => false,
                        'attr' => ['class' => 'form-control']]);
                }
            })
            ->addDependent('schoolSupervisors','school',  function (DependentField $field, ? School $school) {
                if ($school) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'choices' => $school->getPeople(),
                        'choice_label' => 'fullName',
                        'mapped' => false,
                        'attr' => ['class' => 'form-control']]);
                }
            })
            ->addDependent('company','roles',  function (DependentField $field, ? string $roles) {
                if ($roles === 'ROLE_TRAINEE') {
                    $field->add(EntityType::class, [
                        'class' => Company::class,
                        'choice_label' => 'name',
                        'mapped' => false,
                        'attr' => ['class' => 'form-control']]);
                }
            })
            ->addDependent('internshipSupervisors','company',  function (DependentField $field, ? Company $company) {
                if ($company) {
                    $field->add(EntityType::class, [
                        'class' => Person::class,
                        'choices' => $company->getPerson(),
                        'choice_label' => 'fullName',
                        'mapped' => false,
                        'attr' => ['class' => 'form-control']]);
                }

            } )

        ;

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'selected_person' => null,
        ]);
    }
}
