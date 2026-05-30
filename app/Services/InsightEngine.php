<?php

namespace App\Services;

/**
 * Turns campaign data into actionable insight cards.
 * Rule-based, no LLM.
 */
class InsightEngine
{
    /**
     * @param  array  $campaigns  output of MetaService::campaigns()
     * @param  array  $snoozed    [campaign_id => snooze_until_unix_timestamp]
     * @return array of cards: ['type','severity','campaign','title','detail','actions']
     */
    /**
     * Account-level overview insights — shown at top, no actions needed.
     * @return array of ['icon','tone','text']
     */
    public function accountOverview(array $campaigns, array $week, array $month, array $today, float $totalDaily = 0): array
    {
        $overview = [];

        // Active campaign count — use effective_status (campaign can be config-ACTIVE but adsets paused)
        $active = array_filter($campaigns, fn($c) => ($c['effective_status'] ?? $c['status']) === 'ACTIVE');
        $activeCount = count($active);

        // 1) Lead trend
        if ($week['delta']['leads'] !== null) {
            $d = $week['delta']['leads'];
            if ($d >= 20) {
                $overview[] = ['icon' => '📈', 'tone' => 'good', 'text' => "לידים עלו {$d}% השבוע · " . $week['this']['leads'] . " לידים"];
            } elseif ($d <= -20) {
                $overview[] = ['icon' => '📉', 'tone' => 'bad', 'text' => "לידים ירדו " . abs($d) . "% השבוע · " . $week['this']['leads'] . " לידים"];
            }
        }

        // 2) CPL trend
        $thisCpl = $week['this']['leads'] > 0 ? $week['this']['spend'] / $week['this']['leads'] : null;
        $lastCpl = $week['last']['leads'] > 0 ? $week['last']['spend'] / $week['last']['leads'] : null;
        if ($thisCpl !== null && $lastCpl !== null && $lastCpl > 0) {
            $cplChange = round((($thisCpl - $lastCpl) / $lastCpl) * 100);
            if ($cplChange <= -15) {
                $overview[] = ['icon' => '💚', 'tone' => 'good', 'text' => "עלות לליד השתפרה: ₪" . round($thisCpl) . " (היה ₪" . round($lastCpl) . ")"];
            } elseif ($cplChange >= 20) {
                $overview[] = ['icon' => '💔', 'tone' => 'bad', 'text' => "עלות לליד עלתה: ₪" . round($thisCpl) . " (היה ₪" . round($lastCpl) . ")"];
            }
        } elseif ($thisCpl !== null) {
            $overview[] = ['icon' => '💰', 'tone' => 'neutral', 'text' => "עלות לליד ממוצעת השבוע: ₪" . round($thisCpl)];
        }

        // 3) Top performer this week
        $byCpl = array_filter($campaigns, fn($c) => $c['cpl'] !== null && $c['cpl'] > 0 && ($c['effective_status'] ?? $c['status']) === 'ACTIVE');
        usort($byCpl, fn($a, $b) => $a['cpl'] <=> $b['cpl']);
        if ($best = $byCpl[0] ?? null) {
            $overview[] = ['icon' => '🏆', 'tone' => 'good', 'text' => "הכוכב השבוע: \"" . $best['name'] . "\" · ₪" . round($best['cpl']) . " לליד · " . $best['stats']['leads'] . " לידים"];
        }

        // 4) Top spender with no results
        $burners = array_filter($campaigns, fn($c) => ($c['effective_status'] ?? $c['status']) === 'ACTIVE' && $c['stats']['spend'] >= 30 && $c['stats']['leads'] === 0);
        usort($burners, fn($a, $b) => $b['stats']['spend'] <=> $a['stats']['spend']);
        if ($burner = $burners[0] ?? null) {
            $overview[] = ['icon' => '🚨', 'tone' => 'bad', 'text' => "מבזבז: \"" . $burner['name'] . "\" · ₪" . round($burner['stats']['spend']) . " השבוע · 0 לידים"];
        }

        // 5) Monthly pace
        if ($month['spend'] > 0) {
            $overview[] = [
                'icon' => '📅',
                'tone' => 'neutral',
                'text' => "החודש: ₪" . round($month['spend']) . " · קצב חודשי חזוי: ₪" . round($month['projected']),
            ];
        }

        // 6) Today vs daily budget
        if ($totalDaily > 0 && $today['spend'] > 0) {
            $pct = round(($today['spend'] / $totalDaily) * 100);
            if ($pct >= 90) {
                $overview[] = ['icon' => '⚡', 'tone' => 'neutral', 'text' => "ניצול תקציב היום: {$pct}% · ₪" . round($today['spend']) . " מתוך ₪" . round($totalDaily)];
            }
        }

        // 7) Active mix
        $totalCount = count($campaigns);
        if ($activeCount === 0 && $totalCount > 0) {
            $overview[] = ['icon' => '😴', 'tone' => 'neutral', 'text' => "אין קמפיינים פעילים כרגע ({$totalCount} בארכיון)"];
        } elseif ($activeCount > 0) {
            $overview[] = ['icon' => '✓', 'tone' => 'neutral', 'text' => "{$activeCount} קמפיינים פעילים · ₪" . round($totalDaily) . " תקציב יומי"];
        }

        return $overview;
    }

