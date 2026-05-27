<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\AssetUploadType;
use App\Repository\AssetFileRepository;
use App\Service\Asset\AssetStorageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/basisvr/cdn')]
class AssetController extends AbstractController
{
    #[Route('', name: 'basisvr_asset_index', methods: ['GET'])]
    public function index(AssetFileRepository $assetFileRepository): Response
    {
        return $this->render('asset/index.html.twig', [
            'files' => $assetFileRepository->findRecent(),
        ]);
    }

    #[Route('/upload', name: 'basisvr_asset_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request, AssetStorageService $assetStorageService, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AssetUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploaded = $form->get('asset')->getData();
            if ($uploaded !== null) {
                $asset = $assetStorageService->store($uploaded);
                $entityManager->persist($asset);
                $entityManager->flush();

                return $this->redirectToRoute('basisvr_asset_index');
            }
        }

        return $this->render('asset/upload.html.twig', ['form' => $form]);
    }

    #[Route('/{storageKey}', name: 'basisvr_asset_serve', methods: ['GET'])]
    public function serve(string $storageKey, AssetFileRepository $assetFileRepository, AssetStorageService $assetStorageService): Response
    {
        $asset = $assetFileRepository->findOneBy(['storageKey' => $storageKey]);
        if ($asset === null) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($assetStorageService->resolvePath($asset->getStorageKey()));
        $response->headers->set('Content-Type', $asset->getMimeType());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $asset->getOriginalName());

        return $response;
    }
}
