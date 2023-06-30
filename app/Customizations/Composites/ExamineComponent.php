<?php

declare(strict_types=1);

namespace App\Customizations\Composites;

use App\Customizations\Components\CurlComponent;
use App\Customizations\Components\interfaces\InterfaceComponent;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Factories\CurlExaminer;
use App\Customizations\Factories\GetHeadersExaminer;
use App\Customizations\Factories\InterfaceExaminer;
use App\Customizations\Traits\ShareTrait;
use App\Models\RemoteFeeds;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExamineComponent implements InterfaceShare
{
    use ShareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        $examine = new CurlComponent([
            CURLOPT_URL             => $this->acquired->source,
            CURLOPT_NOBODY          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FILETIME        => true,
        ]);

        $response = $examine->execute();

        if ($response === false) {
            throw new DomainException(\strtr("Terminated with the following errors: {errorCodes}", [
                '{errorCodes}' => \implode(", ", \array_keys($examine->getErrorBatch()))
            ]));
        }

        $info = $examine->getInfo();
        $statusCode = (int) $info[$examine::STATUS_CODE];
        if ($statusCode !== 200) {
            throw new DomainException("Terminated, expected status code 200, instead got $statusCode");
        }

        $this->append(['examine' => $examine]);

        if ($this->has('model') === false) {
            return $response;
        }

        try {
            DB::beginTransaction();
            $modelClass = \get_class($this->acquired->model);
            match ($modelClass) {
                RemoteFeeds::class => $this->acquired->model->save([
                    'examine_counter' => $this->acquired->model->examine_counter + 1
                ]),
                default => null,
            };
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed to update model", [
                'message'   => $e->getMessage(),
                'model'     => $this->acquired->model,
            ]);
            $response = false;
        }

        $this->transfer('model');
        return $response;
    }
}
