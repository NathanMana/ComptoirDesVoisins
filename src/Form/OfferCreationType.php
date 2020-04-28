<?php

namespace App\Form;

use DateTime;
use App\Entity\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OfferCreationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('citiesDelivery', HiddenType::class, [
                "required"=>true
            ])
            ->add('groceryType', ChoiceType::class, [
                "required"=>true, 
                "choices"=>[
                    "Supermarché"=>"Supermarché",
                    "Boulangerie"=>"Boulangerie",
                    "Boucherie"=>"Boucherie",
                    "Pharmacie"=>"Pharmacie",
                    "Epicerie"=>"Epicerie",
                    "Papeterie"=>"Papeterie",
                    "Autres"=>"Autres"
                ]
            ])
            ->add('message', TextareaType::class, [
                "required"=>true
            ])
            ->add('dateDelivery', DateTimeType::class, [
                "required"=>true
            ])
            ->add('limited', ChoiceType::class, [
                "required"=>true,
                "choices"=>[
                    1=>1,
                    2=>2,
                    3=>3,
                    4=>4
                ]
            ])
            ->add('communication', ChoiceType::class, [
                "choices"=>[
                    "Téléphone"=>true,
                    "Email"=>false
                ],
                "required"=>true
            ])
            ->add("code_cities", HiddenType::class,[
                "required"=>true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Offer::class,
        ]);
    }
}
