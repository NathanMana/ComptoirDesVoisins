<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Entity\Advert;
use App\Data\SearchData;
use App\Form\SearchType;
use App\Form\ProfileType;
use App\Data\ChangePassword;
use App\Form\RegistrationType;
use App\Data\ForgottenPassword;
use App\Entity\Notification;
use App\Form\AdvertCreationType;
use App\Form\ChangePasswordType;
use App\Repository\UserRepository;
use App\Form\ForgottenPasswordType;
use App\Form\ResetPasswordType;
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
            if($user->getCodeCity()){
                $APIURL = "https://geo.api.gouv.fr/";
                $httpClient = HttpClient::create();
                $response = $httpClient->request('GET',"https://geo.api.gouv.fr/communes?code=".$user->getCodeCity()."&format=json");
                $response = $response->toArray();

                if($response[0]['nom']." (".$response[0]['codeDepartement'].")" === $user->getCity()){
                    $hash = $encoder->encodePassword($user, $user->getPassword());
                    $user   ->setPassword($hash)
                            ->setCity($response[0]["nom"] . ' ('.$response[0]["codeDepartement"].')')
                            ->setPoints(0)
                            ->setUpdatedAt(new \DateTime);
                    $manager->persist($user);
                    $manager->flush();    
                } else {
                    throw new \Exception("Une erreur est intervenue, l'inscription n'a pas été prise en compte");
                }
            } else {
                throw new \Exception("Veuillez sélectionner une ville valide");
            }
           
            return $this->redirectToRoute("login");
        }
        return $this->render('cdv/account/registration.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="login")
     */
    public function login(){
        return $this->render("cdv/account/login.html.twig");
    }

    /**
     * @Route("/connexion/motdepasse/oublie", name="forgotten_password")
     */
    public function forgotten_password(Request $request, UserRepository $UserRepo, \Swift_Mailer $mailer, EntityManagerInterface $manager){

        $forgottenPassword = new ForgottenPassword();
        $form = $this->createForm(ForgottenPasswordType::class, $forgottenPassword);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $email = $forgottenPassword->getEmail();
            $user = $UserRepo->findOneBy(['email'=>$email]);

            if(!empty($email)){
                $token = uniqid("",true);
                $user->setResetPassword($token);
                $manager->persist($user);
                $manager->flush();

                $message = (new \Swift_Message('Récupération du mot de passe'))
                ->setFrom('nat.manar@gmail.com')
                ->setTo($email)
                ->setBody(
                    "Suivez ce lien pour réinitialiser votre mot de passe : ". " https://localhost:8000/connexion/motdepasse/reinitialisation/".$token
                );
                $mailer->send($message);

                return $this->redirectToRoute("email_sent");
            } else {
                throw new \Exception("Cette adresse mail n'existe pas dans notre base de données");
            }
        }
        return $this->render("cdv/account/forgotten_password.html.twig", [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/connexion/motdepasse/reinitialisation/{token}", name="reset_password")
     */
    public function reset_password($token, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, UserRepository $userRepo){
        if($token !== null){
            $user = $userRepo->findOneBy(["resetPassword"=>$token]);
            if(!empty($user)){
                $form = $this->createForm(ResetPasswordType::class);
                $form->handleRequest($request);
                if($form->isSubmitted() && $form->isValid()){
                    $encodedPassword = $encoder->encodePassword($user,  $form->getData()->getPassword());
                    $user->setPassword($encodedPassword);
                    $user->setResetPassword(null);
                    $manager->persist($user);
                    $manager->flush();

                    return $this->redirectToRoute("login");
                }
                return $this->render("cdv/account/reset_password.html.twig", [
                    "form"=>$form->createView()
                ]);
            } else {
                throw $this->createNotFoundException('Cette page n\'existe pas');
            }
        } else {
            throw $this->createNotFoundException('Cette page n\'existe pas');
        }
    }

    /**
     * @Route("/profil/motdepasse", name="change_password")
     */
    public function change_password(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager){
        $reset_password = new ChangePassword();
        $form = $this->createForm(ChangePasswordType::class, $reset_password);
        $form->handleRequest($request);

        $user = $this->getUser();

        if($form->isSubmitted() && $form->isValid()){

            $actualPassword_to_hash = $reset_password->getPassword();
            //On vérifie si le premier mot de passe correspond à celui qui est dans la BDD
            if($encoder->isPasswordValid($user, $actualPassword_to_hash)){
                $newEncodedPassword = $encoder->encodePassword($user, $reset_password->getNewPassword());

                $user->setPassword($newEncodedPassword);
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute("profile");
            }
        }
        return $this->render("cdv/account/change_password.html.twig", [
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

        if($form->isSubmitted() && $form->isValid()){
            if(substr($search->q,-1) !== ")"){
                $APIURL = "https://geo.api.gouv.fr/";
                $httpClient = HttpClient::create();
                $response = $httpClient->request('GET',"https://geo.api.gouv.fr/communes?nom=".$search->q."&format=json");
                $response = $response->toArray();
    
                if(count($response) > 1){
                    return $this->render("cdv/adverts/adverts.html.twig", [
                        "adverts"=>$adverts,
                        "form"=>$form->createView(),
                        "propositionsVille"=>$response
                    ]);
                }
            }
        } 

        return $this->render("cdv/adverts/adverts.html.twig", [
            "adverts"=>$adverts,
            "form"=>$form->createView(),
            "propositionsVille"=>null
        ]);
    }

    /**
     * @Route("/profil", name="profile")
     */
    public function profile(Request $request, EntityManagerInterface $manager){
        $user = $this->getUser();

        if(!$user){
            throw new \Exception('Something went wrong!');
        } else {
            $form=$this->createForm(ProfileType::class, $user);
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid()){
                $APIURL = "https://geo.api.gouv.fr/";
                $httpClient = HttpClient::create();
                $response = $httpClient->request('GET',"https://geo.api.gouv.fr/communes?code=".$user->getCodeCity()."&format=json");
                $response = $response->toArray();
    
                if($response[0]['nom']." (".$response[0]['codeDepartement'].")" === $user->getCity()){
                    $manager->persist($user);
                    $manager->flush();
                }
            }     
            
            return $this->render("cdv/account/profile.html.twig", [
                'form'=>$form->createView()
            ]);
        }

    }

    /**
     * @Route("/profil/notification", name="notifications")
     */
    public function notifications(){
        $notifications = $this->getUser()->getNotifications();
        return $this->render("cdv/account/notifications.html.twig", [
            'notifications'=>$notifications
        ]);
    }

    /**
     * @Route("/mesannonces/creation/sefairelivrer", name="create_my_advert")
     * @Route("/mesannonces/modification/sefairelivrer/{id}", name="edit_advert")
     */
    public function creation_or_edit_my_advert(Advert $advert = null, Request $request, EntityManagerInterface $manager){
        if(!$advert){
            $advert = new Advert();
            $checkUser = true;
            $advert ->setCity($this->getUser()->getCity())
                    ->setCodeCity($this->getUser()->getCodeCity()); //On bind d'entrée la ville de l'annonce avec la ville de l'utilisateur  
        } else {
            if($advert->getUser() !== $this->getUser()){    //Si l'utilisateur qui veut modifier l'annonce n'est pas le proprio de l'annonce
                $checkUser = false;
                throw $this->createNotFoundException('Cette annonce n\'existe pas');
            }  else {
                $checkUser = true;
            }
        }
        
        if($checkUser){
            $form = $this->createForm(AdvertCreationType::class, $advert);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $APIURL = "https://geo.api.gouv.fr/";
                $httpClient = HttpClient::create();
                $response = $httpClient->request('GET',"https://geo.api.gouv.fr/communes?code=".$advert->getCodeCity()."&format=json");
                $response = $response->toArray();

                if($response[0]['nom']." (".$response[0]['codeDepartement'].")" === $advert->getCity()){ //On vérifie si le front et le bac ont les mêmes infos
                    if(!$advert->getId()){                      //Si on créer l'annonce
                        $advert->setCreatedAt(new \Datetime)
                            ->setUser($this->getUser())
                            ->setCancellation(false);
                    } else {            //Si on modifie l'annonce
                        $advert->setCreatedAt(new \Datetime);
                    }
                } else {
                    throw new \Exception("Veuillez sélectionner une ville valide");
                }

                $manager->persist($advert);
                $manager->flush();
    
                return $this->redirectToRoute('my_adverts');
            }
        }

        return $this->render("cdv/adverts/advert_creation.html.twig",[
            "form"=>$form->createView(),
            "editing"=>$advert->getId() !== null
        ]);
    }

    /**
     * @Route("/mesannonces/suppression/{id}", name="delete_advert")
     */
    public function delete_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser() && !$advert->getDeliverer()){
            $manager->remove($advert);
            $manager->flush();
            return $this->redirectToRoute("my_adverts");
        } else {
            throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
        }
    }

    /**
     * @Route("/mesannonces/annulation/{id}", name="cancel_advert")
     */
    public function cancel_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getUser() && $advert->getDeliverer()){
            $advert->setCancellation(true);

            $notification = new Notification();
            $notification->setObject($advert->getUser()->getName(). " veut annuler ça demande")
                        ->setMessage($advert->getUser()->getName(). " veut annuler cet échange, contactez le si vous n'étiez pas au courant.")
                        ->setSeen(false)
                        ->setUser($advert->getDeliverer())
                        ->setCreatedAt(new \DateTime());

            $manager->persist($advert);
            $manager->persist($notification);

            $manager->flush();
            return $this->redirectToRoute("my_adverts");
        } else {
            throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
        }
    }

    /**
     * @Route("/annonce/{id}", name="advert_information")
     */
    public function advert_information(Advert $advert){
        if($advert->getDeliverer() !== null){
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        } else {
            return $this->render("cdv/adverts/advert_information_layout.html.twig", [
                "advert"=>$advert
            ]);
        }
    }

    /**
     * @Route("/annonce/{id}/livraison", name="delivery")
     */
    public function delivery(Advert $advert, EntityManagerInterface $manager){
        if($advert->getDeliverer() === null && $advert->getUser() !== $this->getUser()){
            $advert->setDeliverer($this->getUser());
    
            $notification = new Notification();
            $notification->setObject("Votre annonce a trouvé preneur !")
                        ->setMessage("Votre annonce a été prise en charge par ". $this->getUser()->getName() . ". Elle rentrera bientôt en contact avec vous !")
                        ->setSeen(false)
                        ->setUser($advert->getUser())
                        ->setCreatedAt(new \DateTime());
    
            $manager->persist($advert);
            $manager->persist($notification);
    
            $manager->flush();
            return $this->redirectToRoute("my_deliveries");
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    }

    /**
     * @Route("/meslivraisons/annulation/{id}", name="confirm_cancellation_advert")
     */
    public function confirm_cancellation_advert(Advert $advert, EntityManagerInterface $manager){
        if($this->getUser() === $advert->getDeliverer()){
            $notification = new Notification();
            $notification->setObject("Suppression de l'annonce")
                        ->setMessage("Votre contact a confirmé l'annulation de l'annonce. Elle est donc supprimée")
                        ->setSeen(false)
                        ->setUser($advert->getUser())
                        ->setCreatedAt(new \DateTime());

            $manager->persist($notification);
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

    /**
     * @Route("/profil/supprimer/{id}",name="delete_account")
     */
    public function delete_account(User $user, EntityManagerInterface $manager, Request $request){
        if($user === $this->getUser()){
            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
            $manager->remove($user);
            $manager->flush();
            return $this->redirectToRoute("index");
        } else {
            throw $this->createNotFoundException('Cette page n\'existe pas');
        }
    }

    /**
     * @Route("/profil/notification/supprimer/{id}",name="delete_notification")
     */
    public function delete_notification(Notification $notification, EntityManagerInterface $manager, Request $request){
        if($notification->getUser() === $this->getUser()){
            $manager->remove($notification);
            $manager->flush();
            return $this->redirectToRoute("notifications");
        } else {
            throw $this->createNotFoundException('Cette page n\'existe pas');
        }
    }

    
}
