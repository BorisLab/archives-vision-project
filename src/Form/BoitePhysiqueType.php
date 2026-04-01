<?php

namespace App\Form;

use App\Entity\BoitePhysique;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoitePhysiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code_boite', TextType::class, [
                'label' => 'Code de la boîte',
                'attr' => [
                    'placeholder' => 'Ex: BP-2024-001',
                    'class' => 'form-control',
                ],
            ])
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'attr' => [
                    'placeholder' => 'Ex: Boîte Archives Administratives 2024',
                    'class' => 'form-control',
                ],
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Armoire A - Étagère 1',
                    'class' => 'form-control',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description détaillée du contenu de la boîte',
                    'class' => 'form-control',
                    'rows' => 4,
                ],
            ])
            ->add('capacite_max', IntegerType::class, [
                'label' => 'Capacité maximale',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nombre max de fichiers',
                    'class' => 'form-control',
                    'min' => 1,
                ],
            ])
            ->add('statut', CheckboxType::class, [
                'label' => 'Boîte active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BoitePhysique::class,
        ]);
    }
}
