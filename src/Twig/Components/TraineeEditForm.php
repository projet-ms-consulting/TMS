<?php

namespace App\Twig\Components;

use App\Entity\Person;
use App\Form\PersonType;
use App\Form\TraineeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TraineeEditForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public Person $personne;

    public function __construct()
    {
        // Initialisation de $personne avec une nouvelle instance de Person
        $this->personne = new Person();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(TraineeType::class, $this->personne);
    }
}
