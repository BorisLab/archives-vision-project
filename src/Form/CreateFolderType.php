<?php

namespace App\Form;

use App\Entity\Dossier;
use App\Entity\Departement;
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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateFolderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle_dossier', TextType::class, [
                'attr' => [
                    'placeholder' => 'Entrez le nom du dossier',
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
                'placeholder' => 'Recherchez un département',
                'attr' => [
                    'class' => 'd-none searchable-select-list',
                ],
                'constraints' => [
                    new Callback([$this, 'validateCategory']),
                ],
            ])
            ->add('tags',  HiddenType::class, [
                'attr' => [
                    'class' => 'tags-input',
                    'mapped' => true
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
