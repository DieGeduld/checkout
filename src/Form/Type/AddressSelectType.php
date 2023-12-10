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
use Doctrine\ORM\EntityRepository;



class AddressSelectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $currentAddress = $options['current_address'] ?? null;

        $builder
        ->add('address', EntityType::class, [
            'class' => Address::class,
            'choice_label' => function ($address, $key, $value) {
                // Customize how the choice is displayed
                return $address->getStreet() . ' ' . $address->getNumber() .  ', ' . $address->getCity() . ' ' . $address->getZip(); // Example
            },
            'query_builder' => function (EntityRepository $er) use ($currentAddress) {
                $queryBuilder = $er->createQueryBuilder('a')
                    ->orderBy('a.street', 'ASC');
                return $queryBuilder;
            },
            'data' => $currentAddress,
        ])
        ->add('submit', SubmitType::class, [
            'label'=> 'Address wählen',
            'attr' => ['class' => 'btn btn-primary btn-block'],
        ]); 

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'current_address' => null,
        ]);
    }
}