<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\EbookSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\IsTrue;

class EbookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Votre nom',
                    'class' => 'w-full p-3 bg-[#222222] border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0ed0ff] text-white transition duration-200'
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
                    'class' => 'w-full p-3 bg-[#222222] border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0ed0ff] text-white transition duration-200'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est requis']),
                    new Email(['message' => 'L\'email n\'est pas valide'])
                ]
            ])
            ->add('consent', CheckboxType::class, [
                'label' => 'J\'accepte de recevoir l\'ebook et des informations de la part d\'Enorehab',
                'label_attr' => [
                    'class' => 'text-sm text-gray-400'
                ],
                'required' => true,
                'mapped' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions pour recevoir l\'ebook'
                    ])
                ],
                'attr' => [
                    'class' => 'mt-1 mr-2'
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
            'data_class' => EbookSubscriber::class,
            'csrf_protection' => true,
            'csrf_field_name' => 'csrf_token',
            'csrf_token_id' => 'ebook_form'
        ]);
    }
}