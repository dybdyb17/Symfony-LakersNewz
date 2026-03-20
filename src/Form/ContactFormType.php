<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'entre ton nom'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'email'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre email']),
                ],
            ])
            ->add('sujet', ChoiceType::class, [
                'label' => 'Sujet',
                'placeholder' => 'choisir un sujet',
                'choices' => [
                    'Question générale' => 'question',
                    'Suggestion' => 'suggestion',
                    'Signaler un bug' => 'bug',
                    'Partenariat' => 'partenariat',
                    'Autre' => 'autre',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir un sujet']),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['placeholder' => 'message', 'rows' => 5],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un message']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
