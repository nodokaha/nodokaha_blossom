<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Service\NumberGeneratorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LuckyController extends AbstractController
{
    public function __construct(
        private NumberGeneratorService $numberGenerator,
    ) {}

    #[Route('/lucky/number')]
    public function number(): Response
    {
        $number = $this->numberGenerator->generateRandomNumber();

        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }
}
