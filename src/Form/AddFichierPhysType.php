<?php

namespace App\Form;

use App\Entity\Fichier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AddFichierPhysType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder           
            ->add('libelle_fichier', TextType::class, [
                'label' => 'Libellé de la pièce',
                    'attr' => [
                    'placeholder' => 'Entrer le libellé de la pièce',
                ]
        ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    "---Choisir---" => null,
                    "Document" => "Document",
                    "Image" => "Image",
                    "Vidéo" => "Vidéo",
                    "Audio" => "Audio",
                ],
                'attr' => [
                    'class' => 'type-choices'
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'Un type de pièce est requis !'
                    ])
                ],
        ])
        ->add('tags',  HiddenType::class, [
            'attr' => [
                'class' => 'add-file-tags-input',
                'mapped' => true
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fichier::class,
        ]);
    }
}
