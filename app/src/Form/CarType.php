<?php

namespace App\Form;

use App\Entity\Cars;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('owner', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // Wyświetlanie emaila jako etykiety w wyborze
                'label' => 'Właściciel',
                'disabled' => $options['data']->getOwner() !== null, // Zablokowane, jeśli ustawione
                'placeholder' => 'Wybierz właściciela',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Proszę wybrać właściciela pojazdu.']),
                    new Assert\Valid(),  // Upewnia się, że właściciel jest poprawnym obiektem
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('licencePlate', TextType::class, [
                'label' => 'Numer rejestracyjny',
                'attr' => [
                    'placeholder' => 'Wprowadź numer rejestracyjny',
                    'class' => 'form-control'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Numer rejestracyjny jest wymagany.']),
                    new Assert\Length([
                        'max' => 10,
                        'maxMessage' => 'Numer rejestracyjny nie może mieć więcej niż {{ limit }} znaków.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z0-9]+$/', // Prosty regex dla numeru rejestracyjnego (wymaga tylko liter i cyfr)
                        'message' => 'Numer rejestracyjny może zawierać tylko litery i cyfry.',
                    ]),
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nazwa pojazdu',
                'attr' => [
                    'placeholder' => 'Wprowadź nazwę auta',
                    'class' => 'form-control'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Nazwa pojazdu jest wymagana.']),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Nazwa pojazdu nie może być dłuższa niż {{ limit }} znaków.',
                    ]),
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Typ pojazdu',
                'choices' => [
                    'Elektryczny' => 'elektryczny',
                    'Spalinowy' => 'spalinowy',
                    'Hybrydowy' => 'hybrydowy'
                ],
                'placeholder' => 'Wybierz typ pojazdu',
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Proszę wybrać typ pojazdu.']),
                    new Assert\Choice([
                        'choices' => ['elektryczny', 'spalinowy', 'hybrydowy'],
                        'message' => 'Wybierz poprawny typ pojazdu.',
                    ]),
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Zapisz',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cars::class,
        ]);
    }
}
