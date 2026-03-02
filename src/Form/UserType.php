<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $rolesDisabled = !$options['allow_roles'];

        $builder
            ->add('username', TextType::class, [
                'label' => 'Identifiant',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Super admin' => 'ROLE_SUPER_ADMIN',
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'disabled' => $rolesDisabled,
                'help' => $rolesDisabled ? 'Seul un super admin peut modifier les rôles.' : null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'allow_roles' => false,
        ]);

        $resolver->setAllowedTypes('allow_roles', 'bool');
    }
}
