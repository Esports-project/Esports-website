<?php

namespace App\Form;

use App\Entity\Classement;
use App\Entity\Equipe;
use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ClassementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rang')
            ->add('equipe', EntityType::class, [
                'class' => Equipe::class,
                'mapped' => false,
                'choice_label' => function($nom){
                    return $nom->getNom();
                },
            ])
            ->add('evenement', EntityType::class, [
                'class' => Evenement::class,
                'choice_label' => function($nom){
                    return $nom->getNom();
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Classement::class,
        ]);
    }
}
