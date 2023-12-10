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
            'choice_label' => 'street',
            'query_builder' => function (EntityRepository $er) use ($currentAddress) {
                // Hier können Sie die Logik für die Abfrage definieren
                $queryBuilder = $er->createQueryBuilder('a')
                    ->orderBy('a.street', 'ASC');

                // Weitere Logik (falls erforderlich)

                return $queryBuilder;
            },
            'data' => $currentAddress,
        ]);

    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'current_address' => null,
        ]);
    }
}