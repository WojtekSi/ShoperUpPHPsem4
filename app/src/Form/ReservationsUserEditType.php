<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\ParkingSpaces;
use App\Entity\User;
use App\Entity\Cars;
use App\Repository\UserRepository;

class ReservationsUserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $today = new \DateTimeImmutable();
        $dates = [];
        for ($i = 0; $i < 14; $i++) {
            $date = $today->modify("+$i days");
            $formattedDate = $date->format('Y-m-d');
            $dates[$formattedDate] = $formattedDate;  // Label => Value
        }

        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd', // Format daty
                'label' => 'Data rezerwacji',
                'required' => true,
                'disabled' => $options['data']->getDate() !== null, // Sprawdzenie, czy data już istnieje
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Proszę wybrać datę rezerwacji']),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'Data rezerwacji musi być dzisiejsza lub w przyszłości.',
                    ]),
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('parking_space', EntityType::class, [
                'class' => ParkingSpaces::class,
                'choice_label' => 'name',
                'placeholder' => 'Wybierz miejsce parkingowe',
                'required' => true,
                'disabled' => $options['data']->getParkingSpace() !== null, // Zablokowane, jeśli ustawione
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Proszę wybrać miejsce parkingowe']),
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'placeholder' => 'Wybierz użytkownika',
                'required' => true,
                'disabled' => $options['data']->getUser() !== null,
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%USER_ROLE%');
                },
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Proszę wybrać użytkownika']),
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('car', EntityType::class, [
                'class' => Cars::class,
                'choice_label' => function (Cars $car) {
                    return $car->getOwner()->getEmail() . ' - ' . $car->getLicencePlate() . $car->getName();
                },
                'placeholder' => 'Wybierz samochód',
                'required' => false,
                'query_builder' => function (CarsRepository $carsRepository) use ($options) {
                    return $carsRepository->createQueryBuilder('c')
                        ->where('c.owner = :user')
                        ->setParameter('user', $options['user']); // Pobiera tylko samochody użytkownika
                },
                'attr' => ['class' => 'form-control']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Zapisz rezerwację',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }
}
