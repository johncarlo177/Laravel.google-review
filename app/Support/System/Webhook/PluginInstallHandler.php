<?php

namespace App\Support\System\Webhook;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;

class PluginInstallHandler extends BaseWebhook
{
    use WriteLogs;

    private string $pluginSlug;

    public function slug()
    {
        return 'plugin-install';
    }

    protected function handleWebhook(Request $request)
    {
        $this->pluginSlug = $request->payload['plugin_slug'];

        $this->logInfo('Handling installation request for %s', $this->pluginSlug);

        $this->downloadPlugin();

        $this->logInfo('Plugin archive downloaded successfully');

        $this->extractPlugin();

        $this->deletePluginArchive();

        $this->logInfo('Plugin archive deleted');
    }

    protected function downloadPlugin()
    {
        $url = sprintf(
            'https://quickcode.digital/plugin/download/%s?license_key=%s',
            $this->pluginSlug,
            config('app.purchase_code')
        );

        $this->downloadFile(
            $url,
            $this->pluginArchive()
        );
    }

    protected function extractPlugin()
    {
        return $this->extractArchive(
            $this->pluginArchive(),
            $this->pluginsFolder() . '/'
        );
    }

    protected function deletePluginArchive()
    {
        unlink($this->pluginArchive());
    }

    protected function pluginsFolder()
    {
        return base_path('app/Plugins');
    }

    protected function pluginArchive()
    {
        return sprintf('%s/%s.zip', $this->pluginsFolder(), $this->pluginSlug);
    }
}
