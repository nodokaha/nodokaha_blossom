<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AssetFile;
use App\Form\AssetUploadType;
use App\Repository\AssetFileRepository;
use App\Service\Asset\AssetStorageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            $encryptionKey = $form->get('encryptionKey')->getData();
            $assetType = $form->get('assetType')->getData();
            if ($uploaded !== null && $encryptionKey !== null && AssetFile::isValidAssetType($assetType)) {
                $asset = $assetStorageService->store($uploaded, $encryptionKey, $assetType);
                $entityManager->persist($asset);
                $entityManager->flush();

                return $this->render('asset/upload_success.html.twig', [
                    'asset' => $asset,
                    'downloadUrl' => $this->generateUrl('basisvr_asset_serve', ['storageKey' => $asset->getStorageKey()], UrlGeneratorInterface::ABSOLUTE_URL),
                ]);
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

    #[Route('/finalize', name: 'basisvr_asset_finalize', methods: ['POST'])]
    public function finalize(Request $request, EntityManagerInterface $entityManager, AssetStorageService $assetStorageService): Response
    {
        $data = json_decode($request->getContent() ?: '{}', true);
        if (! is_array($data)) {
            $data = [];
        }

        $filename = $data['filename'] ?? $request->request->get('filename');
        $encryptionKey = $data['encryptionKey'] ?? $request->request->get('encryptionKey');
        $assetType = $data['assetType'] ?? $request->request->get('assetType');

        if (! $filename || ! $encryptionKey || ! $assetType) {
            return new JsonResponse(['error' => 'missing_parameters'], 400);
        }

        if (! AssetFile::isValidAssetType($assetType)) {
            return new JsonResponse(['error' => 'invalid_asset_type'], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/tmp';
        $path = $uploadDir.'/'.$filename;

        if (!file_exists($path)) {
            return new JsonResponse(['error' => 'file_not_found'], 404);
        }

        $uploadedFile = new UploadedFile($path, $filename, null, null, true);
        $asset = $assetStorageService->store($uploadedFile, $encryptionKey, $assetType);
        $entityManager->persist($asset);
        $entityManager->flush();

        return new JsonResponse(['storageKey' => $asset->getStorageKey()]);
    }
}
