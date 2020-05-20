<?php

namespace App\Controller;

use App\Form\CGUForm;
use App\Data\CGUFormData;
use App\Repository\cguRepository;
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
                return $this->createNotFoundException();
            }
        } else {
            return $this->createNotFoundException();
        }

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

    
}
