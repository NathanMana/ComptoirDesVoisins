<?php

namespace App\Form;

use App\ViewModel\Security\ProfileViewModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required'=>false
            ])
            ->add('lastname', TextType::class, [
                'required'=>false
            ])
            ->add('city', TextType::class, [
                'required'=>false
            ])
            ->add('phone', TextType::class, [
                'required'=>false
            ])
            ->add('imageFile', FileType::class, [
                'required'=>false
            ])
            ->add('codeCity', HiddenType::class, [
                'required'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfileViewModel::class,
            'method'=>"POST",
            'csrf_protection'=> true
        ]);
    }

}
