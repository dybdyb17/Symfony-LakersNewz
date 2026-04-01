<?php

namespace App\Form;

use App\Entity\MatchNba;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('team1', TextType::class, [
                'label' => 'Équipe 1',
                'attr' => ['placeholder' => 'Ex: Los Angeles Lakers'],
            ])
            ->add('team2', TextType::class, [
                'label' => 'Équipe 2',
                'attr' => ['placeholder' => 'Ex: Golden State Warriors'],
            ])
            ->add('cote1', NumberType::class, [
                'label' => 'Cote équipe 1',
                'scale' => 2,
                'attr' => ['placeholder' => '1.85'],
            ])
            ->add('cote2', NumberType::class, [
                'label' => 'Cote équipe 2',
                'scale' => 2,
                'attr' => ['placeholder' => '2.10'],
            ])
            ->add('dateMatch', DateTimeType::class, [
                'label' => 'Date du match',
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MatchNba::class,
        ]);
    }
}
