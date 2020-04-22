<?php

namespace App\Controller;

use App\Entity\Advert;
use App\Entity\User;
use App\Form\AdvertCreationType;
use App\Form\ProfileType;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/security", name="security")
     */
    public function index()
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    /**
     * @Route("/inscription", name="registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder){
        $user = new User();
        $form=$this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute("login");
        }
        return $this->render('security/registration.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="login")
     */
    public function login(){
        return $this->render("security/login.html.twig");
    }

    /**
     * @Route("/deconnexion", name="logout")
     */
    public function logout(){}

    /**
     * @Route("/profil", name="profile")
     */
    public function profile(Request $request, EntityManagerInterface $manager){
        $user = $this->getUser();

        if(!$user){
            throw new \Exception('Something went wrong!');
        }

        $form=$this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($user);
            $manager->flush();
        }     
        
        return $this->render("security/profile.html.twig", [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/mesannonces/creation", name="create_my_advert")
     */
    public function create_my_advert(Request $request, EntityManagerInterface $manager){
        $advert = new Advert();
        
        $form = $this->createForm(AdvertCreationType::class, $advert);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $advert->setCreatedAt(new \Datetime)
                   ->setUser($this->getUser());
            $manager->persist($advert);
            $manager->flush();

            return $this->redirectToRoute('my_adverts');
        }

        return $this->render("security/advert_creation.html.twig",[
            "form"=>$form->createView()
        ]);
    }

    /**
     * @Route("/annonce/{id}", name="advert_information")
     */
    public function advert_information(Advert $advert){
        return $this->render("security/advert_information_layout.html.twig", [
            "advert"=>$advert
        ]);
    }

    /**
     * @Route("/annonce/{id}/livraison", name="delivery")
     */
    public function delivery(Advert $advert, EntityManagerInterface $manager){
        $currentUser = $this->getUser();
        if($currentUser){
            if($advert->getUser() === $currentUser){
                throw new \Exception('Something went wrong!');
            }
            $advert->setDeliverer($this->getUser());
            $manager->flush();
            return $this->redirectToRoute("my_deliveries");
        } else {
            throw new \Exception('Something went wrong!');
        }
    }
}
