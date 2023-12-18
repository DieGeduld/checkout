<?php

namespace App\Controller;

use App\Entity\Address;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\Type\AddressType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Type\AddressSelectType;
use App\Entity\Country;
use Symfony\Component\HttpFoundation\JsonResponse;

class AddressController extends AbstractController
{

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/address', name: 'app_address')]
    public function index(Request $request): Response
    {
        $address = new Address(); // Create a new Address entity instance

        $user = $this->getUser(); 

        $form = $this->createForm(AddressSelectType::class, null, [
            'current_address' => $user->getCurrentAddress(),
        ]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TOTO: unsauber!
            $address = $form->getData()["address"];

            $user->setCurrentAddress($address);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', 'Adresse wurde erfolgreich ausgewählt');


            ###ändern:
            return $this->render('shop/deliveryaddress.html.twig', [
                'currentAddress' => $address,
                'form' => $form,
            ]);
        }
   
        return $this->render('address/index.html.twig', [
            'controller_name' => 'AddressController',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/address/create', name: 'app_address_create')]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $address = new Address(); // Create a new Address entity instance
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->getData();
            $address->setUserId($this->getUser());

            $this->entityManager->persist($address);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_shop_deliveryaddress');
        }  


        return $this->render('address/create.html.twig', [
            'controller_name' => 'AddressController',
            'form' => $form->createView(),
        ]);
        
    }

    #[Route('/address/edit/{id}', name: 'app_address_edit')]
    public function edit(Request $request, Address $address): Response
    {
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);
        $address = $form->getData();
        $address->setUserId($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($address);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_shop_deliveryaddress');
        }

        return $this->render('address/edit.html.twig', [
            'controller_name' => 'AddressController',
            'form' => $form->createView(),
        ]);
    }


     #[Route('/address/check-country', name: 'check_country',  methods: ['POST'])]
    public function checkCountry(Request $request): JsonResponse
    {
        $countryId = $request->request->get('countryId');
        $isEu = $this->entityManager->getRepository(Country::class)->find($countryId)->isEU();

        return new JsonResponse(['isEu' => $isEu]);
    }


}
