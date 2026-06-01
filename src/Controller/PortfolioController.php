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
# BasisVRコンテンツ説明の確認を行う小さなSECDプログラム
LDC 13
LDC 21
ADD
ST content_score
LD content_score
LDC 30
GT
SEL LDC "公開: コンテンツ確認完了"; OUT; JOIN | LDC "保留: もう一度検証"; OUT; JOIN
STOP
SECD;

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('portfolio/index.html.twig', [
            'links' => $this->contentLinks(),
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
                'list' => "LDC [\"prop\",\"world\"]\nCAR\nOUT\nSTOP",
            ],
        ]);
    }

    #[Route('/tools/content-signal', name: 'app_portfolio_bloom_signal', methods: ['GET', 'POST'])]
    public function bloomSignal(Request $request): Response
    {
        $moves = $request->isMethod('POST') ? $request->request->all('moves') : ['outline', 'compare', 'quote', 'verdict'];
        $game = $this->scoreContentSignal($moves);

        return $this->render('portfolio/bloom_signal.html.twig', [
            'moves' => $moves,
            'game' => $game,
            'move_catalog' => [
                'outline' => '構成: 説明の要点を並べ直す',
                'compare' => '比較: 似たアセットとの差分を確認する',
                'quote' => '仕様: 同梱物や導入条件を抜き出す',
                'verdict' => '導線: 利用者が次に取る行動を明確にする',
                'risk' => '注意: 見落としやすい制約を先に書く',
            ],
        ]);
    }

    /** @return list<array{route: string, label: string, description: string}> */
    private function contentLinks(): array
    {
        return [
            ['route' => 'basisvr_event_index', 'label' => 'コンテンツ一覧', 'description' => 'BasisVR向けProp、World、Avatarの説明と関連アセットを読む'],
            ['route' => 'basisvr_event_new', 'label' => 'コンテンツを投稿', 'description' => 'タイトル・種別・説明・関連アセット・タグを添えて公開'],
            ['route' => 'app_portfolio_secd_vm', 'label' => 'SECD VM', 'description' => 'コンテンツ説明を確認するデモツールを実行'],
            ['route' => 'app_portfolio_bloom_signal', 'label' => 'CONTENT SIGNAL', 'description' => '投稿内容を整えるミニゲームで説明構成を試す'],
        ];
    }

    /**
     * @param list<string> $moves
     * @return array{score: int, bloom: int, resonance: int, drift: int, log: list<string>, ending: string}
     */
    private function scoreContentSignal(array $moves): array
    {
        $bloom = 4;
        $resonance = 3;
        $drift = 2;
        $log = [];
        $previous = '';

        foreach (array_slice($moves, 0, 6) as $move) {
            [$bloomDelta, $resonanceDelta, $driftDelta, $message] = match ($move) {
                'outline' => [1, $previous === 'risk' ? 3 : 1, -1, '構成を引き直し、コンテンツの要点が整理されました。'],
                'compare' => [2, 0, 1, '比較対象を置き、アセットの特徴が一段くっきりしました。'],
                'quote' => [0, 3, 1, '同梱物や導入条件を示し、投稿の分かりやすさが増しました。'],
                'verdict' => [1, 2, -2, '利用導線を先に置き、利用者が次の操作へ進みやすくなりました。'],
                'risk' => [4, -1, 3, '制約を洗い出し、コンテンツ投稿の透明性が上がりました。'],
                default => [0, 0, 1, '未知の項目は補足メモとして残りました。'],
            };

            $bloom += $bloomDelta;
            $resonance += $resonanceDelta;
            $drift += $driftDelta;
            $log[] = $message;
            $previous = (string) $move;
        }

        $score = max(0, ($bloom * 3) + ($resonance * 2) - abs($drift - 3) * 4);
        $ending = match (true) {
            $score >= 48 => '公開可: 種別、説明、関連アセットがそろい、BasisVRコンテンツ投稿として十分に読めます。',
            $score >= 32 => '要調整: 投稿の骨格は見えています。利用シーンか導入手順をもう一段足しましょう。',
            default => '草稿: まだ材料が散っています。概要と関連アセットから整理すると読みやすくなります。',
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
