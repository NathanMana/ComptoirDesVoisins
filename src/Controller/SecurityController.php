<?php

namespace App\Controller;

use Exception;
use App\Data\Cities;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Advert;
use App\Form\CitiesType;
use App\Form\ProfileType;
use App\Service\API\GeoApi;
use App\Data\ChangePassword;
use App\Entity\Notification;
use App\Service\MailManager;
use App\Form\RegistrationType;
use App\Data\ForgottenPassword;
use App\Form\ResetPasswordType;
use App\Form\ChangePasswordType;
use App\Repository\UserRepository;
use App\Form\ForgottenPasswordType;
use App\Repository\OfferRepository;
use App\Repository\AdvertRepository;
use App\Repository\cguRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/inscription", name="registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, RoleRepository $roleRepo, cguRepository $cguRepo){
        $user = new User();
        $form=$this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
           
        if($form->isSubmitted() && $form->isValid()){
            if($user->getCodeCity()){
                $apiGeo = new GeoApi();
                $response = $apiGeo->RequestApi("code", $user->getCodeCity())->toArray();

                if($response && $response[0]['nom']." (".$response[0]['codeDepartement'].")" === $user->getCity()){

                    $role = $roleRepo->findOneBy(['id'=>1]);//Role : ROLE_USER
                    
                    $hash = $encoder->encodePassword($user, $user->getPassword());
                    $user   ->setPassword($hash)
                            ->setPoints(0)
                            ->setUpdatedAt(new \DateTime)
                            ->setLastLogin($user->getUpdatedAt())
                            ->setLatestVersionCGUValidationDate($user->getUpdatedAt())
                            ->setLatestCGUVersionValidated($cguRepo->findOneBy([],['id'=>'DESC']))
                            ->addRole($role);
                            
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
        return $this->render('cdv/security/registration.html.twig', [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils){

        $error= $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render("cdv/security/login.html.twig", [
            'error' => $error,
            '$lastUsername'=>$lastUsername
        ]);
    }

    /**
     * @Route("/connexion/motdepasse/oublie", name="forgottenPassword")
     */
    public function forgottenPassword(Request $request, UserRepository $UserRepo, \Swift_Mailer $mailer, EntityManagerInterface $manager){

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

                $mailer = new MailManager($email, $token, $mailer);
                $mailer->passwordRecuperation();

                return $this->redirectToRoute("emailSent");
            } else {
                throw new \Exception("Cette adresse mail n'existe pas dans notre base de données");
            }
        }
        return $this->render("cdv/security/forgottenPassword.html.twig", [
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/connexion/motdepasse/reinitialisation/{token}", name="resetPassword")
     */
    public function resetPassword($token, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, UserRepository $userRepo){
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
                return $this->render("cdv/security/resetPassword.html.twig", [
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
     * @Route("/profil/motdepasse", name="changePassword")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager){
        $resetPassword = new ChangePassword();
        $form = $this->createForm(ChangePasswordType::class, $resetPassword);
        $form->handleRequest($request);

        $user = $this->getUser();

        if($form->isSubmitted() && $form->isValid()){

            $actualPassword_to_hash = $resetPassword->getPassword();
            //On vérifie si le premier mot de passe correspond à celui qui est dans la BDD
            if($encoder->isPasswordValid($user, $actualPassword_to_hash)){
                $newEncodedPassword = $encoder->encodePassword($user, $resetPassword->getNewPassword());

                $user->setPassword($newEncodedPassword);
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute("profile");
            }
        }
        return $this->render("cdv/security/changePassword.html.twig", [
            "form"=>$form->createView()
        ]);
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
        } else {
            $form=$this->createForm(ProfileType::class, $user);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $apiGeo = new GeoApi();
                $response = $apiGeo->RequestApi("code", $user->getCodeCity())->toArray();
    
                if($response && $response[0]['nom']." (".$response[0]['codeDepartement'].")" === $user->getCity())
                {
                    $manager->persist($user);
                    $manager->flush();
                }
            }     
            
            return $this->render("cdv/security/profile.html.twig", [
                'form'=>$form->createView()
            ]);
        }

    }

    /**
     * @Route("/profil/supprimer/{id}",name="deleteAccount")
     */
    public function deleteAccount(User $user, EntityManagerInterface $manager, Request $request){
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
     * @Route("/profil/notification/supprimer/{id}",name="deleteNotification")
     */
    public function deleteNotification(Notification $notification, EntityManagerInterface $manager){
        if($notification->getUser() === $this->getUser()){
            $manager->remove($notification);
            $manager->flush();
            return $this->redirectToRoute("notifications");
        } else {
            throw $this->createNotFoundException('Cette page n\'existe pas');
        }
    }
}
