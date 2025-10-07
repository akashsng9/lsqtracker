<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TeamConfig;
use Illuminate\Support\Facades\Http;

class FetchLeadsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');
        $url = "https://api-in21.leadsquared.com/v2/LeadManagement.svc/Leads/Retrieve/BySearchParameter?accessKey={$accessKey}&secretKey={$secretKey}";

        $pageIndex = 1;
        $pageSize  = 1000;
        $existingIds = TeamConfig::pluck('ProspectID')->toArray();
        $existingIds = array_flip($existingIds);

        do {
            $payload = [
                "SearchParameters" => [
                    "ListId" => $this->params['ListId'] ?? '66b97513-920e-11f0-9791-06b8222c9ed1',
                    "RetrieveBehaviour" => $this->params['RetrieveBehaviour'] ?? 0,
                ],
                "Columns" => [
                    "Include_CSV" => $this->params['Include_CSV'] ?? 'ProspectAutoId,EmailAddress,Score,',
                ],
                "Sorting" => [
                    "ColumnName" => $this->params['ColumnName'] ?? 'CreatedOn',
                    "Direction" => $this->params['Direction'] ?? 1,
                ],
                "Paging" => [
                    "PageIndex" => $pageIndex,
                    "PageSize" => $pageSize,
                ],
            ];

            $response = Http::withHeaders(['Content-Type' => 'application/json'])->post($url, $payload);
            $data = $response->json();

            if (empty($data['Leads'])) break;

            $rowsToInsert = [];
            foreach ($data['Leads'] as $leadBlock) {
                $leadArr = [];
                foreach ($leadBlock['LeadPropertyList'] as $prop) {
                    $leadArr[$prop['Attribute']] = $prop['Value'] ?? null;
                }

                if (empty($leadArr['ProspectID']) || isset($existingIds[$leadArr['ProspectID']])) {
                    continue;
                }

                $leadArr['raw'] = json_encode($leadArr);
                $leadArr['created_at'] = now();
                $leadArr['updated_at'] = now();

                // Convert boolean-like fields
                foreach (['IsStarredLead', 'IsTaggedLead', 'CanUpdate'] as $boolField) {
                    if (isset($leadArr[$boolField])) {
                        $val = strtolower((string) $leadArr[$boolField]);
                        if ($val === 'true' || $val === '1') {
                            $leadArr[$boolField] = 1;
                        } elseif ($val === 'false' || $val === '0') {
                            $leadArr[$boolField] = 0;
                        } else {
                            $leadArr[$boolField] = null; // default if invalid
                        }
                    }
                }

                if (!empty($leadArr['CreatedOn'])) {
                    try {
                        $dt = date_create($leadArr['CreatedOn']);
                        $leadArr['CreatedOn'] = $dt ? $dt->format('Y-m-d H:i:s') : null;
                    } catch (\Exception $e) {
                        $leadArr['CreatedOn'] = null;
                    }
                }

                $rowsToInsert[] = $leadArr;
            }

            foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                TeamConfig::insert($chunk);
            }

            $pageIndex++;
        } while (count($data['Leads']) >= $pageSize);
    }
}
