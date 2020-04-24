<?php

namespace App\Controller;

use App\Data\ResetPassword;
use App\Data\SearchData;
use Exception;
use App\Entity\User;
use App\Entity\Advert;
use App\Form\ProfileType;
use App\Form\RegistrationType;
use App\Form\AdvertCreationType;
use App\Form\ResetPasswordType;
use App\Form\SearchCityType;
use App\Form\SearchType;
use App\Repository\UserRepository;
use App\Repository\AdvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/inscription", name="registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder){
        $user = new User();
        $form=$this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $APIURL = "https://geo.api.gouv.fr/";
            $httpClient = HttpClient::create();
            $response = $httpClient->request('GET',"https://geo.api.gouv.fr/communes?codePostal=".$user->getCodePostal()."&nom=".$user->getCity()."&fields=nom&format=json");
            $response = $response->toArray();
            if($response){
                $hash = $encoder->encodePassword($user, $user->getPassword());
                $user   ->setPassword($hash)
                        ->setCity($response[0]["nom"])
                        ->setPoints(0);
                $manager->persist($user);
                $manager->flush();    
            } else {
                throw new \Exception("La ville renseignée n'a pas été trouvée");
            }
            return $this->redirectToRoute("login");
        }
        return $this->render('security/password/registration.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="login")
     */
    public function login(){
        return $this->render("security/password/login.html.twig");
    }

    /**
     * @Route("/connexion/motdepasse_oublie", name="forgotten_password")
     */
    public function forgotten_password(){

    }

    /**
     * @Route("/profil/motdepasse", name="reset_password")
     */
    public function reset_password(Request $request, UserRepository $userRepo,  UserPasswordEncoderInterface $encoder){
        $user = new ResetPassword();
        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $actualPassword = $this->getUser()->getPassword();
            //$actualPassword2 = $encoder->encodePassword($user, $user->getPassword());
            //dd();
        }
        return $this->render("security/password/reset_password.html.twig", [
            "form"=>$form->createView()
        ]);
    }

    /**
     * @Route("/deconnexion", name="logout")
     */
    public function logout(){}

    /**
     * @Route("/annonces", name="adverts")
     */
    public function adverts(AdvertRepository $advertRepo, Request $request){
        
        $search = new SearchData();

        $form= $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);
        $adverts = $advertRepo->findSearch($search);


        return $this->render("CDV/adverts.html.twig", [
            "adverts"=>$adverts,
            "form"=>$form->createView()
        ]);
    }

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
     * @Route("/mesannonces/modification/{id}", name="edit_advert")
     */
    public function creation_or_edit_my_advert(Advert $advert = null, Request $request, EntityManagerInterface $manager){
        if(!$advert){
            $advert = new Advert();
        }
        
        $form = $this->createForm(AdvertCreationType::class, $advert);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!$advert->getId()){
                $advert->setCreatedAt(new \Datetime)
                       ->setUser($this->getUser())
                       ->setCancellation(false);
            } else {
                $advert->setCreatedAt(new \Datetime);
            }
            $manager->persist($advert);
            $manager->flush();

            return $this->redirectToRoute('my_adverts');
        }

        return $this->render("security/advert_creation.html.twig",[
            "form"=>$form->createView(),
            "editing"=>$advert->getId() !== null
        ]);
    }

    /**
     * @Route("/mesannonces/suppression/{id}", name="delete_advert")
     */
    public function delete_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser()){
            if(!$advert->getDeliverer()){
                $manager->remove($advert);
                $manager->flush();
                return $this->redirectToRoute("my_adverts");
            } else {
                throw new \Exception("Vous ne pouvez pas faire cette action");
            }
        } else {
            throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
        }
    }

    /**
     * @Route("/mesannonces/annulation/{id}", name="cancel_advert")
     */
    public function cancel_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser()){
            if($advert->getDeliverer()){
                $advert->setCancellation(true);
                $manager->flush();
                return $this->redirectToRoute("my_adverts");
            } else {
                throw new \Exception("Vous ne pouvez pas faire cette action");
            }
        } else {
            throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
        }
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
                throw new \Exception('Something went wrong!'); //La personne voulait récupérer sa propre commande
            }
            $advert->setDeliverer($this->getUser());
            $manager->flush();
            return $this->redirectToRoute("my_deliveries");
        } else {
            throw new \Exception('Something went wrong!'); //Si l'utilisateur n'est pas authentifié
        }
    }

    /**
     * @Route("/meslivraisons/annulation/{id}", name="confirm_cancellation_advert")
     */
    public function confirm_cancellation_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getDeliverer()){
            $manager->remove($advert);
            $manager->flush();
            return $this->redirectToRoute("my_deliveries");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }

    /**
     * @Route("/mesannonces/reception/{id}", name="given_advert")
     */
    public function given_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser()){
            $points = $advert->getDeliverer()->getPoints();
            $points += 1;
            $advert->getDeliverer()->setPoints($points);
            $manager->remove($advert);
            $manager->flush();
            return $this->redirectToRoute("my_adverts");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }   
}
