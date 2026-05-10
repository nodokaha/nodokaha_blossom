<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $message = null;

        if ($request->isMethod('POST')) {
            $email = mb_strtolower(trim((string) $request->request->get('email')));
            $plainPassword = (string) $request->request->get('password');

            if ($email === '' || $plainPassword === '') {
                $message = 'メールアドレスとパスワードは必須です。';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'メールアドレスの形式が正しくありません。';
            } elseif (mb_strlen($plainPassword) < 8) {
                $message = 'パスワードは8文字以上にしてください。';
            } elseif ($userRepository->findOneBy(['email' => $email]) !== null) {
                $message = 'このメールアドレスはすでに登録されています。';
            } else {
                $user = new User();
                $user->setEmail($email);
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

                $entityManager->persist($user);
                $entityManager->flush();

                $message = 'ユーザーを作成しました。';
            }
        }

        return $this->render('registration/register.html.twig', [
            'message' => $message,
        ]);
    }
}
