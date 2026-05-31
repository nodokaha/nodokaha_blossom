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
# NODOKAHAの一日を評価する小さなSECDプログラム
LDC 13
LDC 21
ADD
ST idea_score
LD idea_score
LDC 30
GT
SEL LDC "開花: プロトタイプへ"; OUT; JOIN | LDC "待機: もう一粒だけ観察"; OUT; JOIN
STOP
SECD;

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('portfolio/index.html.twig', [
            'links' => $this->portfolioLinks(),
            'profile' => $this->profileData(),
        ]);
    }

    #[Route('/nodokaha', name: 'app_portfolio_profile', methods: ['GET'])]
    public function profile(): Response
    {
        return $this->render('portfolio/profile.html.twig', [
            'profile' => $this->profileData(),
        ]);
    }

    #[Route('/portfolio/routes', name: 'app_portfolio_routes', methods: ['GET'])]
    public function routes(): Response
    {
        return $this->render('portfolio/routes.html.twig', [
            'links' => $this->portfolioLinks(),
        ]);
    }

    #[Route('/portfolio/secd-vm', name: 'app_portfolio_secd_vm', methods: ['GET', 'POST'])]
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
                'list' => "LDC [\"seed\",\"rain\"]\nCAR\nOUT\nSTOP",
            ],
        ]);
    }

    #[Route('/portfolio/bloom-signal', name: 'app_portfolio_bloom_signal', methods: ['GET', 'POST'])]
    public function bloomSignal(Request $request): Response
    {
        $moves = $request->isMethod('POST') ? $request->request->all('moves') : ['mirror', 'rain', 'listen', 'thread'];
        $game = $this->scoreBloomSignal($moves);

        return $this->render('portfolio/bloom_signal.html.twig', [
            'moves' => $moves,
            'game' => $game,
            'move_catalog' => [
                'mirror' => '鏡: 直前の揺らぎを反転して発想を澄ませる',
                'rain' => '雨: 観察値を増やし、少しだけ余白を濡らす',
                'listen' => '聴く: ノイズを素材にして共鳴を足す',
                'thread' => '糸: 離れたページ同士を結んで導線を太くする',
                'spark' => '火花: 予測不能な一点突破で開花を急がせる',
            ],
        ]);
    }

    /** @return array{name: string, title: string, statement: string, traits: list<string>, works: list<array{label: string, text: string}>} */
    private function profileData(): array
    {
        return [
            'name' => 'NODOKAHA',
            'title' => '箱庭・物語VM・生活感のあるUIをつなぐ架空の作家/実装者',
            'statement' => 'NODOKAHAは、静かな観察から小さな実験を育て、遊べる導線として公開する人物です。ポートフォリオは作品集であると同時に、ページそのものが庭のように変化する展示室です。',
            'traits' => ['観察を仕様に変える', 'レトロな画面に現代的な動線を置く', 'VMや箱庭など抽象的な仕組みを遊びに翻訳する'],
            'works' => [
                ['label' => 'BLOSSOM HUB', 'text' => '最初に訪れるポートフォリオ型の玄関。人物、作品、実験へ迷わず進めます。'],
                ['label' => 'SECD VM', 'text' => 'Stack / Environment / Control / Dump の状態を追える簡易インタプリタ。'],
                ['label' => 'BLOOM SIGNAL', 'text' => '導線そのものを育てる、短いコマンド選択型ミニゲーム。'],
            ],
        ];
    }

    /** @return list<array{route: string, label: string, description: string}> */
    private function portfolioLinks(): array
    {
        return [
            ['route' => 'app_portfolio_profile', 'label' => 'NODOKAHA', 'description' => '人物像と制作姿勢'],
            ['route' => 'app_portfolio_routes', 'label' => '各ページ動線', 'description' => 'サイト内の入口を一覧化'],
            ['route' => 'app_portfolio_secd_vm', 'label' => 'SECD VM', 'description' => '簡易インタプリタを実行'],
            ['route' => 'app_portfolio_bloom_signal', 'label' => 'BLOOM SIGNAL', 'description' => '独創的ミニゲーム'],
            ['route' => 'app_garden_list', 'label' => '箱庭一覧', 'description' => '既存の共有箱庭へ'],
            ['route' => 'basisvr_event_index', 'label' => 'イベント掲示板', 'description' => '記事とコメントへ'],
        ];
    }

    /**
     * @param list<string> $moves
     * @return array{score: int, bloom: int, resonance: int, drift: int, log: list<string>, ending: string}
     */
    private function scoreBloomSignal(array $moves): array
    {
        $bloom = 4;
        $resonance = 3;
        $drift = 2;
        $log = [];
        $previous = '';

        foreach (array_slice($moves, 0, 6) as $move) {
            [$bloomDelta, $resonanceDelta, $driftDelta, $message] = match ($move) {
                'mirror' => [1, $previous === 'spark' ? 3 : 1, -1, '鏡がノイズを反転し、余白が一歩だけ整いました。'],
                'rain' => [2, 0, 1, '雨粒が観察メモを増やし、庭に湿度を戻しました。'],
                'listen' => [0, 3, 1, '遠いページの足音を聴き、共鳴が濃くなりました。'],
                'thread' => [1, 2, -2, '糸が作品同士を結び、迷子の導線を引き戻しました。'],
                'spark' => [4, -1, 3, '火花が一気に開花を進め、代わりに揺らぎも増えました。'],
                default => [0, 0, 1, '未知の合図は霧になり、少しだけ揺らぎを残しました。'],
            };

            $bloom += $bloomDelta;
            $resonance += $resonanceDelta;
            $drift += $driftDelta;
            $log[] = $message;
            $previous = (string) $move;
        }

        $score = max(0, ($bloom * 3) + ($resonance * 2) - abs($drift - 3) * 4);
        $ending = match (true) {
            $score >= 48 => '満開: 導線が花冠になり、訪問者は自然に次の作品へ歩き出します。',
            $score >= 32 => '開花: 作品の気配がつながり、もう一手で展示が歌い始めます。',
            default => '蕾: まだ静かです。雨や糸で余白を整えると芽吹きます。',
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
