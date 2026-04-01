<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['placeholder' => 'Titre de l\'article'],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['placeholder' => 'Contenu de l\'article', 'rows' => 10],
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
                'required' => false,
                'choices' => [
                    'News' => 'news',
                    'Rumeurs' => 'rumeurs',
                    'Interviews' => 'interviews',
                    'Analyses' => 'analyses',
                ],
            ])
            ->add('image', TextType::class, [
                'label' => 'URL de l\'image',
                'required' => false,
                'attr' => ['placeholder' => 'https://...'],
            ])
            ->add('auteur', TextType::class, [
                'label' => 'Auteur',
                'required' => false,
                'attr' => ['placeholder' => 'Lakers Newz'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
