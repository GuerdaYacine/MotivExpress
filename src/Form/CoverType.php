<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Votre nom',
                'attr' => [
                    'placeholder' => 'Votre nom',
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Votre prénom',
                'attr' => [
                    'placeholder' => 'Votre prénom',
                ],
            ])
            ->add('diplome', TextareaType::class, [
                'label' => 'Votre diplôme',
                'attr' => [
                    'placeholder' => 'Votre diplôme',
                ],
            ])
            ->add('entreprise', TextType::class, [
                'label' => 'Votre Entreprise',
                'attr' => [
                    'placeholder' => 'Votre Entreprise',
                ],
            ])
            ->add('poste', TextType::class, [
                'label' => 'Votre Poste',
                'attr' => [
                    'placeholder' => 'Votre Poste',
                ],
            ])
            ->add('annonce', TextareaType::class, [
                'label' => 'Annonce',
                'attr' => [
                    'placeholder' => 'Annonce',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer une lettre de motivation',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
