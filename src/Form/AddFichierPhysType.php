<?php

namespace App\Form;

use App\Entity\Fichier;
use App\Entity\RegleRetention;
use App\Entity\BoitePhysique;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

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
        ])
        ->add('regle_retention', EntityType::class, [
            'class' => RegleRetention::class,
            'choice_label' => 'libelle',
            'label' => 'Règle de rétention',
            'required' => false,
            'placeholder' => 'Aucune règle (optionnel)',
            'attr' => [
                'class' => 'form-select',
            ],
        ])
        ->add('date_debut', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de début',
            'required' => false,
            'attr' => [
                'class' => 'form-control',
            ],
        ])
        ->add('date_fin', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date de fin',
            'required' => false,
            'attr' => [
                'class' => 'form-control',
            ],
        ])
        ->add('typologie_documentaire', ChoiceType::class, [
            'label' => 'Typologie documentaire',
            'required' => false,
            'placeholder' => 'Sélectionner un type (optionnel)',
            'choices' => [
                'Administratif' => 'Administratif',
                'Financier' => 'Financier',
                'Juridique' => 'Juridique',
                'Technique' => 'Technique',
                'Historique' => 'Historique',
                'Personnel' => 'Personnel',
                'Autre' => 'Autre',
            ],
            'attr' => [
                'class' => 'form-select',
            ],
        ])
        ->add('boite_physique', EntityType::class, [
            'class' => BoitePhysique::class,
            'choice_label' => function(BoitePhysique $boite) {
                return $boite->getCodeBoite() . ' - ' . $boite->getLibelle() . 
                       ($boite->getLocalisation() ? ' (' . $boite->getLocalisation() . ')' : '');
            },
            'label' => 'Boîte physique',
            'required' => false,
            'placeholder' => 'Sélectionner une boîte (optionnel)',
            'attr' => [
                'class' => 'form-select',
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
