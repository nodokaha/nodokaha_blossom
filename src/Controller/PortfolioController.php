<?php

namespace App\Controller;

use App\Service\SecdVmService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PortfolioController extends AbstractController
{
    private const string DEFAULT_SECD_PROGRAM = <<<'SECD'
# レビュー草稿の評価を行う小さなSECDプログラム
LDC 13
LDC 21
ADD
ST review_score
LD review_score
LDC 30
GT
SEL LDC "公開: 読み直し完了"; OUT; JOIN | LDC "保留: もう一度検証"; OUT; JOIN
STOP
SECD;

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('portfolio/index.html.twig', [
            'links' => $this->reviewLinks(),
        ]);
    }

    #[Route('/tools/secd-vm', name: 'app_portfolio_secd_vm', methods: ['GET', 'POST'])]
    public function secdVm(Request $request, SecdVmService $secdVm): Response
    {
        $program = self::DEFAULT_SECD_PROGRAM;
        if ($request->isMethod('POST') && $this->isCsrfTokenValid('secd_vm_run', (string) $request->request->get('_token'))) {
            $program = (string) $request->request->get('program', self::DEFAULT_SECD_PROGRAM);
        }

        return $this->render('portfolio/secd_vm.html.twig', [
            'program' => $program,
            'result' => $secdVm->run($program),
            'examples' => [
                'arith' => "LDC 8\nLDC 5\nMUL\nOUT\nSTOP",
                'list' => "LDC [\"draft\",\"review\"]\nCAR\nOUT\nSTOP",
            ],
        ]);
    }

    #[Route('/tools/review-signal', name: 'app_portfolio_bloom_signal', methods: ['GET', 'POST'])]
    public function bloomSignal(Request $request): Response
    {
        $moves = $request->isMethod('POST') ? $request->request->all('moves') : ['outline', 'compare', 'quote', 'verdict'];
        $game = $this->scoreReviewSignal($moves);

        return $this->render('portfolio/bloom_signal.html.twig', [
            'moves' => $moves,
            'game' => $game,
            'move_catalog' => [
                'outline' => '構成: 記事の論点を並べ直す',
                'compare' => '比較: 似たプロジェクトとの差分を確認する',
                'quote' => '引用: 判断材料になるメモを抜き出す',
                'verdict' => '結論: 読者が次に取る行動を明確にする',
                'risk' => '懸念: 見落としやすい弱点を先に書く',
            ],
        ]);
    }

    /** @return list<array{route: string, label: string, description: string}> */
    private function reviewLinks(): array
    {
        return [
            ['route' => 'basisvr_event_index', 'label' => 'レビュー記事一覧', 'description' => 'プロジェクトの所感、変更理由、採用判断を読む'],
            ['route' => 'basisvr_event_new', 'label' => 'レビューを書く', 'description' => 'タイトル・レビュアー・本文だけで新しい見直し記事を公開'],
            ['route' => 'app_portfolio_secd_vm', 'label' => 'SECD VM', 'description' => 'レビュー草稿を評価するデモツールを実行'],
            ['route' => 'app_portfolio_bloom_signal', 'label' => 'REVIEW SIGNAL', 'description' => '論点整理のミニゲームで記事構成を試す'],
        ];
    }

    /**
     * @param list<string> $moves
     * @return array{score: int, bloom: int, resonance: int, drift: int, log: list<string>, ending: string}
     */
    private function scoreReviewSignal(array $moves): array
    {
        $bloom = 4;
        $resonance = 3;
        $drift = 2;
        $log = [];
        $previous = '';

        foreach (array_slice($moves, 0, 6) as $move) {
            [$bloomDelta, $resonanceDelta, $driftDelta, $message] = match ($move) {
                'outline' => [1, $previous === 'risk' ? 3 : 1, -1, '構成を引き直し、読み直しポイントが整理されました。'],
                'compare' => [2, 0, 1, '比較対象を置き、評価の根拠が一段くっきりしました。'],
                'quote' => [0, 3, 1, '判断材料になるメモを引用し、記事の説得力が増しました。'],
                'verdict' => [1, 2, -2, '結論を先に置き、読者が次の判断へ進みやすくなりました。'],
                'risk' => [4, -1, 3, '懸念点を洗い出し、レビューの透明性が上がりました。'],
                default => [0, 0, 1, '未知の観点は保留メモとして残りました。'],
            };

            $bloom += $bloomDelta;
            $resonance += $resonanceDelta;
            $drift += $driftDelta;
            $log[] = $message;
            $previous = (string) $move;
        }

        $score = max(0, ($bloom * 3) + ($resonance * 2) - abs($drift - 3) * 4);
        $ending = match (true) {
            $score >= 48 => '公開可: 論点、根拠、結論がそろい、レビュー記事として十分に読めます。',
            $score >= 32 => '要調整: 記事の骨格は見えています。比較か結論をもう一段足しましょう。',
            default => '草稿: まだ材料が散っています。構成と引用から見直すと読みやすくなります。',
        };

        return [
            'score' => $score,
            'bloom' => $bloom,
            'resonance' => $resonance,
            'drift' => $drift,
            'log' => $log,
            'ending' => $ending,
        ];
    }
}
