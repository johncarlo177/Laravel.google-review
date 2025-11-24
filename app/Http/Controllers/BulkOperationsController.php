<?php

namespace App\Http\Controllers;

use App\Models\BulkOperationInstance;
use App\Models\File;
use App\Models\QRCode;
use App\Support\BulkOperation\BaseImportOperation;
use App\Support\BulkOperation\BulkOperationManager;
use App\Support\BulkOperation\Result\ImportUrlQRCodesOperationResult;
use App\Support\DropletManager;
use App\Support\PDF\CutContourInjector;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;

class BulkOperationsController extends Controller
{
    use WriteLogs;

    private BulkOperationManager $operations;

    private DropletManager $droplet;

    public function __construct()
    {
        $this->operations = app(BulkOperationManager::class);

        $this->droplet = new DropletManager();

        if (!showing_api_docs()) {
            if ($this->droplet->isSmall()) {
                abort(401);
            }
        }
    }

    public function listOperationTypes()
    {
        return $this->operations->listTypes();
    }

    public function storeOperation($type, Request $request)
    {
        $operation = $this->operations->find($type);

        if (!$operation) {
            abort(404);
        }

        $instance = $operation->createInstance($request);

        $operation->run($instance);

        return $instance;
    }

    public function reRun(BulkOperationInstance $instance)
    {
        $this
            ->operations
            ->ofInstance($instance)
            ?->reRun($instance);

        return [
            'result' => 'ok'
        ];
    }

    public function listInstances($type)
    {
        return $this->operations->find($type)?->instances(
            user: request()->user()
        );
    }

    public function listInstanceResults(BulkOperationInstance $instance)
    {
        return $this
            ->operations
            ->ofInstance($instance)
            ->results($instance);
    }

    public function exportCsv(BulkOperationInstance $instance)
    {
        $this->operations
            ->ofInstance($instance)
            ->exportCsv($instance);
    }

    public function csvSample($type)
    {
        $operation = $this->operations->find($type);

        if (!$operation instanceof BaseImportOperation) {
            abort(404);
        }

        $operation->downloadCsvSample();
    }

    public function editInstanceName(BulkOperationInstance $instance)
    {
        $name = request()->input('name');

        if (empty($name)) {
            abort(422, t('Name is required'));
        }

        $instance->name = $name;

        $instance->save();

        return $instance;
    }

    public function deleteInstance(BulkOperationInstance $instance)
    {
        $instance->delete();

        return $instance;
    }

    public function deleteAllQRCodesOfInstance(BulkOperationInstance $instance)
    {
        $this->operations->ofInstance($instance)->deleteAllQRCodes($instance);

        $instance->delete();

        return ['result' => 'ok'];
    }

    public function print(BulkOperationInstance $instance)
    {
        if (!$instance->checkPrintSignature(request()->input('signature'))) {
            abort(401, 'You are not allowed to print this instance');
        }

        $qrcodeIds = collect($instance->results)
            ->map(
                fn($item) => (new ImportUrlQRCodesOperationResult)
                    ->fromInstanceResult(
                        $item
                    )->id
            );

        $qrcodes = QRCode::whereIn('id', $qrcodeIds->all())->get();

        return view('qrcode.bulk.print', [
            'qrcodes' => $qrcodes
        ]);
    }

    public function processCutContour(File $file)
    {
        if (empty(request()->user())) {
            abort(401);
        }

        $file = CutContourInjector::withFile($file)
            ->process()
            ->saveResult(
                request()->user()
            );

        return [
            'file' => $file,
            'url' => $file->getUrl(),
        ];
    }
}
