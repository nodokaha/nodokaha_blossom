<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Form\AssetType;
use App\Repository\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/assets', name: 'asset_')]
class AssetController extends AbstractController
{
    private AssetRepository $repository;
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(AssetRepository $repository, EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $assets = $this->repository->findBy([], ['uploadedAt' => 'DESC']);

        return $this->render('asset/index.html.twig', [
            'assets' => $assets,
        ]);
    }

    #[Route('/type/{type}', name: 'type', methods: ['GET'])]
    public function byType(string $type): Response
    {
        if (!in_array($type, AssetType::getTypeChoices(), true)) {
            throw $this->createNotFoundException('不明なアセットタイプです。');
        }

        $assets = $this->repository->findByTypeOrdered($type);

        return $this->render('asset/index.html.twig', [
            'assets' => $assets,
            'selectedType' => $type,
        ]);
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $term = (string) $request->query->get('q', '');
        $assets = [];

        if ($term !== '') {
            $assets = $this->repository->search($term);
        }

        return $this->render('asset/search.html.twig', [
            'assets' => $assets,
            'term' => $term,
        ]);
    }

    #[Route('/upload', name: 'upload', methods: ['GET', 'POST'])]
    public function upload(Request $request): Response
    {
        $asset = new Asset();
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('uploadedFile')->getData();
            if ($uploadedFile) {
                $uploadDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/assets';
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0755, true);
                }

                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid(), $uploadedFile->guessExtension() ?? $uploadedFile->getClientOriginalExtension());

                try {
                    $uploadedFile->move($uploadDirectory, $newFilename);
                } catch (FileException $exception) {
                    $this->addFlash('error', 'ファイルのアップロード中にエラーが発生しました。');

                    return $this->redirectToRoute('asset_upload');
                }

                $asset->setFilename($newFilename);
                $asset->setFileSize($uploadedFile->getSize());
                $asset->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream');
                $asset->setUploadedAt(new \DateTimeImmutable());
                $asset->setUploadedBy($this->getUser()?->getUserIdentifier() ?? null);

                $this->entityManager->persist($asset);
                $this->entityManager->flush();

                $this->addFlash('success', 'アセットをアップロードしました。');

                return $this->redirectToRoute('asset_show', ['id' => $asset->getId()]);
            }
        }

        return $this->render('asset/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Asset $asset): Response
    {
        return $this->render('asset/show.html.twig', [
            'asset' => $asset,
        ]);
    }

    #[Route('/{id}/download', name: 'download', methods: ['GET'])]
    public function download(Asset $asset): BinaryFileResponse
    {
        $uploadDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/assets';
        $file = $uploadDirectory.'/'.$asset->getFilename();

        if (!file_exists($file)) {
            throw $this->createNotFoundException('アセットファイルが見つかりません。');
        }

        return $this->file($file, $asset->getName().'.'.pathinfo($file, PATHINFO_EXTENSION));
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Asset $asset): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$asset->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('asset_show', ['id' => $asset->getId()]);
        }

        $uploadDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/assets';
        $file = $uploadDirectory.'/'.$asset->getFilename();
        if (file_exists($file)) {
            @unlink($file);
        }

        $this->entityManager->remove($asset);
        $this->entityManager->flush();

        $this->addFlash('success', 'アセットを削除しました。');

        return $this->redirectToRoute('asset_index');
    }
}
