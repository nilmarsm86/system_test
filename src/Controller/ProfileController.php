<?php

namespace App\Controller;

use App\Form\ProfileEditFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig');
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository): Response
    {
        $form = $this->createForm(ProfileEditFormType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if(!empty($plainPassword)){
                $hashPassword = $userPasswordHasher->hashPassword($this->getUser(), $plainPassword);
                $userRepository->upgradePassword($this->getUser(), $hashPassword);
            }else{
                $entityManager->flush();
            }
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_profile');
        }

        return $this->renderForm('profile/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
