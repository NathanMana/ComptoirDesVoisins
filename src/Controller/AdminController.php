<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\cgu;
use App\Entity\User;
use App\Entity\Offer;
use App\Entity\Advert;
use App\Form\ProfileType;
use App\Service\API\GeoApi;
use App\Service\UserManager;
use App\Repository\UserRepository;
use App\Repository\OfferRepository;
use App\Repository\AdvertRepository;
use App\Repository\ReportRepository;
use App\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{

    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/admin/utilisateurs", name="adminUsers")
     */
    public function adminUsers(UserRepository $userRepo)
    {
        return $this->render('admin/adminUsers.html.twig', [
            'users'=>$userRepo->findAll()
        ]);
    }

    /**
     * @Route("/admin/courses", name="adminOffers")
     */
    public function adminOffers(OfferRepository $offerRepo)
    {
        return $this->render('admin/adminOffers.html.twig', [
            'offers'=>$offerRepo->findAll()
        ]);
    }

    /**
     * @Route("/admin/demandes", name="adminAdverts")
     */
    public function adminAdverts(AdvertRepository $advertRepo)
    {
        return $this->render('admin/adminAdverts.html.twig', [
            'adverts'=>$advertRepo->findAll()
        ]);
    }

    /**
     * @Route("/admin/signalements", name="adminReports")
     */
    public function adminReports(ReportRepository $reportRepo)
    {
        return $this->render('admin/adminReports.html.twig', [
            'reports'=>$reportRepo->findAll()
        ]);
    }

    /**
     * @Route("/admin/supprimer/user/{id}", name="adminDeleteUser")
     */
    public function adminDelete(User $user, EntityManagerInterface $manager)
    {
        $manager->remove($user);
        $manager->flush();
        
        return $this->redirectToRoute("adminUsers");
    }

    /**
     * @Route("/admin/ajouter/cgu", name="adminAddCGU")
     */
    public function adminAddCGU(EntityManagerInterface $manager, UserRepository $userRepo)
    {
        $cgu = new cgu();
        $cgu->setCreatedAt(new \DateTime());
        $manager->persist($cgu);
        $manager->flush();

        $notificationManager = new NotificationManager();
        $notificationManager->sendToEveryone("Nouvelles CGU", "Acceptez nos nouvelles Conditions Générales d'Utilisation ! Allez sur le lien suivant : http://lienbidon.fr", $userRepo, $manager);

        return $this->redirectToRoute("admin");
    }

    /**
     * @Route("/admin/corbeille", name="trashCan")
     */
    public function trashCan(EntityManagerInterface $manager, AdvertRepository $advertRepo)
    {    
        return $this->render('admin/trashCan.html.twig');
    }

    /**
     * @Route("/admin/corbeille/demandes", name="trashCanAdvert")
     */
    public function trashCanAdvert(EntityManagerInterface $manager, AdvertRepository $advertRepo)
    {

        $adverts = $advertRepo->findWaste();
       
        return $this->render('admin/trashCanAdvert.html.twig', [
            "adverts" => $adverts
        ]);
    }

    /**
     * @Route("/admin/corbeille/propositions", name="trashCanOffer")
     */
    public function trashCanOffer(EntityManagerInterface $manager, OfferRepository $offerRepo)
    {

        $offers = $offerRepo->findWaste();
        
        return $this->render('admin/trashCanOffer.html.twig', [
            "offers" => $offers
        ]);
    }

    /**
     * @Route("/admin/corbeille/demandes/supprimer/all", name="trashCan_DeleteAdvertAll")
     */
    public function trashCan_DeleteAdvertAll(EntityManagerInterface $manager, AdvertRepository $advertRepo)
    {
        $adverts = $advertRepo->findWaste();

        foreach($adverts as $item){
            $manager->remove($item);
        }
        $manager->flush();
       
        return $this->redirectToRoute('trashCanAdvert');
    }

    /**
     * @Route("/admin/corbeille/propositions/supprimer/all", name="trashCan_DeleteOfferAll")
     */
    public function trashCan_DeleteOfferAll(EntityManagerInterface $manager, OfferRepository $offerRepo)
    {
        $offers = $offerRepo->findWaste();
        
        foreach($offers as $item){
            $manager->remove($item);
        }
        $manager->flush();
       
        return $this->redirectToRoute('trashCanOffer');
    }

    /**
     * @Route("/admin/corbeille/demandes/supprimer/{id}", name="trashCan_DeleteAdvert")
     */
    public function trashCan_DeleteAdvert(Advert $advert, EntityManagerInterface $manager)
    {
        $manager->remove($advert);
        $manager->flush();
       
        return $this->redirectToRoute('trashCanAdvert');
    }

    /**
     * @Route("/admin/corbeille/propositions/supprimer/{id}", name="trashCan_DeleteOffer")
     */
    public function trashCan_DeleteOffer(Offer $offer, EntityManagerInterface $manager)
    {
        $manager->remove($offer);
        $manager->flush();
       
        return $this->redirectToRoute('trashCanOffer');
    }

    /**
     * @Route("/admin/profilUser/{id}", name="adminUserProfile")
     */
    public function adminUserProfile(User $user, Request $request, EntityManagerInterface $manager)
    {
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

        return $this->render("admin/adminUserProfile.html.twig", [
            'user'=>$user,
            'form'=>$form->createView()
        ]);
    }

}
