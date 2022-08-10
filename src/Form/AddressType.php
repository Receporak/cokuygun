<?php

namespace App\Form;

use App\Entity\Brand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',null,[
                'required'=>true
            ])
            ->add('surname',null,[
                'required'=>true
            ])
            ->add('city',null,[
                'required'=>true
            ])
            ->add('district',null,[
                'required'=>true
            ])
            ->add('fullAddress',TextareaType::class,[
                'attr' => [
                    "class"=>"form-control" ,
                    "placeholder"=>"Sokak, Mahalle, No, Daire, İlçe/İl"
                ],
                'required'=>true
            ])
            ->add('phone',null,[
                'required'=>true
            ])
            ->add('email',null,[
                'required'=>true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
