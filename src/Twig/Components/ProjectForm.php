<?php

namespace App\Twig\Components;

use App\Form\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\Form\FormInterface;

#[AsLiveComponent]
final class ProjectForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ProjectType::class);
    }
}
