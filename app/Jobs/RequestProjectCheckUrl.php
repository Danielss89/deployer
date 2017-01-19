<?php

namespace REBELinBLUE\Deployer\Jobs;

use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use REBELinBLUE\Deployer\CheckUrl;
use REBELinBLUE\Deployer\Events\UrlDown;
use REBELinBLUE\Deployer\Events\UrlUp;

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
            $isCurrentlyHealthy = ($link->status === CheckUrl::UNTESTED || $link->isHealthy());

            try {
                (new Client(['timeout'  => 30]))->get($link->url, [
                    'headers' => [
                        'User-Agent' => USER_AGENT,
                    ],
                ]);

                // FIXME: Move this to methods on the model?

                $status = CheckUrl::ONLINE;
                $missed = 0;

                if (!$isCurrentlyHealthy) {
                    $event = UrlUp::class;
                }

                $link->last_seen = $link->freshTimestamp();
            } catch (\Exception $error) {
                $status = CheckUrl::OFFLINE;
                $missed = $link->missed + 1;

                $event = UrlDown::class;
            }

            $link->status = $status;
            $link->missed = $missed;
            $link->save();

            if (isset($event)) {
                event(new $event($link));
            }
        }
    }
}
