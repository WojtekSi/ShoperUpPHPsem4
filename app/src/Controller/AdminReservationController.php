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


class AdminReservationController extends AbstractController
{

    #[IsGranted('ROLE_ADMIN')]
    #[Route('admin/reservation/show/{date}', name: 'admin_reservation')]
    public function reservation(string $date, EntityManagerInterface $entityManager)
    {
        $validator = Validation::createValidator();
        $constraint = new Assert\DateTime();
        $violations = $validator->validate($date, $constraint);

        if (count($violations) > 0) {

            $parkingSpacesRepository = $entityManager->getRepository(ParkingSpaces::class);
            $parkingSpaces = $parkingSpacesRepository->findAll();

            $reservationsRepository = $entityManager->getRepository(Reservations::class);
            $dateInTypeDateImmutable = new \DateTimeImmutable($date);
            $reservations = $reservationsRepository->findBy(['date' => $dateInTypeDateImmutable]);


            $bookedIds = array_map(function($reservation) {
                return $reservation->getParkingSpace()->getId();
            }, $reservations);

            $placeIsBooked = array_map(function($space) use ($bookedIds) {
                return (object)[
                    'id' => $space->getId(),
                    'name' => $space->getName(),
                    'isBooked' => in_array($space->getId(), $bookedIds),
                    'extrainfo' => in_array($space->getId(), $bookedIds) ? 'Zarezerwowane' : 'Wolne'
                ];
            }, $parkingSpaces);


            return $this->render('AdminDashboard/AdminReservationDashboard/admin_reservation.html.twig', [
                'date' => $date,
                'placeIsBooked' => $placeIsBooked
            ]);
        } else {
            return $this->redirectToRoute('/admin');
        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('admin/reservation/add', name: 'admin_reservation_add')]
    public function reservation_add(Request $request, EntityManagerInterface $entityManager)
    {
        $reservation = new Reservations();
        $form = $this->createForm(ReservationsType::class, $reservation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->get('user')->getData();
            $car = $form->get('car')->getData();

            // Pobranie daty z formularza
            $date = $form->get('date')->getData();

            // Sprawdzenie, czy data to obiekt DateTime (np. DateTime), a jeśli tak, to konwertujemy ją na DateTimeImmutable
            if ($date instanceof \DateTime) {
                $date = \DateTimeImmutable::createFromMutable($date);
            }

            // Przypisanie daty do rezerwacji
            if ($date instanceof \DateTimeImmutable) {
                $reservation->setDate($date);
            }

            // Sprawdzenie, czy samochód należy do wybranego użytkownika
            if ($car && $car->getOwner() !== $user) {
                $this->addFlash('error', 'Wybrany samochód nie należy do wybranego użytkownika.');
            } else {
                $entityManager->persist($reservation);
                $entityManager->flush();

                // Przekierowanie do widoku z datą w odpowiednim formacie
                return $this->redirectToRoute('admin_reservation', [
                    'date' => $reservation->getDate()->format('Y-m-d')
                ]);
            }
        }

        return $this->render('AdminDashboard/AdminReservationDashboard/admin_reservation_add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('admin/reservation/add/{date}/{place_id}', name: 'admin_reservation_add_date_place')]
    public function reservation_add_date_place(Request $request, string $date, int $place_id, EntityManagerInterface $entityManager)
    {
        $parkingSpace = $entityManager->getRepository(ParkingSpaces::class)->find($place_id);
    
        $validator = Validation::createValidator();
        $constraint = new Assert\DateTime(['format' => 'Y-m-d']);
        $violations = $validator->validate($date, $constraint);


    
        if ($parkingSpace !== null && count($violations) === 0) {

            $reservation = new Reservations();
            $reservation->setDate(new \DateTimeImmutable($date));
            $reservation->setParkingSpace($parkingSpace);

    
            $form = $this->createForm(ReservationsType::class, $reservation);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->get('user')->getData();
                $car = $form->get('car')->getData();

                if ($car && $car->getOwner() !== $user) {
                    $this->addFlash('error', 'Wybrany samochód nie należy do wybranego użytkownika.');
                } else {
                    $entityManager->persist($reservation);
                    $entityManager->flush();
                    return $this->redirectToRoute('admin_reservation', ['date' => $date]);
                }
            }
    
            return $this->render('AdminDashboard/AdminReservationDashboard/admin_reservation_add.html.twig', [
                'date' => $date,
                'placeName' => $parkingSpace->getName(),
                'form' => $form->createView()
            ]);
        } else {
            return $this->redirectToRoute('admin_index');  
        }
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('admin/reservation/edit/{date}/{place_id}', name: 'admin_reservation_edit')]
    public function reservation_edit(Request $request, string $date, int $place_id, EntityManagerInterface $entityManager)
    {
        $parkingSpace = $entityManager->getRepository(ParkingSpaces::class)->find($place_id);
        
        // Walidacja daty
        $validator = Validation::createValidator();
        $constraint = new Assert\DateTime(['format' => 'Y-m-d']);
        $violations = $validator->validate($date, $constraint);
        
        $dateInDateTimeImmutableType = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
    
        if ($dateInDateTimeImmutableType === false) {
            $this->addFlash('error', 'Nieprawidłowy format daty.');
            return $this->redirectToRoute('admin_index');
        }
    
        $reservation = $entityManager->getRepository(Reservations::class)
            ->findOneBy([
                'date' => $dateInDateTimeImmutableType,
                'parkingSpace' => $parkingSpace
            ]);
    
        if ($parkingSpace !== null && $reservation !== null && count($violations) === 0) {
    
            $form = $this->createForm(ReservationsType::class, $reservation);
    
            // Zablokowanie edycji daty i miejsca parkingowego
            // $form->get('date')->setDisabled(true);
            // $form->get('parkingSpace')->setDisabled(true);
    
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->get('user')->getData();
                $car = $form->get('car')->getData();
    
                if ($car && $car->getOwner() !== $user) {
                    $this->addFlash('error', 'Wybrany samochód nie należy do wybranego użytkownika.');
                } else {
                    $entityManager->persist($reservation);
                    $entityManager->flush();
                    return $this->redirectToRoute('admin_reservation', ['date' => $date]);
                }
            }
    
            return $this->render('AdminDashboard/AdminReservationDashboard/admin_reservation_edit.html.twig', [
                'date' => $date,                // Przekazujemy datę
                'placeName' => $parkingSpace->getName(),  // Przekazujemy nazwę miejsca parkingowego
                'form' => $form->createView()    // Przekazujemy formularz
            ]);
        } else {
            $this->addFlash('error', 'Rezerwacja lub miejsce parkingowe nie zostały znalezione.');
            return $this->redirectToRoute('admin_index');
        }
    }
    



    #[IsGranted('ROLE_ADMIN')]
    #[Route('admin/reservation/delete/{date}/{place_id}', name: 'admin_reservation_delete', methods: ['POST']), ]
    public function reservation_delete(Request $request, string $date, int $place_id, EntityManagerInterface $entityManager): Response
    {
        $parkingSpace = $entityManager->getRepository(ParkingSpaces::class)->find($place_id);
    
        $validator = Validation::createValidator();
        $constraint = new Assert\DateTime(['format' => 'Y-m-d']);
        $violations = $validator->validate($date, $constraint);
        $dateInDateTimeImmutableType = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        $reservation = $entityManager->getRepository(Reservations::class)
        ->findOneBy([
            'date' => $dateInDateTimeImmutableType,
            'parkingSpace' => $place_id
        ]);
    
        if ($parkingSpace !== null && $reservation !== null && count($violations) === 0) {
                    $entityManager->remove($reservation);
                    $entityManager->flush();
                    return $this->redirectToRoute('admin_reservation', ['date' => $date]);
        } else {
            return $this->redirectToRoute('admin_index');  
        }
    }

}