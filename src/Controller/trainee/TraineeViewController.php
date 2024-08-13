<?php

namespace App\Controller\trainee;

use App\Entity\Project;
use App\Repository\PersonRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TraineeViewController extends AbstractController
{
    #[Route('/trainee/company/{id}', name: 'trainee_company')]
    public function traineeCompany(PersonRepository $personRepository)
    {
        $user = $this->getUser();
        $person = $user->getPerson();
        $company = $person->getCompany();
        $personnes =$company->getPerson();
        $query = $personRepository->filterInternshipPerCompany($company);
        $companyPersons = $query->getResult();

        return $this->render('/trainee/company.html.twig', [
            'connectedPerson' => $person,
            'company' => $company,
            'companyPersons' => $companyPersons,
        ]);
    }

    #[Route('/trainee/school/{id}', name: 'trainee_school')]
    public function traineeSchool(PersonRepository $personRepository)
    {
        $user = $this->getUser();
        $person = $user->getPerson();
        $school = $person->getSchool();
        $query = $personRepository->filterInternshipPerSchool($school);
        $schoolPersons = $query->getResult();

        return $this->render('/trainee/school.html.twig', [
            'connectedPerson' => $person,
            'school' => $school,
            'schoolPersons' => $schoolPersons,
        ]);
    }

    #[Route('/trainee/project/{id}', name: 'trainee_project')]
    public function traineeProject(ProjectRepository $projectRepository)
    {
        $user = $this->getUser();
        $person = $user->getPerson();
        $school = $person->getSchool();
        $query = $projectRepository->filterProjectPerPerson($person);
        $projects = $query->getResult();

        return $this->render('/trainee/project.html.twig', [
            'connectedPerson' => $person,
            'school' => $school,
            'projects' => $projects,
        ]);
    }
    #[Route('/trainee/project/show/{id}', name: 'trainee_project_show')]
    public function traineeProjectShow(Project $project): Response
    {
        $user = $this->getUser();
        $personne = $user->getPerson();
        $links = $project->getLinks();
        $participants = $project->getParticipant();

        foreach ($participants as $participant) {
            $participant->getFullName();
        }
        foreach ($links as $link) {
            $link->getLabel();
            $link->getLink();
        }

        return $this->render('trainee/project_show.html.twig', [
            'project' => $project,
            'connectedPerson' => $personne,
            'links' => $links,
            'participants' => $participants,
        ]);
    }
}
