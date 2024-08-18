<?php

namespace App\Twig\Components;

use App\Entity\Person;
use App\Form\ProfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProfilEditForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;


    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ProfilType::class);
    }
}
