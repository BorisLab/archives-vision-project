<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Entity\Departement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['placeholder' => 'Nom de l\'utilisateur']
            ])
            ->add('prenoms', TextType::class, [
                'attr' => ['placeholder' => 'Prénom(s) de l\'utilisateur']
            ])
            ->add('departement', EntityType::class, [
                    'class' => Departement::class,
                    'choice_label' => 'libelle_dep',
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    "Demandeur d'archives" => "ROLE_USER",
                    "Gestionnaire d'archives" => "ROLE_ARCHIVIST",
                    "Administrateur du système" => "ROLE_ADMIN",
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'Email de l\'utilisateur']
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Pour continuer, cochez cette case.',
                    ]),
                ],
            ])
            ->add('isDG', CheckboxType::class)
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Mot de passe de l\'utilisateur'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
        $builder->get('roles')
           ->addModelTransformer(new CallbackTransformer(
            function ($rolesArray) {
                return count($rolesArray)? $rolesArray[0]: null;
            },
            function ($rolesString) {
                return [$rolesString];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
