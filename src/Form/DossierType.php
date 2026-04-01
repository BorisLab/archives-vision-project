<?php

namespace App\Form;

use App\Entity\Dossier;
use App\Entity\Departement;
use App\Entity\RegleRetention;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DossierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle_dossier', TextType::class, [
                'attr' => [
                    'placeholder' => 'Entrer le nom du dossier',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Un nom de dossier est requis !'
                    ])
                ],
            ])
            ->add('format', ChoiceType::class, [
                'choices' => [
                    "---Choisir---" => null,
                    "Physique" => "Physique",
                    "Numérique" => "Numérique",
                    "Mixte" => "Mixte",
                ],
                'attr' => [
                    'class' => 'format-choices'
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'Un format de dossier est requis !'
                    ])
                ],
            ])
            ->add('departement', EntityType::class, [
                'class' => Departement::class,
                'label' => false,
                'choice_label' => 'libelle_dep',
                'placeholder' => 'Rechercher un département',
                'attr' => [
                    'class' => 'd-none searchable-select-list',
                ],
                'constraints' => [
                    new Callback([$this, 'validateCategory']),
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    "Disponible" => 1,
                    "Indisponible" => 0,
                ],
                'attr' => [
                    'class' => 'statut-choices'
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'Un format de dossier est requis !'
                    ])
                ],
            ])
            ->add('tags',  HiddenType::class, [
                'attr' => [
                    'class' => 'dossier-tags-input',
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
            //->add('date_creation', DateType::class, [
            //    'attr' => [
            //        'class' => 'd-none date-creation-input',
            //        'mapped' => true
            //    ],
            //])
        ;
        $builder->get('format')
           ->addModelTransformer(new CallbackTransformer(
            function ($formatArray) {
                return $formatArray;
            },
            function ($formatString) {
                return $formatString;
            }
        ));
        $builder->get('statut')
        ->addModelTransformer(new CallbackTransformer(
         function ($statutArray) {
             return $statutArray;
         },
         function ($statutInt) {
             return $statutInt;
         }
     ));
    }

    public function validateCategory($value, ExecutionContextInterface $context)
    {
        if ($value === null) {
            $context->buildViolation('Le choix d\'un département pour votre dossier est requis !')
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dossier::class,
        ]);
    }
}
