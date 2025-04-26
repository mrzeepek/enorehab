<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Bilan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class BilanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Votre nom',
                    'class' => 'w-full p-4 bg-[#111111] border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0ed0ff] text-white transition duration-200'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est requis']),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Votre email',
                    'class' => 'w-full p-4 bg-[#111111] border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0ed0ff] text-white transition duration-200'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est requis']),
                    new Email(['message' => 'L\'email n\'est pas valide'])
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre téléphone',
                    'class' => 'w-full p-4 bg-[#111111] border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0ed0ff] text-white transition duration-200'
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[0-9+\s()-]{8,20}$/',
                        'message' => 'Le format du numéro de téléphone n\'est pas valide'
                    ])
                ]
            ])
            ->add('instagram', TextType::class, [
                'label' => 'Instagram',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre instagram',
                    'class' => 'w-full p-4 bg-[#111111] border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0ed0ff] text-white transition duration-200'
                ]
            ])
            // Champ honeypot pour la protection anti-spam
            ->add('website', HiddenType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bilan::class,
            'csrf_protection' => true,
            'csrf_field_name' => 'csrf_token',
            'csrf_token_id' => 'bilan_form'
        ]);
    }
}