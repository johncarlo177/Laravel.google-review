<?php

namespace App\Http\Controllers;

use App\Interfaces\ModelSearchBuilder;
use App\Models\Widget;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WidgetsController extends Controller
{
    use WriteLogs;

    private $search;

    public function __construct(ModelSearchBuilder $search)
    {
        $this->search = $search;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->search
            ->init(Widget::class, request())
            ->inColumn('name')
            ->withQuery(function ($query) {
                $query->where('user_id', $this->optionalUser()?->id);
            })
            ->search()
            ->paginate();
    }

    public function integrationData($id)
    {
        /**
         * @var Widget
         */
        $widget = Widget::where('uuid', $id)->first();

        return $this->response($widget);
    }

    protected function response(Widget $widget)
    {
        return array_merge($widget->toArray(), [
            'destination_url' => url($widget->qrcode?->redirect?->route),
            'icon_url' => file_url($widget->widget_icon_id),
            'logo_url' => file_url(config('frontend.header_logo_inverse')) ?: '/assets/images/logo.png',
        ]);
    }

    public function store(Request $request)
    {
        try {
            // 
            $widget = new Widget();

            $widget->forceFill($request->all());

            $widget->user_id = $this->optionalUser()->id;

            $widget->save();

            return $widget;
            // 
        } catch (Throwable $th) {
            $this->logDebug($th->getMessage());
        }

        return [
            'error' => true
        ];
    }

    public function show(Widget $widget)
    {
        return $this->response($widget);
    }

    public function update(Request $request, Widget $widget)
    {
        try {
            $inputs = $request->all();

            foreach ($inputs as $key => $value) {
                $this->logDebug('Adding (%s) to widget', $key);

                if (Schema::hasColumn('widgets', $key)) {
                    $widget->{$key} = $value;
                }
            }

            $widget->save();

            return $widget;
            // 
        } catch (Throwable $th) {
            $this->logDebug($th->getMessage());
        }

        return [
            'error' => true
        ];
    }

    public function destroy(Widget $widget)
    {
        $widget->delete();

        return $widget;
    }
}
