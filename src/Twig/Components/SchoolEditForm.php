<?php

namespace App\Twig\Components;

use App\Form\SchoolType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class SchoolEditForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface

    {
        return $this->createForm(SchoolType::class);
    }
}