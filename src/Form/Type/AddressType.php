<?php 

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Country;
use App\Entity\Address;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        // with bootstrap styles:
        $builder
            ->add('street', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('number', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('city', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('zip', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name', // Das Attribut der Country-Entity, das angezeigt wird
                // 'choice_value' => 'id', // Optional, wenn Sie den Wert anpassen möchten
                'placeholder' => 'Wählen Sie ein Land',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('telephone', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('email', TextType::class, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('submit', SubmitType::class, [
                'label'=> 'Address übernehmen und weiter',
                'attr' => ['class' => 'btn btn-primary btn-block'],
            ])
            
            ;
            // ->add('country', Country::class)

        
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}