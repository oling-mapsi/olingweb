<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/users')]
class UserAdminController extends AbstractController
{
    #[Route('', name: 'admin_users_index', methods: ['GET'])]
    #[Route('/', name: 'admin_users_index_slash', methods: ['GET'])]
    public function index(UserRepository $repository): Response
    {
        return $this->render('admin/users/index.html.twig', [
            'users' => $repository->findBy([], ['id' => 'DESC']),
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_users_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $form = $this->createForm(UserType::class, $user, [
            'allow_roles' => $this->isGranted('ROLE_SUPER_ADMIN'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $this->generateComplexPassword();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            try {
                $this->sendCredentialsEmail($user, $plainPassword, $mailer, 'Vos accès admin OLING');
                $this->addFlash('success', 'Utilisateur créé. Identifiants envoyés par email.');
            } catch (TransportExceptionInterface $exception) {
                $this->addFlash('warning', 'Utilisateur créé, mais l’envoi de l’email a échoué.');
            }

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/new.html.twig', [
            'form' => $form,
            'user' => $user,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_users_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        UserRepository $repository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('admin_users_index');
        }

        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $currentUser = $this->getUser();
            if (!$currentUser instanceof User || $currentUser->getId() !== $user->getId()) {
                throw $this->createAccessDeniedException();
            }
        }

        $form = $this->createForm(UserType::class, $user, [
            'allow_roles' => $this->isGranted('ROLE_SUPER_ADMIN'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur mis à jour.');

            return $this->redirectToRoute('admin_users_index');
        }

        $currentUser = $this->getUser();
        $isSelf = $currentUser instanceof User && $currentUser->getId() === $user->getId();

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form,
            'user' => $user,
            'is_self' => $isSelf,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/reset-password', name: 'admin_users_reset_password', methods: ['POST'])]
    public function resetPassword(
        int $id,
        UserRepository $repository,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('admin_users_index');
        }

        if (!$this->isCsrfTokenValid('reset-password'.$user->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton invalide.');
            return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
        }

        $plainPassword = $this->generateComplexPassword();
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $entityManager->flush();

        try {
            $this->sendCredentialsEmail($user, $plainPassword, $mailer, 'Nouveau mot de passe admin OLING');
            $this->addFlash('success', 'Nouveau mot de passe envoyé par email.');
        } catch (TransportExceptionInterface $exception) {
            $this->addFlash('warning', 'Mot de passe mis à jour, mais l’envoi de l’email a échoué.');
        }

        return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
    }

    #[Route('/{id}/delete', name: 'admin_users_delete', methods: ['POST'])]
    public function delete(
        int $id,
        UserRepository $repository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $user = $repository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('admin_users_index');
        }

        $currentUser = $this->getUser();
        if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
            $this->addFlash('warning', 'La suppression du compte connecté est désactivée.');
            return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('admin_users_index');
    }

    private function generateComplexPassword(int $length = 14): string
    {
        $length = max(12, $length);

        $lower = 'abcdefghijkmnopqrstuvwxyz';
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';
        $symbols = '!@#$%&*?';
        $all = $lower.$upper.$digits.$symbols;

        $password = [
            $lower[random_int(0, strlen($lower) - 1)],
            $upper[random_int(0, strlen($upper) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        for ($i = count($password); $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        for ($i = count($password) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $tmp = $password[$i];
            $password[$i] = $password[$j];
            $password[$j] = $tmp;
        }

        return implode('', $password);
    }

    private function sendCredentialsEmail(User $user, string $plainPassword, MailerInterface $mailer, string $subject): void
    {
        $email = (new Email())
            ->from('florestan.rouet@oling.fr')
            ->to($user->getEmail())
            ->subject($subject)
            ->text(
                "Bonjour,\n\n".
                "Voici vos accès à l’administration OLING :\n".
                "Identifiant : {$user->getUsername()}\n".
                "Mot de passe : {$plainPassword}\n\n".
                "Connectez-vous sur /login.\n"
            );

        $mailer->send($email);
    }
}
