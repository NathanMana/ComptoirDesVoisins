<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Form\CGUForm;
use App\Entity\Report;
use App\Form\ReportType;
use App\Data\CGUFormData;
use App\Repository\cguRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CDVController extends AbstractController
{

    /**
     * @Route("/", name="index")
     */
    public function index(cguRepository $CGURepo)
    {
        $CGUToValidate = false;
        if($this->getUser()){
            if($this->getUser()->getLatestCGUVersionValidated() != $CGURepo->findOneBy([],['id'=>'DESC']))
            {
                $CGUToValidate = true;
            }
        }

        return $this->render('CDV/index.html.twig', [
            'CGUToValidate'=> $CGUToValidate
        ]);
    }

    /**
     * @Route("/connexion/motdepasse/envoi", name="emailSent")
     */
    public function emailSent(){
        return $this->render("cdv/account/emailSent.html.twig");
    }

    /**
     * @Route("/security/CGUValidation", name="CGUValidation")
     */
    public function CGUValidation(cguRepository $CGURepo, Request $request)
    {
        if($this->getUser()){
            if($this->getUser()->getLatestCGUVersionValidated() != $CGURepo->findOneBy([],['id'=>'DESC']))
            {
                $cguForm = new CGUFormData();
                $form = $this->createForm(CGUForm::class, $cguForm, [
                    'action' => $this->generateUrl('CGUValidation')
                ]);
                
                $form->handleRequest($request);
        
                if($form->isSubmitted() && $form->isValid())
                {
                    $manager = $this->getDoctrine()->getManager();
        
                    $user = $this->getUser();
                    $user   ->setUpdatedAt(new \DateTime()) 
                            ->setLatestVersionCGUValidationDate(new \DateTime())
                            ->setLatestCGUVersionValidated($CGURepo->findOneBy([],['id'=>'DESC']));
        
                    $manager->flush();
        
                    return $this->redirectToRoute("index");
                }
        
                return $this->render("cdv/partialView/_CGUValidation.html.twig", [
                    'form' => $form->createView()
                ]);
            } else {
                return $this->render("cdv/empty.html.twig");
            }
        } else {
            return $this->render("cdv/empty.html.twig");
        }

    }

    /**
     * @Route("/rechercher/choix", name="searchChoice")
     */
    public function searchChoice()
    {
        return $this->render("cdv/searchChoiceType.html.twig");
    }

    /**
     * @Route("/CDV/CGU", name="CGU")
     */
    public function CGU()
    {
        return $this->render("cdv/CGU.html.twig");
    }

    /**
     * @Route("/CDV/Politiques-de-Confidentialité", name="PrivacyPolicies")
     */
    public function PrivacyPolicies()
    {
        return $this->render("cdv/privacyPolicies.html.twig");
    }

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
    public function report(User $user, UserRepository $userRepo, Request $request, EntityManagerInterface $manager)
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
