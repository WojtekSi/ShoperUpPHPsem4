<?php

// namespace App\Controller;

// use App\Entity\User;
// use App\Form\UserType;
// use App\Repository\UserRepository;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
// use Symfony\Component\Routing\Attribute\Route;
// use Symfony\Component\Security\Http\Attribute\IsGranted;
// use Symfony\Component\Validator\Constraints as Assert;
// use Symfony\Component\Validator\Validation;

// //#[isGranted("ROLE_ADMIN")]
// class ReservationController extends AbstractController
// {

//     #[Route('/reservation/{date}', name: 'reservation')]
//     public function reservation(string $date, Request $request)
//     {
//         $validator = Validation::createValidator();
//         $constraint = new Assert\DateTime();
//         $violations = $validator->validate($date, $constraint);

//         if (count($violations) > 0) {

//             return $this->render('reservation/reservation.html.twig', [
//                 'dateIsCorrect' => true,
//                 'date' => $date
//             ]);
//         } else {
//             return $this->redirectToRoute('panel_index');
//         }


//         $reservation = $entityManager->find(Revervations::class, $date);
//         if (!$reservation) {
//             throw $this->createNotFoundException('Nie znaleziono użytkownika');
//         }
//         return $this->render('reservation/reservation.html.twig', [
//             'date' => $date
//         ]);
//     }

//     #[Route('/reservation/{date}/book', name: 'reservation_book')]
//     public function reservatio_book(int $date, Request $request)
//     {

//         //jeśli robi to user, i data ma możliwość rezerwacji, zajmij pierwsze wolne miejsce
//         //jesli 
//         return $this->render('reservation/reservation.html.twig');
//     }


//     #[Route('/reservation/{date}/delete', name: 'reservation_delete')]
//     public function reservation_delete(int $date, Request $request)
//     {
//         //jeśli robi to user, to jeśli ma rezerwację tego dnia, usuń ją i odśwież stronę na któej sie znajdujesz
//         //jeśli robi to user, to usuń rezerwację w którą klika
//         return $this->render('reservation/reservation.html.twig');
//     }
// }