<?php

namespace App\Controller;

use Exception;
use App\Entity\City;
use App\Entity\User;
use App\Entity\Report;
use App\Form\ProfileType;
use App\Service\API\GeoApi;
use App\Entity\Notification;
use App\Service\MailManager;
use App\Form\RegistrationType;
use App\Form\ResetPasswordType;
use App\Form\ChangePasswordType;
use App\Repository\cguRepository;
use App\ViewModel\ChangePassword;
use App\Repository\CityRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Form\ForgottenPasswordType;
use App\ViewModel\ForgottenPassword;
use Doctrine\ORM\EntityManagerInterface;
use App\ViewModel\Security\ProfileViewModel;
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
    public function registration(Request $request, CityRepository $cityRepository,EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, RoleRepository $roleRepo, cguRepository $cguRepo){
       
        $user = new User();
        
        $form=$this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
           
        if($form->isSubmitted() && $form->isValid()){
            
            if($user->getCodeCity()){ //Si on a renseigné une ville
                $apiGeo = new GeoApi();
                $response = $apiGeo->RequestApi("code", $user->getCodeCity())->toArray();

                $responseName = $response[0]['nom']." (".$response[0]['codeDepartement'].")";

                if($response && $responseName === $user->getCityName()){
                   
                    $city = $apiGeo->setCity($response, $cityRepository, $manager);
                    $user->setCity($city);
                  
                } else {
                    $user->setCity(null);
                }
            } else {
                $user->setCity(null);
            }

            $role = $roleRepo->findOneBy(['id'=>1]);//Role : ROLE_USER
            
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user   ->setPassword($hash)
                    ->setPoints(0)
                    ->setUpdatedAt(new \DateTime)
                    ->setLastLogin(new \DateTime)
                    ->setLatestVersionCGUValidationDate(new \DateTime)
                    ->setLatestCGUVersionValidated($cguRepo->findOneBy([],['id'=>'DESC']))
                    ->addRole($role);
                    
            $manager->persist($user);
            $manager->flush();    
           
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

                $mailer = new MailManager($email, $mailer);
                $mailer->passwordRecuperation($token);

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

    /**
     * @Route("/connexion/motdepasse/envoi", name="emailSent")
     */
    public function emailSent(){
        return $this->render("cdv/account/emailSent.html.twig");
    }
}