    public function generate(array $campaigns, array $snoozed = []): array
    {
        $cards = [];
        $now = time();

        foreach ($campaigns as $c) {
            // Skip snoozed
            if (isset($snoozed[$c['id']]) && $snoozed[$c['id']] > $now) continue;
            // Skip archived
            if (in_array($c['status'] ?? '', ['ARCHIVED', 'DELETED'])) continue;

            $s = $c['stats'];
            $eff = $c['effective_status'] ?? $c['status'];

            // RULE 0: New campaign — created in last 3 days
            if (!empty($c['created_time'])) {
                $created = strtotime($c['created_time']);
                if ($created && ($now - $created) < 3 * 86400) {
                    $cards[] = $this->newCampaignCard($c);
                    continue;
                }
            }

            // RULE 1: Failing — spent meaningfully, zero leads (only flag ACTIVE)
            if (($c['effective_status'] ?? $c['status']) === 'ACTIVE' && $s['spend'] >= 30 && $s['leads'] === 0) {
                $cards[] = $this->failingCard($c, "השקעת ₪" . round($s['spend']) . " ב-7 ימים האחרונים ללא לידים");
                continue;
            }

            // RULE 2: Burning — high spend + low CTR
            if (($c['effective_status'] ?? $c['status']) === 'ACTIVE' && $s['spend'] >= 50 && $s['ctr'] > 0 && $s['ctr'] < 1) {
                $cards[] = $this->burningCard($c, "CTR " . number_format($s['ctr'], 2) . "% נמוך מאוד · הוצאת ₪" . round($s['spend']));
                continue;
            }

            // RULE 3: Winning — strong CPL
            if (($c['effective_status'] ?? $c['status']) === 'ACTIVE' && $c['cpl'] !== null && $c['cpl'] > 0 && $c['cpl'] < 15) {
                $cards[] = $this->winningCard($c, "₪" . number_format($c['cpl'], 1) . " לליד · " . $s['leads'] . " לידים השבוע");
                continue;
            }

            // RULE 4: High CTR new — high engagement, didn't spend much yet
            if (($c['effective_status'] ?? $c['status']) === 'ACTIVE' && $s['ctr'] >= 3 && $s['spend'] < 50 && $s['impressions'] >= 500) {
                $cards[] = $this->promisingCard($c, "CTR " . number_format($s['ctr'], 1) . "% — אנשים מגיבים. שווה לבדוק העלאת תקציב");
                continue;
            }

            // RULE 5: Expensive lead — flag CPL > ₪50
            if (($c['effective_status'] ?? $c['status']) === 'ACTIVE' && $c['cpl'] !== null && $c['cpl'] > 50 && $s['leads'] >= 1) {
                $cards[] = $this->expensiveCard($c, "₪" . round($c['cpl']) . " לליד — גבוה יחסית");
                continue;
            }
        }

        // Sort: red first, yellow second, green last
        usort($cards, fn($a, $b) => $this->sevRank($a['severity']) - $this->sevRank($b['severity']));

        return $cards;
    }

