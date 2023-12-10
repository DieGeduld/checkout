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

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AddressType extends AbstractType
{
    private $loggedin;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder->add("firstName", TextType::class, [
            "label"=> "First Name",
            "required"=> true,
            'attr' => ['class' => 'form-control']
        ])
        ->add("lastName", TextType::class, [
            "label"=> "Last Name",
            "required"=> true,
            'attr' => ['class' => 'form-control']
        ])
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
            'choice_label' => 'name',
            'placeholder' => 'Wählen Sie ein Land',
            'attr' => ['class' => 'form-control'],
            'choice_attr' => function($country) {
                return $country->isEU() ? ['attr-isEu' => '1'] : ['attr-isEu' => '0'];
            },
        ])
        ->add('taxNumber', TextType::class, [
            'attr' => ['class' => 'form-control'],
        ])
        ->add('telephone', TextType::class, [
            'attr' => ['class' => 'form-control'],
        ])
        ->add('email', TextType::class, [
            'attr' => ['class' => 'form-control'],
        ])
        ->add('submit', SubmitType::class, [
            'label'=> 'Weiter',
            'attr' => ['class' => 'form-control btn btn-primary btn-block'],
        ]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class
        ]);
    }
}