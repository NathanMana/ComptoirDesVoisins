<?php

namespace App\Form;

use DateTime;
use App\Entity\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OfferEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", TextType::class, [
                "required" => true
            ])
            ->add('message', TextareaType::class, [
                "required"=>true
            ])
            ->add('dateDelivery', DateType::class, [
                "required"=>true,
                'widget'=> "single_text"
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
            ->add('timezone', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Offer::class,
        ]);
    }
}
