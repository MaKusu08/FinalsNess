<?php

namespace App\Form;

use App\Entity\AdminReservations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminReservationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Customer_Name')
            ->add('Contact_Number')
            ->add('Reservation_Date')
            ->add('Start_Time')
            ->add('End_Time')
            ->add('Guests')
            ->add('Total_Amount')
            ->add('Payment_Status')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdminReservations::class,
        ]);
    }
}
