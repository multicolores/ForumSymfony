<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{

    /**
     * @Route("/home", name="home", methods={"GET", "POST"})
     */
    public function home(ManagerRegistry $doctrine): Response
    {
        // $entityManager = $doctrine->getManager();
        // $theme = new Theme();
        // $theme->setName("nom1")
        //     ->setStatus(true);
        // $entityManager->persist($theme);
        // $entityManager->flush();

        // $theme->setName("nom2")
        //     ->setStatus(false);
        // $entityManager->persist($theme);
        // $entityManager->flush();

        // $theme->setName("nom3")
        //     ->setStatus(true);
        // $entityManager->persist($theme);
        // $entityManager->flush();

        $repository = $doctrine->getRepository(Theme::class);
        $themes = $repository->findAll();

        return $this->render('home/home.html.twig', [
            'themes' => $themes,
        ]);
    }
}
