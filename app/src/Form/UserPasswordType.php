<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Nowe hasło',
                    'attr' => ['placeholder' => 'Hasło'],
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => 'Hasło jest wymagane.',
                        ]),
                        new Assert\Length([
                            'min' => 8,
                            'minMessage' => 'Hasło musi mieć co najmniej {{ limit }} znaków.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/[A-Z]/',
                            'message' => 'Hasło musi zawierać przynajmniej jedną dużą literę.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/[a-z]/',
                            'message' => 'Hasło musi zawierać przynajmniej jedną małą literę.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/[0-9]/',
                            'message' => 'Hasło musi zawierać przynajmniej jedną cyfrę.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/[\W_]/',
                            'message' => 'Hasło musi zawierać przynajmniej jeden znak specjalny.',
                        ]),
                    ]
                ],
                'second_options' => [
                    'label' => 'Potwierdź nowe hasło',
                    'attr' => ['placeholder' => 'Potwierdź hasło'],
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => 'Potwierdzenie hasła jest wymagane.',
                        ]),
                    ]
                ],
                'invalid_message' => 'Hasła muszą być takie same.',
            ])
            ->add('Zapisz zmiany', SubmitType::class)
        ;
    }
}
