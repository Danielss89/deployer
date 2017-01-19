<?php

namespace REBELinBLUE\Deployer\Jobs;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use REBELinBLUE\Deployer\CheckUrl;

/**
 * Request the urls.
 */
class RequestProjectCheckUrl extends Job implements ShouldQueue
{
    use SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $links;

    /**
     * RequestProjectCheckUrl constructor.
     *
     * @param CheckUrl[] $links
     */
    public function __construct($links)
    {
        $this->links = $links;
    }

    /**
     * Execute the command.
     */
    public function handle()
    {
        foreach ($this->links as $link) {
            try {
                (new HttpClient(['timeout'  => 30]))->get($link->url, [
                    'headers' => [
                        'User-Agent' => USER_AGENT,
                    ],
                ]);

                $link->online();
            } catch (\Exception $error) {
                $link->offline();
            }
        }
    }
}
