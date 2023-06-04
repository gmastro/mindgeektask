<?php
declare(strict_types=1);

namespace App\Customizations\Composites;

use App\Customizations\Factories\CurlExaminer;
use App\Customizations\Factories\GetHeadersExaminer;
use App\Customizations\Factories\InterfaceExaminer;
use App\Models\RemoteFeeds;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExamineComponent implements InterfaceComponent
{
    private $examiner;

    /**
     * Magic Constructor
     *
     * Gets last udpated from the feed tuple.
     * Additionally checks when the given source/link/url was last modifier.
     * An greater to last update value to download and process source content.
     *
     * @access  public
     * @param   Carbon $updatedAt Last update on the feed
     * @param   InterfaceExaminer $examiner Eit
     */
    public function __construct(public RemoteFeeds $feed)
    {
        $this->examiner = match($feed->examine_handler) {
            CurlExaminer::class         => new CurlExaminer($feed->source),
            GetHeadersExaminer::class   => new GetHeadersExaminer($feed->source),
            // ... other handlers
            default                     => throw new \UnhandledMatchError("unknown examine handler"),
        };

        DB::transaction(fn() => $feed->save([
            'is_active'         => $this->examiner->isValid(),
            'examine_counter'   => $feed->examine_counter + 1,
            'timestamps'        => false,
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        return $this->examiner->isValid()
            && $this->feed->updated_at->lt($this->examiner->getLastModified());
    }
}