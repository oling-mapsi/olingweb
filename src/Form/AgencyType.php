<?php

namespace App\Form;

use App\Entity\Agency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l’agence',
            ])
            ->add('streetLine1', TextType::class, [
                'label' => 'Adresse (ligne 1)',
            ])
            ->add('streetLine2', TextType::class, [
                'label' => 'Adresse (ligne 2)',
                'required' => false,
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays (affichage)',
            ])
            ->add('countryCode', TextType::class, [
                'label' => 'Code pays (JSON-LD)',
                'help' => 'Ex : FR, GP, MQ',
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Ordre d’affichage',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agency::class,
        ]);
    }
}
