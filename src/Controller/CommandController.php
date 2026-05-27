<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GardenRepository;
use App\Repository\CommandQueueRepository;
use App\Service\CommandQueueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/my-garden/{userId}', name: 'app_garden_')]
final class CommandController extends AbstractController
{
    #[Route('/commands', name: 'commands', methods: ['GET'])]
    public function listCommands(
        int $userId,
        GardenRepository $gardenRepository,
        CommandQueueRepository $commandQueueRepository,
    ): Response {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $userId) {
            throw $this->createAccessDeniedException();
        }

        $gardens = $gardenRepository->findByOwnerId($userId);
        $primaryGarden = $gardens[0] ?? null;

        if (!$primaryGarden) {
            throw $this->createNotFoundException('ガーデンが見つかりません');
        }

        $commands = $commandQueueRepository->findByUserAndGarden($userId, $primaryGarden->getId() ?? 0);

        return $this->render('command/list.html.twig', [
            'commands' => $commands,
            'garden' => $primaryGarden,
        ]);
    }

    #[Route('/command/add', name: 'command_add', methods: ['POST'])]
    public function addCommand(
        int $userId,
        Request $request,
        GardenRepository $gardenRepository,
        CommandQueueService $commandQueueService,
    ): Response {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $userId) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('command_add', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $gardens = $gardenRepository->findByOwnerId($userId);
        $primaryGarden = $gardens[0] ?? null;

        if (!$primaryGarden) {
            throw $this->createNotFoundException();
        }

        $opcode = (string) $request->request->get('opcode', '');
        $args = (string) $request->request->get('args', '');

        if (!$opcode) {
            $this->addFlash('error', 'opcodeを指定してください');

            return $this->redirectToRoute('app_garden_commands', ['userId' => $userId]);
        }

        $command = [
            'opcode' => strtoupper($opcode),
            'args' => $args,
        ];

        try {
            $commandQueueService->addCommand($currentUser, $primaryGarden, $command);
            $this->addFlash('success', '命令を追加しました');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_garden_commands', ['userId' => $userId]);
    }

    #[Route('/command/{commandId}/delete', name: 'command_delete', methods: ['POST'])]
    public function deleteCommand(
        int $userId,
        int $commandId,
        Request $request,
        CommandQueueRepository $commandQueueRepository,
        CommandQueueService $commandQueueService,
    ): Response {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $userId) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('command_delete_' . $commandId, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $command = $commandQueueRepository->find($commandId);
        if (!$command || $command->getUser()->getId() !== $userId) {
            throw $this->createNotFoundException();
        }

        try {
            $commandQueueService->deleteCommand($command);
            $this->addFlash('success', '命令を削除しました');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_garden_commands', ['userId' => $userId]);
    }

    #[Route('/command/{commandId}/view', name: 'command_view', methods: ['GET'])]
    public function viewCommand(
        int $userId,
        int $commandId,
        CommandQueueRepository $commandQueueRepository,
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $userId) {
            throw $this->createAccessDeniedException();
        }

        $command = $commandQueueRepository->find($commandId);
        if (!$command || $command->getUser()->getId() !== $userId) {
            throw $this->createNotFoundException();
        }

        return $this->json([
            'id' => $command->getId(),
            'opcode' => $command->getCommand()['opcode'] ?? '',
            'args' => $command->getCommand()['args'] ?? '',
            'status' => $command->getStatus(),
            'inserted_date' => $command->getInsertedDate()->format('Y-m-d'),
            'execution_week' => $command->getExecutionWeek(),
        ]);
    }
}
