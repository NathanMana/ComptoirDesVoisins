<?php

namespace App\Controller;

use DateTime;
use Exception;
use DateTimeZone;
use App\Entity\Help;
use App\Entity\User;
use App\Entity\Offer;
use NotificationViewModel;
use App\Service\API\GeoApi;
use App\Entity\Notification;
use App\Service\MailManager;
use App\Repository\CityRepository;
use App\Repository\HelpRepository;
use App\Repository\OfferRepository;
use App\ViewModel\CounterViewModel;
use App\Service\NotificationManager;
use App\Service\Coding\CounterFunction;
use App\ViewModel\JsonListNotification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use App\Service\Coding\HistoricalFunction;
use App\ViewModel\Security\ProfileViewModel;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountController extends AbstractController
{

    /**
     * @Route("/profil/notification", name="notifications")
     */
    public function notifications(){
        $notifications = $this->getUser()->getNotifications();
        return $this->render("cdv/account/profile/notifications.html.twig", [
            'notifications'=>$notifications
        ]);
    }

    /**
     * @Route("/profil", name="profile")
     */
    public function profile(Request $request, EntityManagerInterface $manager, CityRepository $cityRepository){
        $user = $this->getUser();
        
        if(!$user){
            throw new \Exception('Something went wrong!');
        } else {

            $profileVM = new ProfileViewModel();
            $profileVM  ->setName($user->getName())
                        ->setLastname($user->getLastname())
                        ->setImageFile($user->getImageFile())
                        ->setPhone($user->getPhone());
            
            if($user->getCity()){
                $userCity = $user->getCity()->getName();
                $userCodeCity = $user->getCity()->getCode();
                $profileVM  ->setCity($userCity)
                            ->setCodeCity($userCodeCity);
            }

            $form=$this->createForm(ProfileType::class, $profileVM );
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                $apiGeo = new GeoApi();
                $response = $apiGeo->RequestApi("code", $profileVM->getCodeCity())->toArray();

                $responseName = $response[0]['nom']." (".$response[0]['codeDepartement'].")";

                if($response && $responseName === $profileVM->getCity())
                {
                    $city = $apiGeo->setCity($response, $cityRepository, $manager);

                    $user   ->setName($profileVM->getName())
                            ->setLastname($profileVM->getLastname())
                            ->setImageFile($profileVM->getImageFile())
                            ->setPhone($profileVM->getPhone())
                            ->setCity($city);

                }
    
                $manager->flush();
            }     
            
            return $this->render("cdv/account/profile/profile.html.twig", [
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

    /**
     * @Route("/comptoir", name="myCounter")
     */
    public function myCounter(HelpRepository $HelpRepository, OfferRepository $offerRepository, TranslatorInterface $translator)
    {
        $user = $this->getUser();

        $myHelpsWithDeliverer = $HelpRepository->findHelpsWithDeliverer($user); //getMyHelps()->Where(id.user => $this->getUser) : Recherche toutes mes annonces qui ont un livreur
        $myDeliveriesForHelp = $HelpRepository->getListDelivererHelps($user); //Recherche toutes mes livraisons pour des gens qui ont demandés
        $myOffersWithClient = $offerRepository->findOffersWithClientButNotDead($user); //Recherches toutes mes offres qui ont des clients
        $ClientOfOffer = $user->getClientOffers();

        $counterFunction = new CounterFunction();
        $rdata = $counterFunction->main($myHelpsWithDeliverer, $myDeliveriesForHelp, $myOffersWithClient, $ClientOfOffer, $translator);

        //Récupérer mes annonces sans livreur
        $myHelpsWithoutDeliverer = $HelpRepository->findMyHelpsWithoutDeliverer($user);
        //Récupérer mes offres qui ne sont pas remplie au max
        $myOffersWithPlace = $offerRepository->findMyOffersWithPlace($user);
        
        return $this->render("cdv/account/counter/index.html.twig", [
            "rdata"=> $rdata,                    
            "myHelpsWithoutDeliverer"=>$myHelpsWithoutDeliverer,    //Mes annonces sans livreur
            "myOffersWithPlace"=>$myOffersWithPlace                     //Mes courses pas remplies au max
        ]);
    }

    /**
     * @Route("/comptoir/historique", name="myHistorical")
     */
    public function Historical(HelpRepository $HelpRepository, OfferRepository $offerRepository)
    {
        $HelpHistorical = $HelpRepository->findHistorical($this->getUser());
        $offerHistorical = $offerRepository->findHistorical($this->getUser());

        $historicalVM = new HistoricalFunction();
        $historicalVM->main($HelpHistorical, $offerHistorical, $this->getUser());

        return $this->render("cdv/account/counter/myHistorical.html.twig", [
            'model' => $historicalVM
        ]);
    }

    /**
     * @Route("/comptoir/activites", name="comingActivities")
     */
    public function comingActivities(HelpRepository $HelpRepository, OfferRepository $offerRepository, TranslatorInterface $translator){

        $user = $this->getUser();

        $myHelpsWithDeliverer = $HelpRepository->findHelpsWithDeliverer($user); //getMyHelps()->Where(id.user => $this->getUser) : Recherche toutes mes annonces qui ont un livreur
        $myDeliveriesForHelp = $user->getMyDeliveries(); //Recherche toutes mes livraisons pour des gens qui ont demandés
        $myOffersWithClient = $offerRepository->findOffersWithClientButNotDead($user); //Recherches toutes mes offres qui ont des clients
        $ClientOfOffer = $user->getClientOffers(); //Recherche toutes les offres où je suis le client

        $counterFunction = new CounterFunction();
        $rdata = $counterFunction->main($myHelpsWithDeliverer, $myDeliveriesForHelp, $myOffersWithClient, $ClientOfOffer, $translator);

        return $this->render("cdv/account/counter/comingActivities.html.twig", [
            "model" => $rdata
        ]);
    }

    /**
     * @Route("/comptoir/propositions", name="myOffers")
     */
    public function myOffers(OfferRepository $offerRepository){

        $user = $this->getUser();
        $offers = $offerRepository->findOnlineOffersForUser($user);

        return $this->render("cdv/account/counter/offer/myOffers.html.twig", [
            "model" => $offers
        ]);
    }

    /**
     * @Route("/comptoir/propositions/{id}", name="myOffer")
     */
    public function myOffer(Offer $offer){
        return $this->render("cdv/account/counter/myOffer.html.twig", [
            "offer" => $offer
        ]);
    }

    /**
     * @Route("/comptoir/propositions/supprimer/{id}", name="deleteOffer")
     */
    public function delete_offer(Offer $offer, EntityManagerInterface $manager, \Swift_Mailer $mailer){

        if($this->getUser() === $offer->getUser()){
            if(!empty($offer->getClients())){

                $notification = new Notification();

                $clients = $offer->getClients();
                foreach($clients as $client){

                    $notification   ->setObject("Suppression de l'annonce")
                                    ->setMessage($this->getUser()->getName()." a supprimé l'annonce à laquelle vous vous étiez rattaché")
                                    ->setSeen(false)
                                    ->setUser($client)
                                    ->setCreatedAt(new \DateTime());

                    if($client->getMailAuthorization()){
                        $mailManager = new MailManager($client->getEmail(),$mailer);
                        $mailManager->notifIntoMail($notification);
                    }
                                    
                    $manager->persist($notification);
                }
            }

            $manager->remove($offer);
            $manager->flush();

            return $this->redirectToRoute("myCounter");

        } else if(in_array($this->getUser(), $offer->getClients()->toArray())){     //Si un client décide de supprimer l'offre, il la supprime seulement pour lui
            
            $offer->removeClient($this->getUser());
            $offer->setAvailable($offer->getAvailable() - 1);
            $manager->persist($offer);
            $manager->flush();

            return $this->redirectToRoute("myCounter");
        }
        else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }
    } 

    /**
     * @Route("/comptoir/demandes", name="myHelps")
     */
    public function myHelps(HelpRepository $HelpRepository){

        $user = $this->getUser();
        $offers = $HelpRepository->findOnlineHelpsForUser($user);

        return $this->render("cdv/account/counter/help/myHelps.html.twig", [
            "model" => $offers
        ]);
    }

    /**
     * @Route("/comptoir/demandes/{id}", name="myHelp")
     */
    public function myHelp(Help $Help)
    {
        if($this->getUser() === $Help->getUser()){
            return $this->render("cdv/account/counter/help/informationForCreator.html.twig", [
                'Help'=>$Help
            ]);
        } else {
            throw $this->createNotFoundException('Cette demande n\'existe pas');
        }
    }

    /**
     * @Route("/comptoir/demandes/suppression/{id}", name="deleteHelp")
     */
    public function deleteHelp(Help $Help, EntityManagerInterface $manager){
        if($this->getUser() === $Help->getUser() && !$Help->getDeliverer()){
            $manager->remove($Help);
            $manager->flush();
            return $this->redirectToRoute("myCounter");
        } else {
            throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
        }
    }

    //  /**
    //  * @Route("/meslivraisons/demandes/{id}", name="informationForDelivererHelp")
    //  */
    // public function informationForDeliverer(Help $Help)
    // {
    //     if($this->getUser() === $Help->getDeliverer()){
    //         return $this->render("dev/helps/informationForDeliverer.html.twig", [
    //             'Help'=>$Help
    //         ]);
    //     } else {
    //         throw $this->createNotFoundException('Cette demande n\'existe pas');
    //     }
    // }

    // /**
    //  * @Route("/demandes/{id}/livraison", name="delivery")
    //  */
    // public function delivery(Help $Help, EntityManagerInterface $manager, \Swift_Mailer $mailer){
    //     if($Help->getDeliverer() === null && $Help->getUser() !== $this->getUser()){
    //         $Help->setDeliverer($this->getUser());

    //         $notification = new NotificationManager();
    //         $notification = $notification->delivererHelp($Help, $this->getUser());

    //         if($Help->getUser()->getMailAuthorization()){
    //             $mailManager = new MailManager($Help->getUser()->getEmail(),$mailer);
    //             $mailManager->notifIntoMail($notification);
    //         }

    //         $manager->persist($Help);
    //         $manager->persist($notification);
    
    //         $manager->flush();
    //         return $this->redirectToRoute("informationForDelivererHelp", [
    //             'id' => $Help->getId()
    //         ]);
    //     } if($Help->getUser() === $this->getUser()){
    //         throw $this->createNotFoundException('Vous ne pouvez pas vous livrer !');
    //     } else {
    //         throw $this->createNotFoundException('Cette annonce n\'existe pas');
    //     }
    // }

    // /**
    //  * @Route("/mesdemandes/annulation/{id}", name="confirmCancellationHelp")
    //  */
    // public function confirmCancellationHelp(Help $Help, EntityManagerInterface $manager, \Swift_Mailer $mailer){
    //     if($this->getUser() === $Help->getDeliverer()){

    //         $notification = new NotificationManager();
    //         $notification = $notification->HelpDeleteConfirmation($Help, $this->getUser());

    //         if($Help->getUser()->getMailAuthorization()){
    //             $mailManager = new MailManager($Help->getUser()->getEmail(),$mailer);
    //             $mailManager->notifIntoMail($notification);
    //         }
           
    //         $manager->persist($notification);
    //         $manager->remove($Help);
    //         $manager->flush();
    //         return $this->redirectToRoute("myCounter");
    //     } else {
    //         throw $this->createNotFoundException('Cette annonce n\'existe pas');
    //     }
    // }

    // /**
    //  * @Route("/mesdemandes/reception/{id}", name="givenHelp")
    //  */
    // public function givenHelp(Help $Help, EntityManagerInterface $manager){
    //     if($this->getUser() === $Help->getUser()){
    //         $points = $Help->getDeliverer()->getPoints();
    //         $points += 1;
    //         $Help->getDeliverer()->setPoints($points);
    //         $Help->setIsDelivered(true);
    //         $manager->flush();
    //         return $this->redirectToRoute("myCounter");
    //     } else {
    //         throw $this->createNotFoundException('Cette annonce n\'existe pas');
    //     }
    // }   

    // /**
    //  * @Route("/comptoir/activites/demande/{id}", name="HelpInformationCreator")
    //  */
    // public function HelpInformationCreator(Help $Help){
    //     return $this->render("cdv/account/counter/HelpInformationCreator.html.twig", [
    //         "Help" => $Help
    //     ]);
    // }

    // /**
    //  * @Route("/comptoir/demande/supprimer/{id}", name="HelpDeletion")
    //  */
    // public function HelpDeletion(Help $Help, EntityManagerInterface $manager){
    //     if($this->getUser() === $Help->getUser()){
    //         $manager->remove($Help);
    //         $manager->flush();
            
    //         return $this->redirectToRoute("myCounter");
    //     } else {
    //         throw new \Exception("Vous n'avez pas les droits de modification pour cet article !");
    //     }
    // }

    /**
     * @Route("/annonces", name="choiceCreation")
     */
    public function choiceCreation(){
        return $this->render("cdv/account/creation/choiceCreation.html.twig");
    }

    /**
     * @Route("/annonces/demande/creer", name="createHelp")
     * @Route("/annonces/demande/modifier/{id}", name="editHelp")
     */
    public function creationOrEditMyHelp(Help $Help = null, Request $request, EntityManagerInterface $manager, CityRepository $cityRepository){
        if(!$Help){
            $Help = new Help();
            $checkUser = true;
            $Help ->setCityName($this->getUser()->getCity()->getName())
                    ->setCodeCity($this->getUser()->getCity()->getCode())
                    ->setDateHelp(new DateTime()); //On bind d'entrée la ville de l'annonce avec la ville de l'utilisateur  
        } else {
            if($Help->getUser() !== $this->getUser()){    //Si l'utilisateur qui veut modifier l'annonce n'est pas le proprio de l'annonce
                $checkUser = false;
                throw $this->createNotFoundException('Cette annonce n\'existe pas');
            }  else {
                $checkUser = true;
            }
        }
        
        if($checkUser){
            $form = $this->createForm(HelpCreationType::class, $Help);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $todayWithHours = new DateTime();
                $today = $todayWithHours->format("Y-m-d");
                $dateOffer = $Help->getDateHelp()->format("Y-m-d");

                if($dateOffer >= $today){
                    $apiGeo = new GeoApi();
                    $response = $apiGeo->RequestApi("code", $Help->getCodeCity())->toArray();
                    
                    $responseName = $response[0]['nom']." (".$response[0]['codeDepartement'].")";

                    if($response && $responseName === $Help->getCityName()) {

                        $city = $apiGeo->setCity($response, $cityRepository, $manager);

                        $deadline = new DateTime($Help->getDateHelp()->format("yy-m-d"), new DateTimeZone($Help->getTimezone()));
                        $deadline->setTimezone(new \DateTimeZone('UTC'));

                        $Help ->setCity($city)
                                ->setCreatedAt(new \Datetime)
                                ->setDateHelp($deadline);
    
                        if(!$Help->getId()){  //Si on créer l'annonce
                            $Help ->setUser($this->getUser())
                                    ->setIsCancel(false)
                                    ->setIsDelivered(false);
    
                        }
    
                    } else {
                        throw new Exception('Veuillez entrer une ville valide');
                    }
                    
                    $manager->persist($Help);
                    $manager->flush();
    
                    return $this->redirectToRoute('myCounter');
                } else {
                    throw new Exception('Indiquez une date limite valide');
                }
            }
    
            return $this->render("cdv/account/creation/helpCreation.html.twig",[
                "form"=>$form->createView(),
                "editing"=>$Help->getId() !== null
            ]);
        } else {
            throw $this->createNotFoundException();
        }

    }  

    /**
     * @Route("/annonces/creation/proposition", name="offerCreation")
     */
    public function offerCreation(Request $request, EntityManagerInterface $manager, CityRepository $cityRepository)
    {
        $offer = new Offer();
        $offer->setDateDelivery(new DateTime());

        $form = $this->createForm(OfferCreationType::class, $offer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $todayWithHours = new DateTime();
            $today = $todayWithHours->format("Y-m-d");
            $dateOffer = $offer->getDateDelivery()->format("Y-m-d");
            
            if($dateOffer >= $today){
                $apiGeo = new GeoApi();

                $response = $apiGeo->RequestApi("code", $offer->getCodeCities())->toArray();  
                $responseName = $response[0]['nom']." (".$response[0]['codeDepartement'].")";
                
                if($response && $responseName === $offer->getCitiesDeliveryName()){
                    $city = $apiGeo->setCity($response, $cityRepository, $manager);

                    $datetime = new DateTime();
                    $datetime = $datetime->setTimezone(new \DateTimeZone('UTC'));

                    $offer  ->setUser($this->getUser())
                            ->setAvailable(0)
                            ->addCitiesDelivery($city)
                            ->setCreatedAt($datetime);
                } else {
                    throw new Exception("Veuillez entrer une ville valide");
                }

                $groceriesType = "";
                foreach($offer->getGroceryTypeArray() as $key => $item){
                    if($key === count($offer->getGroceryTypeArray()) - 1){
                        $groceriesType .= $item;
                    } else {
                        $groceriesType .= $item . ", ";
                    }
                    
                }    

                $offer->setGroceryType($groceriesType);

                $dateDelivery = new DateTime($offer->getDateDelivery()->format("yy-m-d"), new DateTimeZone($offer->getTimezone()));
                $dateDelivery->setTimezone(new \DateTimeZone('UTC'));
                $offer->setDateDelivery($dateDelivery);

                $manager->persist($offer);
                $manager->flush();

                return $this->redirectToRoute("myCounter");
            } else {
                throw new Exception("Veuillez rentrer une date valide");
            }

        }

        return $this->render('cdv/account/creation/offerCreation.html.twig', [
            "form"=>$form->createView(),
        ]);
    }

    /**
     * @Route("/annonces/proposition/modifier/{id}", name="editOffer")
     */
    public function editOffer(Offer $offer, Request $request, EntityManagerInterface $manager){
        if($offer->getUser() === $this->getUser()){    //Si l'utilisateur qui veut modifier l'annonce n'est pas le proprio de l'annonce
            
            $form = $this->createForm(OfferEditType::class, $offer);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                if($offer->getLimited() < $offer->getAvailable()){ //Si on modifie et que l'on baisse le nombre de personne à livrer (ex : je livre deja 2 personnes, je modifie pour livrer qu'une seule personne)
                    throw new \Exception("Vous ne pouvez pas mettre un nombre de livraison inférieur au nombre de personne que vous livrez déjà");
                }
                else {
                    if($offer->getDateDelivery() >= new DateTime('now')){
                        $offer->setCreatedAt(new \Datetime);
                        $dateDelivery = new DateTime($offer->getDateDelivery()->format("yy-m-d"), new DateTimeZone($offer->getTimezone()));
                        $dateDelivery->setTimezone(new \DateTimeZone('UTC'));
                        $offer->setDateDelivery($dateDelivery);    
    
                        $manager->persist($offer);
                        $manager->flush();  
    
                        return $this->redirectToRoute("myCounter");
                    } else {
                        throw new Exception("Veuillez rentrer une date valide");
                    }
                }
            } 
            return $this->render('cdv/account/creation/editOffer.html.twig', [
                "form"=>$form->createView(),
            ]);
        } else {
            throw $this->createNotFoundException('Cette annonce n\'existe pas');
        }   
    }

    // /**
    // * @Route("/mescourses/client/retirer/{id}/{user}", name="removeClient")
    // */
    // public function removeClient(Offer $offer, User $user, EntityManagerInterface $manager)
    // {
    //     $clients = $offer->getClients()->toArray();
    //     $userInClients = in_array($user, $clients);
    //     if($this->getUser() === $offer->getUser() && $userInClients){

    //         $el = array_search($user,$clients,true);
    //         if($el !== null){

    //             $offer->setAvailable($offer->getAvailable()-1);
    //             $user->removeClientOffer($offer);
    //             $manager->flush();

    //             return $this->redirectToRoute("informationForCreator", [
    //                 'id'=>$offer->getId()
    //             ]);
    //         } else {

    //             throw new Exception("Une erreur est intervenue"); 

    //         }

    //     } else {
    //         return $this->createNotFoundException();
    //     }
    // }

}
