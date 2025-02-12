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



class UserDashboardController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/user', name: 'user_index')]
    public function index(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_index');
        }

        $user = $this->getUser();
        $userEmail = $this->getUser()->getEmail();

        $dates = [];
        $reservationsRepository = $entityManager->getRepository(Reservations::class);
    
        for ($i = 0; $i < 8; $i++) {
            $date = new \DateTimeImmutable();
            $date = $date->modify("+$i day");
            $dateFormatted = $date->format('Y-m-d');
    
            $reservations = $reservationsRepository->findBy(['date' => $date]);
            $reservationsCount = count($reservations);
    
            $myReservationsCount = 0;
            $myReservation = null;

            foreach ($reservations as $reservation) {
                // Sprawdzamy, czy rezerwacja ma użytkownika o takim e-mailu
                if ($reservation->getUser() && $reservation->getUser()->getEmail() === $userEmail) {
                    $myReservationsCount++;

                    $myReservation = $reservation;
                }
            }
    
            // Dodajemy datę do listy
            $dates[] = [
                'date' => $dateFormatted,
                'reservationsCount' => $reservationsCount,
                'hasReservation' => $reservationsCount > 0,
                'myReservationsCount' => $myReservationsCount,
                'myReservation' => $myReservation,
            ];
        }

        $carsRepository = $entityManager->getRepository(Cars::class);

        $myCars = $carsRepository->findBy(['owner' => $user]);

    
        return $this->render('UserDashboard/index.html.twig', [
            'dates' => $dates,
            'cars' => $myCars,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('user/reservation/add/{date}', name: 'user_reservation_add', methods: ['POST'])]
    public function reservation_add(Request $request, string $date, EntityManagerInterface $entityManager)
    {
        // Walidacja daty
        $validator = Validation::createValidator();
        $constraint = new Assert\DateTime(['format' => 'Y-m-d']);
        $violations = $validator->validate($date, $constraint);
        if (count($violations) > 0) {
            // Obsługa błędów walidacji daty (np. zwrócenie błędu lub komunikatu)
            return $this->json(['error' => 'Invalid date format'], 400);
        }
        $date = new \DateTimeImmutable($date);  // Użyj przekazanej daty z PUT
    
        // Pobierz bieżącego użytkownika
        $user = $this->getUser();
        
        // Pobierz miejsca parkingowe
        $parkingSpacesRepository = $entityManager->getRepository(ParkingSpaces::class);
        $parkingSpaces = $parkingSpacesRepository->findAll();
    
        // Pobierz rezerwacje na dany dzień
        $reservationsRepository = $entityManager->getRepository(Reservations::class);
        $reservations = $reservationsRepository->findBy(['date' => $date]);
    
        $availableParkingSpaces = null;
    
        // Stwórz tablicę z identyfikatorami zarezerwowanych miejsc parkingowych
        $reservedParkingSpaceIds = array_map(function ($reservation) {
            return $reservation->getParkingSpace()->getId();
        }, $reservations);
    
        // Przejdź po wszystkich miejscach parkingowych i sprawdź, które nie są zarezerwowane
        foreach ($parkingSpaces as $parkingSpace) {
            if (!in_array($parkingSpace->getId(), $reservedParkingSpaceIds)) {
                $availableParkingSpaces = $parkingSpace;
                break;
            }
        }
    
        // Pobierz pierwszy samochód użytkownika
        $CarsRepository = $entityManager->getRepository(Cars::class);
        $myCar = $CarsRepository->findOneBy(['owner' => $user]);
    
        // Sprawdzamy, czy znaleźliśmy dostępne miejsce parkingowe i samochód
        if ($availableParkingSpaces === null || $myCar === null) {
            return $this->json(['error' => 'No available parking space or car not found'], 400);
        }
    
        // Stwórz nową rezerwację
        $newReservation = new Reservations();
        $newReservation->setDate($date);
        $newReservation->setCar($myCar);
        $newReservation->setUser($user);
        $newReservation->setParkingSpace($availableParkingSpaces);
    
        // Zapisz rezerwację w bazie danych
        $entityManager->persist($newReservation);
        $entityManager->flush();
    
        // Zwróć odpowiedź JSON, potwierdzając, że rezerwacja została pomyślnie dodana
        return $this->redirectToRoute('user_index');
    }
    


    #[IsGranted('ROLE_USER')]
    #[Route('user/reservation/edit/{date}', name: 'user_reservation_edit')]
    public function reservation_edit(Request $request, string $date, EntityManagerInterface $entityManager)
    {
        
        // Walidacja daty
        $validator = Validation::createValidator();
        $constraint = new Assert\DateTime(['format' => 'Y-m-d']);
        $violations = $validator->validate($date, $constraint);


        $user = $this->getUser();
        $userEmail = $this->getUser()->getEmail();
        
        $dateInDateTimeImmutableType = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
    
        if ($dateInDateTimeImmutableType === false) {
            $this->addFlash('error', 'Nieprawidłowy format daty.');
            return $this->redirectToRoute('user_index');
        }
    


        $reservation = $entityManager->getRepository(Reservations::class)
            ->findOneBy([
                'date' => $dateInDateTimeImmutableType,
                'user' => $user,
            ]);

        $myCars = $entityManager->getRepository(Reservations::class)
            ->findBy(['user' => $user ]);

            
        if ($reservation !== null && count($violations) === 0) {
    
            $form = $this->createForm(ReservationsType::class, $reservation);
            $form->handleRequest($request);


            $form->get('car')->setFormTypeOption('choices', $myCars);
    
            if ($form->isSubmitted() && $form->isValid()) {
                
                $user = $form->get('user')->getData();
                $car = $form->get('car')->getData();
    
                if ($car && $car->getOwner() !== $user) {
                    $this->addFlash('error', 'Wybrany samochód nie należy do wybranego użytkownika.');
                } else {
                    $entityManager->persist($reservation);
                    $entityManager->flush();
                    return $this->redirectToRoute('user_index');
                }
            }
    
            return $this->render('UserDashboard/reservation/user_reservation_edit.html.twig', [
                'date' => $date,
                'reservation' => $reservation,
                'user' => $user,   
                'cars' => $myCars,
                'form' => $form->createView(),
            ]);
        } else {
            $this->addFlash('error', 'Rezerwacja lub miejsce parkingowe nie zostały znalezione.');
            return $this->redirectToRoute('user_index');
        }
    }
    



    #[IsGranted('ROLE_USER')]
    #[Route('user/reservation/delete/{date}', name: 'user_reservation_delete', methods: ['POST']), ]
    public function reservation_delete(Request $request, string $date, EntityManagerInterface $entityManager)
    {
        $validator = Validation::createValidator();
        $constraint = new Assert\DateTime(['format' => 'Y-m-d']);
        $violations = $validator->validate($date, $constraint);
        if (count($violations) > 0) {
            return $this->json(['error' => 'Invalid date format'], 400);
        }
        $date = new \DateTimeImmutable($date);


        $user = $this->getUser();
        
        $reservationsRepository = $entityManager->getRepository(Reservations::class);
        $reservation = $reservationsRepository->findOneBy(['date' => $date, 'user' => $user ]);
    
    
        $entityManager->remove($reservation);
        $entityManager->flush();


        return $this->redirectToRoute('user_index');
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/user/cars/add', name: 'user_cars_add')]
    public function adminCarsAdd(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $car = new Cars(); 
        $car->setOwner($user);

        $form = $this->createForm(CarType::class, $car);

    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($car);
            $entityManager->flush();
    
            return $this->redirectToRoute('user_index'); 
        }
    
        return $this->render('UserDashboard/car/user_car_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/user/cars/edit/{id}', name: 'user_cars_edit')]
    public function userCarsEdit(Request $request, int $id, EntityManagerInterface $entityManager, CarsRepository $carsRepository): Response
    {
        $user = $this->getUser();
        $car = $carsRepository->find($id);
        $car->setOwner($user);

        $form = $this->createForm(CarType::class, $car);

    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($car);
            $entityManager->flush();
    
            return $this->redirectToRoute('user_index'); 
        }
    
        return $this->render('UserDashboard/car/user_car_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('user/cars/delete/{id}', name: 'user_cars_delete', methods: ['POST']), ]
    public function userCarsDelete(Request $request, int $id, EntityManagerInterface $entityManager)
    {
        $carsRepository = $entityManager->getRepository(Cars::class);
        $car = $carsRepository->find($id);    
    
        $entityManager->remove($car);
        $entityManager->flush();


        return $this->redirectToRoute('user_index');
    }
}
