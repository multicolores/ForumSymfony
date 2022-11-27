<?php

namespace App\Controller;

use App\Entity\Discussion;
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

class DiscussionController extends AbstractController
{

    /**
     * @Route("/discussion/{themeId}", name="discussion", methods={"GET", "POST"})
     */
    public function home(int $themeId, ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {

        // $repository = $doctrine->getRepository(Theme::class);
        // $theme = $repository->findOneBy(['id' => '1']);

        // $repository = $doctrine->getRepository(User::class);
        // $user = $repository->findOneBy(['id' => '1']); //a voir ca en regardant dans la db ^^ ( créer 2 vrai user pour utiliser plusieurs fois )

        // $entityManager = $doctrine->getManager();
        // $discussion = new Discussion();
        // $discussion->setUser($user);
        // $discussion->setTheme($theme);
        // $discussion->setText("5eme message de la discussion");
        // $discussion->setCreatedAt(time());

        // $entityManager->persist($discussion);
        // $entityManager->flush();

        $discussions = null;

        $repository = $doctrine->getRepository(Theme::class);
        $theme = $repository->findOneBy(['id' => $themeId]);
        $repository = $doctrine->getRepository(Discussion::class);
        $donnees = $repository->findBy(['theme' => $theme]);
        // var_dump($donnees);
        // $donnees = $repository->findAll();

        if ($donnees) {
            $discussions = $paginator->paginate(
                $donnees,
                $request->query->getInt('page', 1),
                10
            );
        }

        //todo : quand j'aurais fait la partie authentification : modifier le code d'après 
        $connectedUser = null;
        $repository = $doctrine->getRepository(User::class);
        $connectedUser = $repository->findOneBy(['id' => '1']);
        $newDiscussion = new Discussion();
        $form = $this->createFormBuilder($newDiscussion)
            ->add('text', TextType::class, ['label' => false])
            ->add('save', SubmitType::class, ['label' => 'Envoyer'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $repository = $doctrine->getRepository(Theme::class);
            $theme = $repository->findOneBy(['id' => $themeId]);

            $repository = $doctrine->getRepository(User::class);
            $user = $repository->findOneBy(['id' => $connectedUser->getId()]);

            $entityManager = $doctrine->getManager();
            $discussion = new Discussion();
            $discussion->setUser($user);
            $discussion->setTheme($theme);
            $discussion->setText($form["text"]->getData());
            $discussion->setCreatedAt(time());

            $entityManager->persist($discussion);
            $entityManager->flush();
            unset($form);
            unset($discussion);
            $newDiscussion = new Discussion();
            $form = $this->createFormBuilder($newDiscussion)
                ->add('text', TextType::class, ['label' => false])
                ->add('save', SubmitType::class, ['label' => 'Envoyer'])
                ->getForm();
        }



        return $this->render('/discussion/discussion.html.twig', [
            'discussions' => $discussions,
            'theme' => $theme,
            'connectedUser' => $connectedUser,
            'sendMessageForm' => $form->createView(),
        ]);
    }
}
