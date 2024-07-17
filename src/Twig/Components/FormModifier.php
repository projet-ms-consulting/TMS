<?php

namespace App\Twig\Components;

use App\Entity\Person;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\Form\FormInterface;

#[AsLiveComponent]
final class FormModifier extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;


    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UserType::class);
    }
}
