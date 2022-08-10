<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoryList = $options["data"]->categoryList;

        $builder
            ->add('image', FileType::class, [
                'label' => 'Ürün Resmi',
                'mapped' => false,
                'required' => !isset($options["data"]->isUpdate),
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'png' => 'image/png',
                            'jpeg' => 'image/jpeg',
                            'jpg' => 'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'jpeg/jpg/png formatında bir resim yükleyiniz',
                    ])
                ],
            ])
            // Görselin değişip değişmediğini anlamak için hidden input kullanıldı.
            ->add('oldImage',HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('name', null, [
                'label' => 'Ürün adı',
                'attr'=> [
                    'class' => 'form-control',
                ],
            ])
            ->add('description', null, [
                'label' => 'Açıklama',
                'attr'=> [
                    'class' => 'form-control',
                ],
            ])
            ->add('price', null, [
                'label' => 'Fiyat',
                'attr'=> [
                    'class' => 'form-control',
                ],
            ])
            ->add('stock', null, [
                'label' => 'Ürün Stok Adedi',
                'attr'=> [
                    'class' => 'form-control',
                ],
            ])
            ->add('category', null, [
                'label' => 'Kategori',
                'attr'=> [
                    'class' => 'form-control select2',
                    'multiple'=>"true",
                    'style'=>"height: 100px!important;",
                ],
                'choices' =>$options["data"]->categoryList,
                'choice_label' => function ($category) use ($categoryList) {
                    return array_search($category, $categoryList)??$category->getName();
                },
                'mapped'=>false,
                'required'=>true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
