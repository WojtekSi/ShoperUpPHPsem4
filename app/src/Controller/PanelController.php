<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

//#[isGranted("ROLE_ADMIN")]
class PanelController extends AbstractController
{
    #[Route('/panel', name: 'panel_index')]
    public function index()
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_index');
        }

        $dates = [];
        
        for ($i = 0; $i < 7; $i++) {
            $date = new \DateTime();
            $date->modify("+$i day");
            $dates[] = $date->format('d-m-Y');
        }

        return $this->render('panel/index.html.twig',[
            'dates' => $dates,
        ]);
    }

}
