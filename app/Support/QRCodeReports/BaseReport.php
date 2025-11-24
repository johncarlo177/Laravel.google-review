<?php

namespace App\Support\QRCodeReports;

use App\Models\QRCode;
use App\Models\QRCodeScan;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseReport
{
    protected QRCode $qrcode;

    protected ?Carbon $from, $to;

    /**
     *  
     * @var \Illuminate\Database\Query\Builder 
     **/
    protected $query;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $result;


    protected $defaultDays = 15;

    public function __construct()
    {
        $this->from = null;
        $this->to = null;
        $this->result = collect([]);

        $this->logDatabaseQueries();
    }

    public static function of(QRCode $qrcode)
    {
        $instance = new static;

        $instance->qrcode = $qrcode;

        return $instance;
    }

    public abstract function slug(): string;

    protected abstract function reportColumn();

    public function from($date)
    {
        if ($date) {
            $this->from = new Carbon($date);
        }

        return $this;
    }

    public function to($date)
    {
        if ($date)
            $this->to = new Carbon($date);

        return $this;
    }

    protected function initDates()
    {
        if (!$this->from && $this->to) {
            $this->from = $this->to->clone()->subDays($this->defaultDays);
        }

        if ($this->from && !$this->to) {
            $this->to = $this->from->clone()->addDays($this->defaultDays);
        }

        if (!$this->from && !$this->to) {
            $this->to = Carbon::now();

            $this->from = $this->to->clone()->subDays($this->defaultDays);
        }
    }

    protected function shouldLogQueries()
    {
        return false;
    }

    private function logDatabaseQueries()
    {
        if (!$this->shouldLogQueries()) return;

        DB::listen(function ($query) {

            $sql = $query->sql;

            $bindings = $query->bindings;

            $sql_with_bindings = preg_replace_callback('/\?/', function ($match) use ($sql, &$bindings) {
                return json_encode(array_shift($bindings));
            }, $sql);

            Log::info(
                $sql_with_bindings
            );
        });
    }

    protected function applyDates()
    {
        $from = $this->from->clone()->subDay();
        $to = $this->to->clone()->addDay();

        $this->query->where('created_at', '>=', $from->format('Y-m-d'));
        $this->query->where('created_at', '<=', $to->format('Y-m-d'));
    }

    protected function selectReportColumn()
    {
        $this->query->selectRaw($this->reportColumn());
    }

    protected function applyGroup()
    {
        $this->query->groupBy($this->reportColumn());
    }

    protected function generateResult()
    {
        $this->result = $this->query->get();
    }

    protected function selectCount()
    {
        $this->query->selectRaw('COUNT(*) AS "scans"');
    }

    protected function applyReportColumnNotEmpty()
    {
        if (!empty($this->reportColumn())) {
            $this->query->whereNotNull($this->reportColumn());

            $this->query->whereRaw(
                sprintf('CHAR_LENGTH(%s) > 0', $this->reportColumn())
            );
        }
    }

    protected function orderBy()
    {
    }

    public function generate()
    {
        if (empty($this->qrcode)) {
            throw new Exception('You should call ' . $this::class . '::of($qrcode) before calling generate().');
        }

        $this->query = QRCodeScan::where('qrcode_id', $this->qrcode->id);

        $this->initDates();

        $this->applyDates();

        $this->selectCount();

        $this->applyReportColumnNotEmpty();

        $this->selectReportColumn();

        $this->orderBy();

        $this->applyGroup();

        $this->generateResult();

        $this->padResult();

        $this->formatResult();

        return $this->result;
    }

    protected function padResult()
    {
    }

    protected function formatResult()
    {
    }
}
