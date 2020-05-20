<?php

namespace App\Controller;

use App\Entity\cgu;
use App\Entity\User;
use App\Repository\AdvertRepository;
use App\Repository\OfferRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserManager;
use Symfony\Component\Notifier\Notification\Notification;

class AdminController extends AbstractController
{

    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->render('cdv/admin/index.html.twig');
    }

    /**
     * @Route("/admin/utilisateurs", name="adminUsers")
     */
    public function adminUsers(UserRepository $userRepo)
    {
        return $this->render('cdv/admin/adminUsers.html.twig', [
            'users'=>$userRepo->findAll()
        ]);
    }

    /**
     * @Route("/admin/courses", name="adminOffers")
     */
    public function adminOffers(OfferRepository $offerRepo)
    {
        return $this->render('cdv/admin/adminOffers.html.twig', [
            'offers'=>$offerRepo->findAll()
        ]);
    }

    /**
     * @Route("/admin/demandes", name="adminAdverts")
     */
    public function adminAdverts(AdvertRepository $advertRepo)
    {
        return $this->render('cdv/admin/adminAdverts.html.twig', [
            'adverts'=>$advertRepo->findAll()
        ]);
    }

    /**
     * @Route("/admin/signalements", name="adminReports")
     */
    public function adminReports(ReportRepository $reportRepo)
    {
        return $this->render('cdv/admin/adminReports.html.twig', [
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
        
        $this->redirectToRoute("adminUsers");
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

}
