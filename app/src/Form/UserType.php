<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\EqualTo;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'email'],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Adres e-mail jest wymagany.',
                    ]),
                    new Assert\Email([
                        'message' => 'Proszę podać poprawny adres e-mail.',
                    ]),
                    new Assert\Length([
                        'max' => 180,
                        'maxMessage' => 'Adres e-mail nie może być dłuższy niż {{ limit }} znaków.',
                    ]),
                    new Assert\UniqueEntity([
                        'fields' => 'email',
                        'message' => 'Ten adres e-mail jest już zajęty.',
                    ])
                ]
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['placeholder' => 'Hasło'],
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Hasło jest wymagane.',
                    ]),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Hasło musi mieć przynajmniej {{ limit }} znaków.',
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
                    ])
                ]
            ])
            ->add('save', SubmitType::class)
        ;
    }
}