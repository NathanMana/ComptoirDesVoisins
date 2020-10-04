<?php

namespace App\Controller;

use App\ViewModel\CGUFormData;
use App\Repository\cguRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(AuthenticationUtils $authenticationUtils, cguRepository $CGURepo)
    {
        $CGUToValidate = false;
        if($this->getUser()){
            $error = null;
            $lastUsername = null;
            if($this->getUser()->getLatestCGUVersionValidated() != $CGURepo->findOneBy([],['id'=>'DESC']))
            {
                $CGUToValidate = true;
            }
        } else {
            $error= $authenticationUtils->getLastAuthenticationError();
            $lastUsername = $authenticationUtils->getLastUsername();
        }

        return $this->render('cdv/home/index.html.twig', [
            'CGUToValidate'=> $CGUToValidate,
            'error' => $error,
            '$lastUsername'=>$lastUsername
        ]);
    }

    /**
     * @Route("/CDV/CGU", name="CGU")
     */
    public function CGU()
    {
        return $this->render("cdv/home/CGU.html.twig");
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
        
                    return $this->redirectToRoute("index"); //Faire un return JSON 
                }
        
                return $this->render("cdv/home/_CGUValidation.html.twig", [
                    'form' => $form->createView()
                ]);
            } else {
                return $this->render("cdv/home/empty.html.twig");
            }
        } else {
            return $this->render("cdv/home/empty.html.twig");
        }
    }
    

    /**
     * @Route("/CDV/Politiques-de-Confidentialité", name="PrivacyPolicies")
     */
    public function PrivacyPolicies()
    {
        return $this->render("cdv/home/privacyPolicies.html.twig");
    }
}
