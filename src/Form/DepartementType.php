<?php

namespace App\Form;

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

class DepartementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle_dep', TextType::class, [
                'attr' => [
                    'placeholder' => 'Entrer le nom du département',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Un nom de département est requis !'
                    ])
                ],
            ])
            ->add('departement_parent', EntityType::class, [
                'class' => Departement::class,
                'label' => false,
                'required' => false,
                'choice_label' => 'libelle_dep',
                'placeholder' => 'Rechercher un département',
                'attr' => [
                    'class' => 'd-none searchable-select-list',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Departement::class,
        ]);
    }
}
