<?php

namespace App\Form;

use App\Entity\Links;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class ProjectLinksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('labelChoice', ChoiceType::class, [
                'label' => 'Lien',
                'choices' => [
                    'Choisissez un lien' => null,
                    'Github' => 'Github',
                    'Trello' => 'Trello',
                    'Autre' => 'Autre',
                ],
                'mapped' => false,
            ])
            ->addDependent('linkGit', 'labelChoice', function (DependentField $field, ?string $labelChoice) {
                if ('Github' === $labelChoice) {
                    $field->add(TextType::class, [
                        'label' => 'Lien git',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new Regex([
                                'pattern' => '/^https:\/\/github\.com(\/[a-zA-Z0-9\-_]+)+$/',
                                'message' => 'Le lien git n\'est pas valide.',
                            ]),
                        ],
                    ]);
                }
            })
            ->addDependent('linkTrello', 'labelChoice', function (DependentField $field, ?string $labelChoice) {
                if ('Trello' === $labelChoice) {
                    $field->add(TextType::class, [
                        'label' => 'Lien Trello',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new Regex([
                                'pattern' => '/^https:\/\/trello\.com(\/[a-zA-Z0-9\-_]+)+$/',
                                'message' => 'Le lien trello n\'est pas valide.',
                            ]),
                        ],
                    ]);
                }
            })
            ->addDependent('linkOther', 'labelChoice', function (DependentField $field, ?string $labelChoice) {
                if ('Autre' === $labelChoice) {
                    $field->add(TextType::class, [
                        'label' => 'Autre lien',
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new Regex([
                                'pattern' => '/^https:\/\/[^\s]*\.[^\s]*$/',
                                'message' => 'Le lien n\'est pas valide.',
                            ]),
                        ],
                    ]);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
