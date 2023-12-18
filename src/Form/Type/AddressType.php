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
use Doctrine\ORM\EntityManagerInterface;


class AddressType extends AbstractType
{
    private $loggedin;
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder->setAttributes([
            'id' => 'address-form',
        ])
        ->add("firstName", TextType::class, [
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
            'row_attr' => [
                'class' => 'country_container',
                'id' => 'counry-container' 
            ],
        ])
        ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            // if ($data->getCountryId() != null ) {
            //     $isEu = $this->entityManager->getRepository(Country::class)->find($data->getCountryId())->isEU();

            //     if ($isEu) {
            // if ($data) {
            //     dd($data);
            // } 
            // if ($data && $data->getCountryId() == null) {
            //     dd($data->getCountryId());
            // }


            if (($data == null) || ($data && $data->getCountryId() == null) || $data && $data->getCountryId() && $data->getCountryId()->isEU()) {
                
                $form->add('taxNumber', TextType::class, [
                    'attr' => ['class' => 'form-control tax-number-field'],
                    'required' => false,
                    'row_attr' => [
                        'class' => 'tax_container',
                        'id' => 'tax-container' 
                    ],
                ]);
            } 
            // } 
        })
        // ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
        //     $data = $event->getData();
        //     $form = $event->getForm();

        //     // if (isset($data) && isset($data['country']) && is_numeric($data['country'])) {
        //     //     $isEu = $this->entityManager->getRepository(Country::class)->find($data['country'])->isEU();

        //     //     if ($isEu) {
        //             $form->add('taxNumber', TextType::class, [
        //                 'attr' => ['class' => 'form-control tax-number-field', 'style' => 'display: none;'],
        //                 'required' => false,
        //                 'row_attr' => [
        //                     'class' => 'tax_container',
        //                     'id' => 'tax-container' 
        //                 ],
        //             ]);
        //         // }
        //     // } 
        // })

        // ->add('taxNumber', TextType::class, [
        //     'attr' => ['class' => 'form-control'],
        //     'row_attr' => [
        //         'class' => 'tax_container',
        //         'id' => 'tax-container' 
        //     ],
        // ])
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


        // ->add('country', EntityType::class, [
        //     'class' => Country::class,
        //     'choice_label' => 'name',
        //     'placeholder' => 'Wählen Sie ein Land',
        //     'attr' => ['class' => 'form-control'],
        //     'choice_attr' => function($country) {
        //         return $country->isEU() ? ['attr-isEu' => '1'] : ['attr-isEu' => '0'];
        //     },
        // ])

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'attr' => ['id' => 'address-form'],
            'action' => '/shop/deliveryaddress',
        ]);
    }
}