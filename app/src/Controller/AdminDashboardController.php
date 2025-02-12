<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Cars;
use App\Form\UserType;
use App\Form\CarType;
use App\Form\ReservationsType;
use App\Repository\UserRepository;
use App\Repository\CarsRepository;
use App\Entity\ParkingSpaces;
use App\Entity\Reservations;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;


class AdminDashboardController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin_index')]
    public function index(EntityManagerInterface $entityManager)
    {
        $dates = [];
        $reservationsRepository = $entityManager->getRepository(Reservations::class);
    
        // Pobranie dat przez 14 dni
        for ($i = 0; $i < 14; $i++) {
            $date = new \DateTimeImmutable();
            $date = $date->modify("+$i day");
            $dateFormatted = $date->format('Y-m-d');

            $reservations = $reservationsRepository->findBy(['date' => $date]);


            $reservationsCount = count($reservations);
    
            $dates[] = [
                'date' => $dateFormatted,
                'reservationsCount' => $reservationsCount,
            ];
        }
    
        return $this->render('AdminDashboard/index.html.twig', [
            'dates' => $dates,
        ]);
    }

}
