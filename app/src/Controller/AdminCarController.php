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


class AdminCarController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/cars', name: 'cars_list')]
    public function listCars(CarsRepository $carsRepository): Response
    {
        $cars = $carsRepository->findAll();

        return $this->render('AdminDashboard/AdminCarDashboard/list_cars.html.twig', [
            'cars' => $cars,
        ]);
    }
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/cars/add', name: 'cars_add')]
    public function listCarsAdd(Request $request, EntityManagerInterface $entityManager, CarsRepository $carsRepository): Response
    {
        $car = new Cars(); 
        $form = $this->createForm(CarType::class, $car);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($car);
            $entityManager->flush();
    
            return $this->redirectToRoute('cars_list'); 
        }
    
        return $this->render('AdminDashboard/AdminCarDashboard/car_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/cars/edit/{id}', name: 'cars_edit')]
    public function listCarsEdit(Request $request, int $id, EntityManagerInterface $entityManager, CarsRepository $carsRepository): Response
    {
        $car = $entityManager->find(Cars::class, $id);
        if (!$car) {
            throw $this->createNotFoundException('Nie znaleziono Pojazdu');
        }

        $form = $this->createForm(CarType::class, $car);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->persist($car);
            $entityManager->flush();

            return $this->redirectToRoute('cars_list');
        }

        return $this->render('AdminDashboard/AdminCarDashboard/car_add.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('admin/cars/delete/{id}', name: 'admin_cars_delete', methods: ['POST']), ]
    public function userCarsDelete(Request $request, int $id, EntityManagerInterface $entityManager)
    {
        $carsRepository = $entityManager->getRepository(Cars::class);
        $car = $carsRepository->find($id);    
    
        $entityManager->remove($car);
        $entityManager->flush();


        return $this->redirectToRoute('cars_list');
    }
}