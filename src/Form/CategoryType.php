<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $parentCategoryList = $options["data"]->parentCategoryList;

        $builder
            ->add('name', null, [
                'label' => 'Kategori Adı',
                'attr' => [
                    'class' => 'form-control mt-3',
                ],
            ])
            ->add('orderNo', null, [
                'label' => 'Kategori listeme öncelik sırası',
                'attr' => [
                    'class' => 'form-control mt-3',
                ],
                "required" => true,
            ])
            ->add('parent', null, [
                'label' => 'Ana Kategori',
                'attr' => [
                    'class' => 'form-group select2 mt-3',
                ],
                'choices' => $options["data"]->parentCategoryList,
                'choice_label' => function ($category) use ($parentCategoryList) {
                    return array_search($category, $parentCategoryList) ?? $category->getName();
                },
            ])
            ->add('hasCampaign', null, [
                'label' => 'Kampanyalı Kategori mi?',
                'attr' => [
                    'class' => 'form-group col-1 mt-3',
                    'style'=>'float:left;',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
