<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Report;
use App\Form\ReportType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReportController extends AbstractController
{
    /**
     * @Route("/signalement/confirmation", name="confirmReport")
     */
    public function confirmReport()
    {
        return $this->render("cdv/report/confirmReport.html.twig");
    }
    
    /**
     * @Route("/signalement/{id}", name="report")
     */
    public function index(User $user, UserRepository $userRepo, Request $request, EntityManagerInterface $manager)
    {   
        $target = $userRepo->findOneBy(['id'=>$user]);
        if($target && $target !== $this->getUser()){
            $report = new Report();
            $form = $this->createForm(ReportType::class, $report);
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $report ->setUser($this->getUser())
                        ->setTarget($target);

                $manager->persist($report);
                $manager->flush();

                return $this->redirectToRoute('confirmReport');
            }
    
            return $this->render('cdv/report/index.html.twig', [
                'form'=>$form->createView(),
            ]);

        } else {
            throw new Exception("Une erreur est survenue");
        }
    }

}
