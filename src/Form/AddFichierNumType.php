<?php

namespace App\Form;

use App\Entity\Fichier;
use App\Entity\RegleRetention;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AddFichierNumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fichiers', FileType::class, [
                'label' => false,
                'mapped' => false,
                'multiple' => true,
                'attr' => [
                    'class' => 'form-control-file',
                    'id' => 'file-input'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Un nom de fichier est requis !'
                    ])
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fichier::class,
        ]);
    }
}
