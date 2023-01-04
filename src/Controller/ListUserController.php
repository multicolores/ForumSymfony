<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ListUserController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    /**
     * @Route("/admin/userlist", name="user_list", methods={"GET", "POST"})
     */
    public function user_list(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {

        $repository = $doctrine->getRepository(User::class);
        $donnees = $repository->findAll();

        $users = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('/userList/user-list.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/update/user", name="user_update", methods={"GET", "POST"})
     */
    public function user_update(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $connectedUser = $this->security->getUser();
        $repository = $doctrine->getRepository(User::class);
        $user = $repository->findOneBy(['id' => $connectedUser->getId()]);
        $form = $this->createFormBuilder($user)
            ->add('email', TextType::class, ['label' => 'Email*',])
            ->add('pseudo', TextType::class, ['label' => 'Pseudo*',])
            ->add('nom', TextType::class, [
                'required'   => false,
                'empty_data' => null,
            ])
            ->add('prenom', TextType::class, [
                'required'   => false,
                'empty_data' => null,
            ])
            ->add('age', TextType::class, [
                'required'   => false,
                'empty_data' => null,
            ])
            ->add('tel', TextType::class, [
                'required'   => false,
                'empty_data' => null,
            ])
            ->add('ville', ChoiceType::class, [
                'required'   => false,
                'empty_data' => null,
                'choices'  => [
                    '-' => null,
                    'Saint-Quentin' => 'Saint-Quentin',
                    'Paris' => 'Paris',
                    'Lyon' => 'Lyon',
                    'Lille' => 'Lille',
                ],
            ])

            ->add('save', SubmitType::class, ['label' => 'Modification'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmail($form["email"]->getData())
                ->setPseudo($form["pseudo"]->getData())
                ->setNom($form["nom"]->getData())
                ->setAge($form["age"]->getData())
                ->setPrenom($form["prenom"]->getData())
                ->setVille($form["ville"]->getData())
                ->setTel($form["tel"]->getData());

            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('/userList/update-user.html.twig', [
            'editForm' => $form->createView(),
        ]);
    }
}
