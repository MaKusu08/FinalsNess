<?php

namespace App\Form;

use App\Entity\AdminMovies;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class AdminMoviesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Movie_Name')
            ->add('Movie_Description')

            // ✅ IMAGE UPLOAD FIELD
            ->add('Movie_Image', FileType::class, [
                'label' => 'Movie Image',
                'mapped' => false,        // IMPORTANT
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPG, PNG, WEBP)',
                    ])
                ],
            ])

            ->add('Movie_Duration')
            ->add('Movie_Price');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdminMovies::class,
        ]);
    }
}
