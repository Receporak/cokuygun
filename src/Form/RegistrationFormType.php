<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'form-control'
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label'=> 'Şartları kabul ediyorum',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Şartlarımızı kabul etmelisiniz.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => 'Şifre',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Şifre',
                    'class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Lütfen şifrenizi giriniz.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Şifreniz en az {{ limit }} karakter olmalıdır.',
                        // max length allowed by Symfony for security reasons
                        'max' => 96,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
