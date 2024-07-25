<?php

namespace App\Twig\Components;

use App\Entity\Person;
use App\Form\PersonType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class PersonEditForm extends AbstractController
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
        return $this->createForm(PersonType::class, $this->personne);
    }
}
