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


class AdminUserController extends AbstractController
{

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/user', name: 'admin_new_user')]
    public function newUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $password = $hasher->hashPassword($user, $password);
            $user->setPassword($password);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_list_users');
        }

        return $this->render('AdminDashboard/AdminUserDashboard/new_user.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/users', name: 'admin_list_users')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('AdminDashboard/AdminUserDashboard/list_users.html.twig', [
            'users' => $users,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/user/{id}', name: 'admin_edit_user')]
//    #[IsGranted('ROLE_ADMIN', message: 'nie masz dostępu', exceptionCode: 1000)]
    public function editUser(int $id, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
//        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'brak dostępu');

        $user = $entityManager->find(User::class, $id);
        if (!$user) {
            throw $this->createNotFoundException('Nie znaleziono użytkownika');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            if ($password) {
                $password = $hasher->hashPassword($user, $password);
                $user->setPassword($password);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_list_users');
        }

        return $this->render('AdminDashboard/AdminUserDashboard/edit_user.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/user/{id}/delete', name: 'admin_delete_user', methods: ['POST'])]
    public function deleteUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->find(User::class, $id);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($user->isAdmin()) {
            throw new \Exception('test');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('admin_list_users');
    }

}