<?php

namespace App\Form;

use App\Entity\HomeAwardItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomeAwardItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('subtitle', TextType::class, [
                'label' => 'Sous-titre',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Contenu',
                'required' => false,
                'attr' => ['rows' => 5, 'class' => 'form-control js-wysiwyg'],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Ordre',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isOpen', CheckboxType::class, [
                'label' => 'Ouvert par défaut',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HomeAwardItem::class,
        ]);
    }
}
