<?php

namespace App\Form;

use App\Entity\Mouvement;
use App\Entity\Fichier;
use App\Entity\Dossier;
use App\Entity\BoitePhysique;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MouvementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_mouvement', ChoiceType::class, [
                'label' => 'Type de mouvement',
                'choices' => [
                    'Arrivage' => 'arrivage',
                    'Prêt' => 'pret',
                    'Réintégration' => 'reintegration',
                    'Consultation sur place' => 'consultation',
                    'Déplacement' => 'deplacement',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('date_mouvement', DateTimeType::class, [
                'label' => 'Date et heure du mouvement',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('fichier', EntityType::class, [
                'class' => Fichier::class,
                'choice_label' => 'libelle_fichier',
                'label' => 'Fichier concerné',
                'required' => false,
                'placeholder' => 'Sélectionner un fichier (optionnel)',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('dossier', EntityType::class, [
                'class' => Dossier::class,
                'choice_label' => 'libelle_dossier',
                'label' => 'Dossier concerné',
                'required' => false,
                'placeholder' => 'Sélectionner un dossier (optionnel)',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('boite_destination', EntityType::class, [
                'class' => BoitePhysique::class,
                'choice_label' => function(BoitePhysique $boite) {
                    return $boite->getCodeBoite() . ' - ' . $boite->getLibelle() . 
                           ($boite->getLocalisation() ? ' (' . $boite->getLocalisation() . ')' : '');
                },
                'label' => 'Boîte de destination',
                'required' => false,
                'placeholder' => 'Sélectionner une boîte (optionnel)',
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('emprunteur_nom', TextType::class, [
                'label' => 'Nom de l\'emprunteur',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Pour les prêts uniquement',
                    'class' => 'form-control',
                ],
            ])
            ->add('date_retour_prevue', DateType::class, [
                'label' => 'Date de retour prévue',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('observations', TextareaType::class, [
                'label' => 'Observations',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Commentaires ou notes sur ce mouvement',
                    'class' => 'form-control',
                    'rows' => 3,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mouvement::class,
        ]);
    }
}
