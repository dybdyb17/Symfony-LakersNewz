<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_paris');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('pages/connexion.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;
        $success = false;
        $prenom = '';

        if ($request->isMethod('POST')) {
            $email = strip_tags(trim($request->request->get('email', '')));
            $password = $request->request->get('password', '');
            $confirmPassword = $request->request->get('confirmPassword', '');
            $pseudo = strip_tags(trim($request->request->get('pseudo', '')));
            $firstname = strip_tags(trim($request->request->get('prenom', '')));
            $lastname = strip_tags(trim($request->request->get('nom', '')));
            $dateNaissance = strip_tags(trim($request->request->get('dateNaissance', '')));
            $telephone = strip_tags(trim($request->request->get('telephone', '')));
            $lieuNaissance = strip_tags(trim($request->request->get('lieuNaissance', '')));

            if (!str_contains($email, '@')) {
                $error = 'Email invalide';
            } elseif (strlen($password) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caractères';
            } elseif ($password !== $confirmPassword) {
                $error = 'Les mots de passe ne correspondent pas';
            } elseif ($em->getRepository(\App\Entity\User::class)->findOneBy(['email' => $email])) {
                $error = 'Cet email est déjà utilisé';
            } else {
                $user = new \App\Entity\User();
                $user->setEmail($email);
                $user->setPassword($hasher->hashPassword($user, $password));
                $user->setPseudo($pseudo ?: null);
                $user->setFirstname($firstname ?: null);
                $user->setLastname($lastname ?: null);
                $user->setTelephone($telephone ?: null);
                $user->setLieuNaissance($lieuNaissance ?: null);

                if (!empty($dateNaissance)) {
                    $dateStr = str_replace([' ', '-'], ['', '/'], $dateNaissance);
                    $date = \DateTime::createFromFormat('d/m/Y', $dateStr);
                    if ($date) {
                        $user->setDateNaissance($date);
                    }
                }

                $em->persist($user);
                $em->flush();

                $success = true;
                $prenom = $firstname;
            }
        }

        return $this->render('pages/inscription.html.twig', [
            'error' => $error,
            'success' => $success,
            'prenom' => $prenom,
        ]);
    }
}