    protected function sevRank(string $sev): int
    {
        return match ($sev) {
            'danger' => 0,
            'warning' => 1,
            'info' => 2,
            'success' => 3,
            default => 4,
        };
    }

    protected function newCampaignCard(array $c): array
    {
        $eff = $c['effective_status'] ?? $c['status'];
        $isActive = $eff === 'ACTIVE';
        $isPaused = in_array($eff, ['PAUSED', 'CAMPAIGN_PAUSED']);
        $detail = $isActive
            ? 'הקמפיין פעיל · עוקבים אחרי ביצועים'
            : ($isPaused ? 'הקמפיין במצב מושהה · מוכן להפעלה' : "סטטוס: {$eff}");

        $actions = [];
        if ($isPaused) {
            $actions[] = ['label' => '▶️ הפעל', 'action' => 'activate', 'style' => 'primary'];
        } elseif ($isActive) {
            $actions[] = ['label' => '⏸ השהה', 'action' => 'pause', 'style' => 'default'];
        }
        $actions[] = ['label' => 'התעלם לשבוע', 'action' => 'snooze', 'style' => 'ghost'];

        return [
            'type' => 'new',
            'severity' => 'info',
            'icon' => '🆕',
            'campaign' => $c,
            'title' => $c['name'],
            'detail' => $detail,
            'actions' => $actions,
        ];
    }

    protected function failingCard(array $c, string $detail): array
    {
        return [
            'type' => 'failing',
            'severity' => 'danger',
            'icon' => '⚠️',
            'campaign' => $c,
            'title' => $c['name'],
            'detail' => $detail,
            'actions' => [
                ['label' => 'השהה', 'action' => 'pause', 'style' => 'primary'],
                ['label' => 'הורד תקציב 30%', 'action' => 'budget_pct', 'param' => -30, 'style' => 'default'],
                ['label' => 'התעלם לשבוע', 'action' => 'snooze', 'style' => 'ghost'],
            ],
        ];
    }

    protected function burningCard(array $c, string $detail): array
    {
        return [
            'type' => 'burning',
            'severity' => 'warning',
            'icon' => '🔥',
            'campaign' => $c,
            'title' => $c['name'],
            'detail' => $detail,
            'actions' => [
                ['label' => 'הורד תקציב 30%', 'action' => 'budget_pct', 'param' => -30, 'style' => 'primary'],
                ['label' => 'השהה', 'action' => 'pause', 'style' => 'default'],
                ['label' => 'התעלם לשבוע', 'action' => 'snooze', 'style' => 'ghost'],
            ],
        ];
    }

    protected function winningCard(array $c, string $detail): array
    {
        return [
            'type' => 'winning',
            'severity' => 'success',
            'icon' => '✨',
            'campaign' => $c,
            'title' => $c['name'],
            'detail' => $detail,
            'actions' => [
                ['label' => '+₪10/יום', 'action' => 'budget_add', 'param' => 10, 'style' => 'primary'],
                ['label' => '+₪20/יום', 'action' => 'budget_add', 'param' => 20, 'style' => 'default'],
                ['label' => 'השאר', 'action' => 'snooze', 'style' => 'ghost'],
            ],
        ];
    }

    protected function promisingCard(array $c, string $detail): array
    {
        return [
            'type' => 'promising',
            'severity' => 'info',
            'icon' => '🚀',
            'campaign' => $c,
            'title' => $c['name'],
            'detail' => $detail,
            'actions' => [
                ['label' => 'הגדל תקציב 20%', 'action' => 'budget_pct', 'param' => 20, 'style' => 'primary'],
                ['label' => 'ממשיכים לעקוב', 'action' => 'snooze', 'style' => 'ghost'],
            ],
        ];
    }

    protected function expensiveCard(array $c, string $detail): array
    {
        return [
            'type' => 'expensive',
            'severity' => 'warning',
            'icon' => '💸',
            'campaign' => $c,
            'title' => $c['name'],
            'detail' => $detail,
            'actions' => [
                ['label' => 'הורד תקציב 20%', 'action' => 'budget_pct', 'param' => -20, 'style' => 'primary'],
                ['label' => 'התעלם לשבוע', 'action' => 'snooze', 'style' => 'ghost'],
            ],
        ];
    }
}
