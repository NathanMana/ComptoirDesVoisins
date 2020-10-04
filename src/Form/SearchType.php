<?php

namespace App\Form;

use App\ViewModel\SearchData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;

class SearchType extends AbstractType 
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder 
            ->add('q', TextType::class, [
                'label'=>false,
                'required'=>false,
                'attr'=>[
                    'placeholder'=>'Rechercher'
                ]
            ])
            ->add('groceryType', ChoiceType::class, [
                'required'=>false,
                'label'=>false,
                'choices'=>[
                    "Supermarché"=>"Supermarché",
                    "Boulangerie"=>"Boulangerie",
                    "Boucherie"=>"Boucherie",
                    "Pharmacie"=>"Pharmacie",
                    "Epicerie"=>"Epicerie",
                    "Papeterie"=>"Papeterie",
                    "Autre"=>"Autre"
                ],
                "expanded"=>true,
                "multiple"=>true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'method'=>"GET",
            'csrf_protection'=> false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}


?>