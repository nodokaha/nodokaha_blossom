<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GardenRepository;
use App\Repository\TileRepository;
use App\Service\TileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/my-garden/{userId}', name: 'app_tile_')]
final class TileController extends AbstractController
{
    #[Route('/field', name: 'field', methods: ['GET'])]
    public function field(
        int $userId,
        GardenRepository $gardenRepository,
        TileService $tileService,
    ): Response {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $userId) {
            throw $this->createAccessDeniedException();
        }

        $gardens = $gardenRepository->findByOwnerId($userId);
        $primaryGarden = $gardens[0] ?? null;

        if (!$primaryGarden) {
            throw $this->createNotFoundException();
        }

        $tiles = $tileService->getTilesByGarden($primaryGarden->getId() ?? 0);
        $dimensions = $tileService->getGardenDimensions($primaryGarden->getId() ?? 0);

        return $this->render('tile/field.html.twig', [
            'garden' => $primaryGarden,
            'tiles' => $tiles,
            'dimensions' => $dimensions,
        ]);
    }

    #[Route('/field/json', name: 'field_json', methods: ['GET'])]
    public function fieldJson(
        int $userId,
        GardenRepository $gardenRepository,
        TileService $tileService,
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $userId) {
            throw $this->createAccessDeniedException();
        }

        $gardens = $gardenRepository->findByOwnerId($userId);
        $primaryGarden = $gardens[0] ?? null;

        if (!$primaryGarden) {
            throw $this->createNotFoundException();
        }

        $gardenId = $primaryGarden->getId() ?? 0;
        $tiles = $tileService->getTilesByGarden($gardenId);
        $dimensions = $tileService->getGardenDimensions($gardenId);

        $tileData = array_map(fn($tile) => [
            'id' => $tile->getId(),
            'x' => $tile->getX(),
            'y' => $tile->getY(),
            'role' => $tile->getRole(),
            'stack_data' => $tile->getStackData(),
            'stack_state' => $tile->getStackState(),
        ], $tiles);

        return $this->json([
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'tiles' => $tileData,
        ]);
    }

    #[Route('/tile/{tileId}/stack', name: 'tile_stack_update', methods: ['POST'])]
    public function updateTileStack(
        int $userId,
        int $tileId,
        Request $request,
        TileRepository $tileRepository,
        TileService $tileService,
        GardenRepository $gardenRepository,
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $userId) {
            throw $this->createAccessDeniedException();
        }

        $gardens = $gardenRepository->findByOwnerId($userId);
        $primaryGarden = $gardens[0] ?? null;

        if (!$primaryGarden) {
            throw $this->createNotFoundException();
        }

        $tile = $tileRepository->find($tileId);
        if (!$tile || $tile->getGarden()->getId() !== $primaryGarden->getId()) {
            throw $this->createNotFoundException();
        }

        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data) || !isset($data['stack_data'])) {
            return $this->json(['error' => 'Invalid request'], 400);
        }

        $stackData = $data['stack_data'];
        if (!is_array($stackData)) {
            return $this->json(['error' => 'stack_data must be an array'], 400);
        }

        try {
            $tileService->updateTileStack($tile, $stackData);

            return $this->json([
                'success' => true,
                'tile_id' => $tile->getId(),
                'stack_data' => $tile->getStackData(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/tile/{tileId}', name: 'tile_view', methods: ['GET'])]
    public function viewTile(
        int $userId,
        int $tileId,
        TileRepository $tileRepository,
        GardenRepository $gardenRepository,
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getId() !== $userId) {
            throw $this->createAccessDeniedException();
        }

        $gardens = $gardenRepository->findByOwnerId($userId);
        $primaryGarden = $gardens[0] ?? null;

        if (!$primaryGarden) {
            throw $this->createNotFoundException();
        }

        $tile = $tileRepository->find($tileId);
        if (!$tile || $tile->getGarden()->getId() !== $primaryGarden->getId()) {
            throw $this->createNotFoundException();
        }

        return $this->json([
            'id' => $tile->getId(),
            'x' => $tile->getX(),
            'y' => $tile->getY(),
            'role' => $tile->getRole(),
            'stack_data' => $tile->getStackData(),
            'stack_state' => $tile->getStackState(),
            'created_at' => $tile->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $tile->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
