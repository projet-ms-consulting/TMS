<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Person;
use App\Entity\Project;
use App\Entity\School;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraineeRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startInternship', null, [
                'widget' => 'single_text',
                'data' => new \DateTime(),
            ])
            ->add('endInternship', null, [
                'widget' => 'single_text',
                'data' => (new \DateTime())->modify('+1 week'),
            ])
            ->add('mailPerso')
            ->add('mailPro')
            ->add('internshipSupervisor', EntityType::class, [
                'class' => Person::class,
                'choice_label' => 'fullName',
            ])
            ->add('schoolSupervisor', EntityType::class, [
                'class' => Person::class,
                'choice_label' => 'fullName',
            ])
            ->add('manager', EntityType::class, [
                'class' => Person::class,
                'choice_label' => 'fullName',
            ])
            ->add('school', EntityType::class, [
                'class' => School::class,
                'choice_label' => 'name',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'name',
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
