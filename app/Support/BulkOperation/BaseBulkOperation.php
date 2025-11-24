<?php

namespace App\Support\BulkOperation;

use App\Interfaces\FileManager;
use App\Models\BulkOperationInstance;
use App\Models\BulkOperationResult;
use App\Models\User;
use App\Notifications\Dynamic\BulkOperationCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Collection;
use App\Support\BulkOperation\Result\BaseResultItem;
use App\Support\QRCodeManager;

abstract class BaseBulkOperation
{
    protected FileManager $files;

    public function __construct()
    {
        $this->files = app(FileManager::class);
    }

    public function createInstance(Request $request): BulkOperationInstance
    {
        $instance = $this->createInstanceModel($request);

        $this->store($request, $instance);

        return $instance;
    }

    protected abstract function store(Request $request, BulkOperationInstance $instance);

    public abstract function run(BulkOperationInstance $instance): static;

    public final function reRun(BulkOperationInstance $instance): static
    {
        if (!$this->canQueueForRerun($instance)) return $this;

        $this->queueReRun($instance);

        $this->setQueuedForRerunTime($instance, time());

        return $this;
    }

    protected abstract function queueReRun(BulkOperationInstance $instance);

    protected function setQueuedForRerunTime(BulkOperationInstance $instance, $value)
    {
        $instance->setMeta('rerun_queued_at', $value);
    }

    protected function canQueueForRerun(BulkOperationInstance $instance)
    {
        $span = time() - $instance->getMeta('rerun_queued_at');

        // only try re-run every 30 minutes once

        return $span / 3600. > 0.5;
    }

    public function createInstanceModel(Request $request)
    {
        $operation = new BulkOperationInstance();

        $operation->type = $this->type();

        $operation->name = $this->name();

        $operation->user_id = $request->user()->id;

        $operation->status = $operation::STATUS_NEW;

        $operation->save();

        return $operation;
    }

    public abstract function name(): string;

    public abstract function type(): string;

    /**
     * @return Collection<BaseResultItem>
     */
    public function results(BulkOperationInstance $instance)
    {
        $results = BulkOperationResult::where(
            'bulk_operation_instance_id',
            $instance->id
        )
            ->orderBy('id', 'desc')
            ->get();

        return $this->transformResults($results);
    }

    /**
     * @return Collection<BaseResultItem>
     */
    protected function transformResults($results)
    {
        return $results;
    }

    public function instances(?User $user = null)
    {
        $query = BulkOperationInstance::whereType($this->type())
            ->orderBy('id', 'desc');

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $instances = $query->get();

        $instances = $instances->map(function (BulkOperationInstance $instance) {
            return $this->transformInstance($instance);
        });

        return $instances;
    }

    protected function transformInstance(BulkOperationInstance $instance)
    {
        $instance->print_signature = $instance->getPrintSignature();

        return $instance;
    }

    public function deleteAllQRCodes(BulkOperationInstance $instance)
    {
        $qrcodeIds = $this->results($instance)
            ->map(function (BaseResultItem $item) {
                return $item->toQRCode()?->id;
            })->filter(fn($id) => !empty($id));

        (new QRCodeManager)->deleteMany($qrcodeIds->toArray());
    }



    protected abstract function progress(BulkOperationInstance $instanace): string;

    public abstract function updateInstanceStatus(BulkOperationInstance $instance);

    public abstract function exportCsv(BulkOperationInstance $instance);

    public function toArray()
    {
        return [
            'name' => $this->name(),
            'type' => $this->type()
        ];
    }

    public abstract function isCompleted(BulkOperationInstance $instance): bool;

    public function onOperationCompleted(BulkOperationInstance $instance)
    {
        $instance->user->notify(
            BulkOperationCompleted::for($instance)
        );
    }

    public function url()
    {
        return url('/dashboard/bulk-operations?tab-id=' . $this->type());
    }

    /**
     * arrayToCsvDownload
     * 
     */
    function arrayToCsvDownload($array, $filename = "export.csv", $delimiter = ";")
    {
        // use keys as column titles
        $data = [];

        // working with items
        foreach ($array as $item) {
            $values = array_values((array) $item);
            array_push($data, implode($delimiter, $values));
        }

        try {
            // flush buffer
            ob_flush();
        } catch (Throwable $th) {
            //
        }

        // mixing items
        $csvData = join("\n", $data);
        //setup headers to download the file
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        //setup utf8 encoding
        header('Content-Type: application/csv; charset=UTF-8');
        // showing the results
        die($csvData);
    }

    protected function jobsTableIsEmpty()
    {
        return DB::table('jobs')->count() == 0;
    }
}
