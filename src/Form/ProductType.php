<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('size', IntegerType::class,  ['required' => true] )
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => false
            ])
            ->add('save', SubmitType::class, ['label' => 'Add New Product'])
            ->add('image', FileType::class, [
                'label' => 'Product Image',

                // unmapped means the field is not associated with any entity property
                'mapped' => false,

                // optional so the reupload does not happen every time
                // we edit the product details
                'required' => false,

                // unmapped fields can't define their validation using
                // attributes in the associated entity, so we define constraint classes
                'constraints' => [
                    new Assert\File(
                        maxSize: '2M',
                        extensions: ['jpg', 'png', 'avif'],
                        extensionsMessage: 'Please upload a valid JPG image',
                    )
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
