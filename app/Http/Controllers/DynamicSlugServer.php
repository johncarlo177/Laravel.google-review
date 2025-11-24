<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\QRCodeRedirect;
use Illuminate\Http\Request;

class DynamicSlugServer extends Controller
{
    private Request $request;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $this->request = $request;

        if ($this->isPageRoute()) {
            return $this->renderPageRoute();
        }

        if ($this->isQRCodeRoute()) {
            return $this->renderQRCodeRoute();
        }

        return abort(404);
    }

    private function renderQRCodeRoute()
    {
        return QRCodeRedirectController::serveSlug($this->slug());
    }

    private function isQRCodeRoute()
    {
        $qrcodeRedirect = QRCodeRedirect::whereSlug(
            $this->slug()
        )->first();

        return !empty($qrcodeRedirect);
    }

    private function slug()
    {
        return $this->request->route('slug');
    }

    private function isPageRoute()
    {
        $page = Page::whereSlug($this->slug())->first();

        return !empty($page);
    }

    private function renderPageRoute()
    {
        if (frontend_custom_url()) {
            return redirect(frontend_custom_url($this->request->path()));
        }

        return app()->call(PageController::class . '@viewPage');
    }
}
