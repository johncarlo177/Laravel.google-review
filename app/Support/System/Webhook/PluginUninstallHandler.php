<?php

namespace App\Support\System\Webhook;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PluginUninstallHandler extends BaseWebhook
{
    use WriteLogs;

    private string $pluginSlug;

    public function slug()
    {
        return 'plugin-uninstall';
    }

    protected function handleWebhook(Request $request)
    {
        $this->pluginSlug = $request->payload['plugin_slug'];

        $this->logInfo('Uninstalling plugin %s', $this->pluginSlug);

        $this->deletePluginFolder()();

        $this->logInfo('Plugin %s uninstalled successfully');
    }

    protected function pluginsFolder()
    {
        return base_path('app/Plugins');
    }

    protected function deletePluginFolder()
    {
        File::deleteDirectory($this->pluginsFolder() . '/' . $this->pluginFolderName());
    }

    protected function pluginFolderName()
    {
        $files = glob($this->pluginsFolder() . '/*/Plugin.php');

        return collect($files)
            ->filter(function ($file) {
                return preg_match(sprintf("/%s/i", $this->pluginSlug), $file);
            })->reduce(function ($result, $file) {
                $result = str_replace($this->pluginsFolder(), '', $file);

                $result = str_replace('/Plugin.php', '', $result);

                $result = str_replace('/', '', $result);

                return $result;
            });
    }
}
