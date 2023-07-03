<?php

declare(strict_types=1);

namespace App\Customizations\Composites;

use App\Customizations\Components\CurlComponent;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Traits\ShareTrait;
use App\Models\RemoteFeeds;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

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

        $this->transfer('disk')->append(['examine' => $examine]);

        if ($this->has('model') === false) {
            return $response;
        }

        $filetime = Carbon::createFromTimestamp($info[$examine::FILETIME]);
        $updatedAt = $this->acquired->model->updated_at ?? null;
        if ($updatedAt !== null && $filetime->lt($updatedAt)) {
            throw new DomainException(\strtr(
                "Terminated, local content is up-to-date. Source: {source}, Local: {local}",
                [
                    '{source}'  => $filetime->toString(),
                    '{local}'   => $this->acquired->model->updated_at->toString()
                ]
            ));
        }

        $modelClass = \get_class($this->acquired->model);
        match ($modelClass) {
            RemoteFeeds::class => DB::transaction(function () {
                $this->acquired->model->examine_counter += 1;
                $this->acquired->model->save();
            }),
            default => null,
        };

        $this->transfer('model');
        return $response;
    }
}
