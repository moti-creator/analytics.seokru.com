<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaAgentService
{
    protected const MAX_ITERATIONS = 10;
    protected const GRAPH_BASE = 'https://graph.facebook.com/v21.0';

    protected string $token;
    protected string $accountId;

    public function __construct()
    {
        $this->token = (string) env('META_ACCESS_TOKEN', '');
        $this->accountId = (string) env('META_AD_ACCOUNT_ID', '');
    }

    public function chat(array $messages): array
    {
        if (!$this->token || !$this->accountId) {
            return ['reply' => 'META_ACCESS_TOKEN or META_AD_ACCOUNT_ID not configured.', 'tool_calls' => []];
        }

        $toolCalls = [];

        for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
            $resp = Http::withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model' => config('services.anthropic.model', 'claude-sonnet-4-6'),
                'max_tokens' => 4096,
                'system' => $this->systemPrompt(),
                'messages' => $messages,
                'tools' => $this->toolDefs(),
            ]);

            if (!$resp->ok()) {
                Log::warning('MetaAgent: Claude API error', ['body' => substr($resp->body(), 0, 500)]);
                return ['reply' => 'שגיאת API. נסה שוב.', 'tool_calls' => $toolCalls];
            }

            $json = $resp->json();
            $stopReason = $json['stop_reason'] ?? 'end_turn';

            if ($stopReason === 'tool_use') {
                // Extract all tool_use blocks from content
                $assistantContent = $json['content'] ?? [];
                $messages[] = ['role' => 'assistant', 'content' => $assistantContent];

                $toolResultContent = [];
                foreach ($assistantContent as $block) {
                    if (($block['type'] ?? '') !== 'tool_use') continue;

                    $toolId = $block['id'];
                    $toolName = $block['name'];
                    $toolInput = $block['input'] ?? [];

                    $result = $this->executeTool($toolName, $toolInput);
                    $toolCalls[] = ['tool' => $toolName, 'args' => $toolInput];

                    $toolResultContent[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolId,
                        'content' => json_encode($result),
                    ];
                }

                $messages[] = ['role' => 'user', 'content' => $toolResultContent];
                continue;
            }

            // end_turn — extract final text
            $text = '';
            foreach ($json['content'] ?? [] as $block) {
                if (($block['type'] ?? '') === 'text') {
                    $text .= $block['text'];
                }
            }

            return ['reply' => trim($text), 'tool_calls' => $toolCalls];
        }

        return ['reply' => 'חרגתי ממספר הפעולות המקסימלי. נסה שאלה פשוטה יותר.', 'tool_calls' => $toolCalls];
    }

    protected function executeTool(string $name, array $args): array
    {
        try {
            return match ($name) {
                'list_campaigns'    => $this->listCampaigns($args),
                'get_insights'      => $this->getInsights($args),
                'list_adsets'       => $this->listAdsets($args),
                'update_budget'     => $this->updateBudget($args),
                'set_campaign_status' => $this->setCampaignStatus($args),
                'create_campaign'   => $this->createCampaign($args),
                default             => ['error' => "Unknown tool: $name"],
            };
        } catch (\Throwable $e) {
            Log::warning("MetaAgent tool error [$name]", ['msg' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    protected function metaGet(string $path, array $params = []): array
    {
        $params['access_token'] = $this->token;
        $resp = Http::timeout(30)->get(self::GRAPH_BASE . $path, $params);
        return $resp->json() ?? ['error' => 'Empty response'];
    }

    protected function metaPost(string $path, array $data = []): array
    {
        $data['access_token'] = $this->token;
        $resp = Http::timeout(30)->asForm()->post(self::GRAPH_BASE . $path, $data);
        return $resp->json() ?? ['error' => 'Empty response'];
    }

    protected function listCampaigns(array $args): array
    {
        $fields = 'id,name,status,objective,daily_budget,lifetime_budget,start_time,stop_time';
        $params = ['fields' => $fields, 'limit' => 50];
        if (!empty($args['status'])) {
            $params['effective_status'] = json_encode([$args['status']]);
        }
        return $this->metaGet("/{$this->accountId}/campaigns", $params);
    }

    protected function getInsights(array $args): array
    {
        $campaignId = $args['campaign_id'] ?? null;
        $datePreset = $args['date_preset'] ?? 'last_30d';
        $fields = 'campaign_name,spend,impressions,clicks,ctr,cpc,cpm,actions,cost_per_action_type,reach,frequency';

        $path = $campaignId
            ? "/{$campaignId}/insights"
            : "/{$this->accountId}/insights";

        return $this->metaGet($path, [
            'fields' => $fields,
            'date_preset' => $datePreset,
            'level' => 'campaign',
        ]);
    }

    protected function listAdsets(array $args): array
    {
        $campaignId = $args['campaign_id'] ?? null;
        $fields = 'id,name,status,daily_budget,lifetime_budget,optimization_goal,targeting,start_time,end_time';

        $path = $campaignId
            ? "/{$campaignId}/adsets"
            : "/{$this->accountId}/adsets";

        return $this->metaGet($path, ['fields' => $fields, 'limit' => 50]);
    }

    protected function updateBudget(array $args): array
    {
        $adsetId = $args['adset_id'] ?? null;
        if (!$adsetId) return ['error' => 'adset_id required'];

        $amountCents = (int) round(($args['amount'] ?? 0) * 100);
        $type = $args['type'] ?? 'daily';

        $data = $type === 'lifetime'
            ? ['lifetime_budget' => $amountCents]
            : ['daily_budget' => $amountCents];

        return $this->metaPost("/{$adsetId}", $data);
    }

    protected function setCampaignStatus(array $args): array
    {
        $campaignId = $args['campaign_id'] ?? null;
        $status = strtoupper($args['status'] ?? 'PAUSED');
        if (!$campaignId) return ['error' => 'campaign_id required'];
        if (!in_array($status, ['ACTIVE', 'PAUSED', 'ARCHIVED'])) {
            return ['error' => 'status must be ACTIVE, PAUSED, or ARCHIVED'];
        }
        return $this->metaPost("/{$campaignId}", ['status' => $status]);
    }

    protected function createCampaign(array $args): array
    {
        $name = $args['name'] ?? null;
        $objective = $args['objective'] ?? 'OUTCOME_LEADS';
        if (!$name) return ['error' => 'name required'];

        $data = [
            'name' => $name,
            'objective' => $objective,
            'status' => 'PAUSED',
            'special_ad_categories' => '[]',
        ];

        return $this->metaPost("/{$this->accountId}/campaigns", $data);
    }

    protected function systemPrompt(): string
    {
        $account = $this->accountId;
        return <<<PROMPT
אתה מנהל קמפיינים של מטא (Meta Ads) עבור דניאלה פלמן-קושקה, מורת יוגה מיפו.
חשבון: {$account}.

תפקידך: לעזור לדניאלה להבין ולנהל את קמפיינים הפרסום שלה בפייסבוק/אינסטגרם.
תשובות: בעברית, קצרות, ישירות. מספרים בשקלים (₪).

## מה מותר לך לעשות
- לשלוף נתונים על קמפיינים, תוצאות, תקציבים
- לשנות תקציב של אד-סט (אחרי אישור המשתמש)
- להפעיל/להשהות קמפיין (אחרי אישור המשתמש)
- ליצור קמפיין חדש במצב PAUSED (אחרי אישור מלא)

## חשוב
- **לפני כל שינוי** (תקציב, סטטוס, יצירה): סכם מה אתה עומד לעשות ובקש אישור מפורש.
- אל תבצע שינויים בלי "כן, אשר" מהמשתמש.
- אם המשתמש שואל על ביצועים — שלוף נתונים ואז הסבר.
- אם אין נתונים — אמור זאת, אל תמציא.

## מבנה החשבון (ידע רקע)
- הקמפיין הטוב ביותר היסטורית: "d2 teaching - combined lookalike" — 217 לידים, ₪12.81 לליד, CTR 10.4%
- מוצרים עיקריים: שנת העמקה (קורס מורים), חברות לסטודיו ביפו
- כיום פעילים רק קמפיייני מעורבות, ללא ליד-ג'ן

תמיד ענה בעברית אלא אם התבקשת אחרת.
PROMPT;
    }

    protected function toolDefs(): array
    {
        return [
            [
                'name' => 'list_campaigns',
                'description' => 'List all Meta campaigns in the account. Returns id, name, status, objective, budget.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => [
                            'type' => 'string',
                            'description' => 'Filter by status: ACTIVE, PAUSED, ARCHIVED. Omit for all.',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'get_insights',
                'description' => 'Get performance insights (spend, impressions, clicks, CTR, CPC, leads/actions) for a campaign or all campaigns.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'campaign_id' => [
                            'type' => 'string',
                            'description' => 'Specific campaign ID. Omit to get account-level insights.',
                        ],
                        'date_preset' => [
                            'type' => 'string',
                            'description' => 'Date range: today, yesterday, last_3d, last_7d, last_14d, last_30d, last_90d, last_month, maximum. Default: last_30d.',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'list_adsets',
                'description' => 'List ad sets for a specific campaign or all ad sets in the account.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'campaign_id' => [
                            'type' => 'string',
                            'description' => 'Campaign ID to filter. Omit for all ad sets.',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'update_budget',
                'description' => 'Update the daily or lifetime budget of an ad set. Budget is in ILS (shekels — will be converted to agorot internally).',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'adset_id' => ['type' => 'string', 'description' => 'Ad set ID to update.'],
                        'amount' => ['type' => 'number', 'description' => 'Budget amount in ILS (shekels).'],
                        'type' => ['type' => 'string', 'description' => 'Budget type: daily or lifetime. Default: daily.'],
                    ],
                    'required' => ['adset_id', 'amount'],
                ],
            ],
            [
                'name' => 'set_campaign_status',
                'description' => 'Activate, pause, or archive a campaign.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'campaign_id' => ['type' => 'string', 'description' => 'Campaign ID.'],
                        'status' => ['type' => 'string', 'description' => 'ACTIVE, PAUSED, or ARCHIVED.'],
                    ],
                    'required' => ['campaign_id', 'status'],
                ],
            ],
            [
                'name' => 'create_campaign',
                'description' => 'Create a new campaign (always PAUSED). Use only after explicit user confirmation.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Campaign name.'],
                        'objective' => [
                            'type' => 'string',
                            'description' => 'OUTCOME_LEADS, OUTCOME_TRAFFIC, OUTCOME_ENGAGEMENT, OUTCOME_AWARENESS, OUTCOME_SALES.',
                        ],
                    ],
                    'required' => ['name'],
                ],
            ],
        ];
    }
}
