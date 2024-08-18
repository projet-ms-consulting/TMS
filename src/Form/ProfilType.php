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
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $user = $options['data'] ?? null;

        $firstName = $user->getPerson()->getFirstName() ?? '';
        $lastName = $user->getPerson()->getLastName() ?? '';

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
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
                'data' => $firstName,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'mapped' => false,
                'data' => $lastName,
            ])
            ->add('modifFile', ChoiceType::class, [
                'label' => 'Modifier/Ajouter un fichier : ',
                'choices' => [
                    'CV' => 'cv',
                    'Lettre de motivation' => 'lettre_motivation',
                    'Convention de stage' => 'convention_stage',
                    'Rapport de stage' => 'rapport_stage',
                    'Autre document' => 'autre_document',
                ],
                'mapped' => false,
                'required' => false,
            ])
            ->addDependent('cv', 'modifFile', function (DependentField $field, ?string $modifFile) {
                if ('cv' === $modifFile) {
                    $field->add(FileType::class, [
                        'label' => 'CV : ',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez sélectionner un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                    ]);
                }
            })
            ->addDependent('lm', 'modifFile', function (DependentField $field, ?string $modifFile) {
                if ('lettre_motivation' === $modifFile) {
                    $field->add(FileType::class, [
                        'label' => 'Lettre de motivation : ',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez sélectionner un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                    ]);
                }
            })
            ->addDependent('cs', 'modifFile', function (DependentField $field, ?string $modifFile) {
                if ('convention_stage' === $modifFile) {
                    $field->add(FileType::class, [
                        'label' => 'Convention de stage : ',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez sélectionner un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                    ]);
                }
            })
            ->addDependent('rs', 'modifFile', function (DependentField $field, ?string $modifFile) {
                if ('rapport_stage' === $modifFile) {
                    $field->add(FileType::class, [
                        'label' => 'Rapport de stage : ',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez sélectionner un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                    ]);
                }
            })
            ->addDependent('other', 'modifFile', function (DependentField $field, ?string $modifFile) {
                if ('autre_document' === $modifFile) {
                    $field->add(FileType::class, [
                        'label' => 'Autre fichier : ',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png', ],
                                'mimeTypesMessage' => 'Veuillez sélectionner un fichier PDF, JPEG ou PNG valide',
                            ]),
                        ],
                    ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
