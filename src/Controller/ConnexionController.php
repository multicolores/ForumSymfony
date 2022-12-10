<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use VictorPrdh\RecaptchaBundle\Form\ReCaptchaType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ConnexionController extends AbstractController
{
    /**
     * @Route("/inscription", name="inscription_user", methods={"GET", "POST"})
     */
    public function inscriptionUser(Request $request,  MailerInterface $mailer, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {

        $errorState = false;
        $errorMessage = "";
        $form = $this->createFormBuilder()
            ->add('email', TextType::class, ['label' => 'Email*',])
            ->add('password', PasswordType::class, [
                'label' => 'Password*',
                'constraints' => new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
                    'message' => 'Le mot de passe doit avoir au minimum 8 charactère et au moins une lettre et un nombre'
                ]),
            ])
            ->add('password2', PasswordType::class, [
                'label' => 'Confirm password*',
                'constraints' => new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
                    'message' => 'Le mot de passe doit avoir au minimum 8 charactère et au moins une lettre et un nombre'
                ]),
            ])
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
                'constraints' => new Assert\Regex([
                    'pattern' => '/^[0-9]*$/',
                    'message' => 'Age doit être un nombre'
                ]),
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
            ->add("recaptcha", ReCaptchaType::class)

            ->add('save', SubmitType::class, ['label' => 'Inscription'])
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form["password"]->getData() === $form["password2"]->getData()) {
                // Send email to user
                $email = (new Email())
                    ->from('florian.tellier02@gmail.com')
                    ->to($form["email"]->getData())
                    ->subject('Inscription au Forum !')
                    ->text("Bonjour" . $form["pseudo"]->getData())
                    ->html('<h1>Bonjour ' . $form["pseudo"]->getData() . '!</h1><p>Merci de corfirmer votre inscription au forum en cliquant sur ce lien : <a href="http://localhost:8000/confirmation/' . $form["pseudo"]->getData() . '">Confirmer mon inscription </a> ( attention vous n\' avez que 24 heures )</p>');

                $mailer->send($email);

                //Add user in database with confirmed value to false
                $user = new User();
                $user->setEmail($form["email"]->getData())
                    ->setPseudo($form["pseudo"]->getData())
                    ->setNom($form["nom"]->getData())
                    ->setAge($form["age"]->getData())
                    ->setPrenom($form["prenom"]->getData())
                    ->setVille($form["ville"]->getData())
                    ->setTel($form["tel"]->getData())
                    ->setConfirmed(false);

                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form["password"]->getData()
                );
                $user->setPassword($hashedPassword);

                $entityManager = $doctrine->getManager();

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('email_send');
            } else {
                $errorState = true;
                $errorMessage = "Attention les mots de passes renseignés ne sont pas identiques !";
            }
        }

        return $this->render('/connexion/inscription.html.twig', [
            'loginForm' => $form->createView(),
            'erreur' => $errorState,
            'errorMessage' => $errorMessage
        ]);
    }

    /**
     * @Route("/emailsend/", name="email_send")
     */
    public function emailSend(): Response
    {
        return $this->render('/connexion/emailsend.html.twig');
    }

    /**
     * @Route("/confirmation/{pseudo}", name="email_confirm")
     */
    public function confirmEmail($pseudo, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['pseudo' => $pseudo]);
        $error = false;
        if (!$user) {
            $error = true;

            return $this->render('/connexion/confirmation.html.twig', [
                'error' => $error,
            ]);
        } else {
            $user->setConfirmed(true);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->render('/connexion/confirmation.html.twig', [
                'error' => $error,
                'userPseudo' => $user->getPseudo(),
            ]);
        }
    }


    #[Route('/login', name: 'app_login')]
    public function loginUser(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        //todo on error return to "/" et faire le déconte du nombre de tentative et l'attente
        return $this->render('connexion/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {
    }
}
