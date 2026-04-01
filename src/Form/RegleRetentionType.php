<?php

namespace App\Form;

use App\Entity\RegleRetention;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegleRetentionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé de la règle',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Archives comptables',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le libellé est obligatoire']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('duree_conservation', IntegerType::class, [
                'label' => 'Durée de conservation (en années)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 10',
                    'min' => 1,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La durée de conservation est obligatoire']),
                    new Assert\Positive(['message' => 'La durée doit être positive']),
                ],
            ])
            ->add('base_legale', TextareaType::class, [
                'label' => 'Base légale',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Ex: Article L123-22 du Code de commerce',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La base légale est obligatoire']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Description complémentaire (optionnel)',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegleRetention::class,
        ]);
    }
}
