<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class HomeController extends AbstractController
{


    #[Route('/{errorMessage}', name: 'home', defaults: ['errorMessage' => "noError"])]
    public function home($errorMessage, ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        // $entityManager = $doctrine->getManager();
        // $theme = new Theme();

        // $theme->setName("nom7")
        //     ->setStatus(true);
        // $entityManager->persist($theme);
        // $entityManager->flush();

        $repository = $doctrine->getRepository(Theme::class);
        $donnees = $repository->findAll();

        $themes = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('home/home.html.twig', [
            'themes' => $themes,
        ]);
    }

    #[Route('/admin/create-theme', name: 'create_theme',)]
    public function createTheme(ManagerRegistry $doctrine, Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('nom', TextType::class, ['label' => 'Nom du theme',])
            ->add('save', SubmitType::class, ['label' => 'Créer le theme'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $theme = new Theme();
            $theme->setName($form["nom"]->getData())
                ->setStatus(true);

            $entityManager = $doctrine->getManager();

            $entityManager->persist($theme);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }


        return $this->render('home/create-theme.html.twig', [
            'formulaire' => $form->createView(),
        ]);
    }
}
