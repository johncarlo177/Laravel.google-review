<?php

namespace App\Support;

use App\Interfaces\BlogPostManager;
use App\Interfaces\FileManager;
use App\Interfaces\TranslationManager;
use App\Models\Config;
use App\Models\ContentBlock;
use App\Models\CustomCode;
use App\Models\File;
use App\Plugins\PluginManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ContentManager
{
    use WriteLogs;

    private static TranslationManager $translations;

    private static FileManager $files;

    private static $blocks = null;
    private static $customCodes = null;

    protected static $bodyClass = null;

    public static function setBodyClass($class)
    {
        static::$bodyClass = $class;
    }

    private function files()
    {
        if (!isset($this::$files)) {
            $this::$files = app(FileManager::class);
        }

        return $this::$files;
    }

    private function translations()
    {
        if (!isset($this::$translations)) {
            $this::$translations = app(TranslationManager::class);
        }

        return $this::$translations;
    }

    public function __construct(BlogPostManager $posts, TranslationManager $translations) {}

    public function bodyClass($classes = '')
    {
        if (static::$bodyClass) {
            return sprintf('class="%s"', static::$bodyClass);
        }

        $path = $this->getRequestPath();

        return "class='path-$path $classes'";
    }

    protected function getRequestPath()
    {
        if (preg_match('/cache/i', request()->path())) {
            return 'base';
        }

        $slug = Str::slug(request()->path());

        return empty($slug) ? 'base' : $slug;
    }

    protected function createDefaultBlock($position, $content)
    {
        $block = new ContentBlock();

        $block->position = $position;

        $block->content = $content;

        $block->save();

        return $block;
    }

    public function block(
        $position,
        $default = '',
        $noParagraph = true,
        $join = true
    ) {

        $this->addPositionIfNeeded($position);

        if (!$this->hasAnyBlocks($position)) {

            $this->createDefaultBlock(
                $position,
                content: $default
            );
        }
        return $this->contentBlocks($position, $noParagraph, $join) ?: $default;
    }

    public function contentBlocks($position, $noParagraph = true, $join = true)
    {
        $this->addPositionIfNeeded($position);

        $content = $this->getBlocksHtml($position, $join);

        if ($noParagraph === true) {
            $content = preg_replace('/<p\>(.*)<\/p\>/', '$1', $content);
        }

        if (is_string($content))

            $content = trim($content);

        return $content;
    }

    private function getBlocksHtml($position, $join)
    {
        $blocks = $this->getBlocks($position)->map(function ($block) {
            return $block->content_html;
        });

        if ($join)
            return $blocks->join("\n");

        return $blocks;
    }

    private function getBlocks($position)
    {
        $this->buildBlocks();

        return collect(@$this::$blocks[$position]);
    }

    private function applyCurrentLanguageRestrictions(Builder $query)
    {
        $translation = $this->translations()->getCurrentTranslation();

        if ($translation) {
            $query->where('translation_id', $translation->id);
        }
    }

    private function buildBlocks()
    {
        if ($this::$blocks !== null) {
            return;
        }

        $query = ContentBlock::query();

        $this->applyCurrentLanguageRestrictions($query);

        /**
         * @var Collection<ContentBlock>
         */
        $blocks = $query
            ->orderBy('sort_order', 'asc')
            ->get();

        $blocks = $blocks->reduce(function ($result, ContentBlock $block) {

            $result[$block->position][] = $block;

            return $result;
        }, []);

        $this::$blocks = $blocks;
    }

    public function customCodeTemplate($position)
    {
        return sprintf(
            '<template class="custom-code" position="%s">%s</template>',
            $position,
            base64_encode($this->customCode($position))
        );
    }

    private function getCustomCode($position)
    {
        if (is_array($this::$customCodes)) {
            return collect(@$this::$customCodes[$position]);
        }

        $codes = CustomCode::orderBy('sort_order', 'asc')
            ->get();

        $this::$customCodes = $codes->reduce(function ($result, CustomCode $code) {

            $result[$code->position][] = $code;

            return $result;
        }, []);

        return collect(@$this::$customCodes[$position]);
    }

    public function customCode($position)
    {
        $this->addPositionIfNeeded($position, 'custom-code-positions');

        $codes = '';

        try {

            $codes = $this->getCustomCode($position)
                ->map(function ($model) {

                    if ($model->language === 'javascript') {
                        return "<script>$model->code</script>";
                    }

                    if ($model->language === 'css') {
                        return "<style>$model->code</style>";
                    }

                    return $model->code;
                })
                ->join("\n\r");
        } catch (Throwable $th) {
            if (config('app.installed'))
                Log::error('Error while rendering custom code. ' . $th->getMessage());
        }

        return $codes;
    }

    private function shouldTryToAddPosition()
    {
        if (app()->environment('local')) return true;

        return config('app.should_build_custom_code_positions', false);
    }

    public function addPositionIfNeeded($position, $configKey = 'positions')
    {
        if (!$this->shouldTryToAddPosition()) return;

        $positions = $this->getPositions($configKey);

        $found = array_filter($positions, fn($p) => $p == $position);

        if (!$found) {

            $positions[] = $position;

            sort($positions);

            ConfigFileManager::saveJson('content-manager.' . $configKey, $positions);
        }
    }

    private function getPositions($configKey = 'positions')
    {
        if (app()->environment('local') && function_exists('eval')) {
            $code = file_get_contents(
                base_path('config/content-manager.php')
            );

            $code = str_replace('<?php', '', $code);

            $configs = eval($code);

            $positions = $configs[$configKey];
        } else {
            $positions = config('content-manager.' . $configKey, '');
        }

        $positions = json_decode($positions) ?: [];

        sort($positions);

        return $positions;
    }

    public function renderConfigThemeStyles()
    {
        $configKeys = [
            'theme.primary_0',
            'theme.primary_1',
            'theme.accent_0',
            'theme.accent_1',
            'theme.input_placeholder_font_style',
            'theme.dynamic_ribbon_color',
            'theme.checkout_page_gradient_color_1',
            'theme.checkout_page_gradient_color_2',
            'theme.dashboard_sidebar_background_color',
            'theme.dashboard_sidebar_text_color',
            'theme.dashboard_sidebar_hover_text_color',
            'theme.dashboard_sidebar_hover_background_color',
            'theme.dashboard_sidebar_label_color',
            'theme.blue.primary_0',
            'theme.blue.primary_1',
            'theme.blue.accent_0',
            'theme.blue.accent_1',
            'theme.active_step_background_color',
            'theme.active_step_text_color',
        ];

        $cssVar = function ($key) {
            $str = str_replace('theme.', '', $key);
            $str = str_replace('blue.', 'blue-', $str);
            $str = str_replace('_', '-', $str);
            return "--$str";
        };

        $styles = array_reduce($configKeys, function ($result, $key) use ($cssVar) {
            $color = config($key);

            if ($color) {
                $result .= sprintf("%s: %s; \n", $cssVar($key), $color);
            }

            return $result;
        }, '');

        $styles = trim($styles);

        return "<style> :root { $styles } </style>";
    }

    /**
     * Search if there are any blocks in any position that matches $search
     */
    public function hasAnyBlocks($search)
    {
        $this->buildBlocks();

        try {
            if ($search[0] != '/') {
                $search = "/$search/";
            }

            $positions = collect($this->getPositions())->filter(
                fn($p) => preg_match($search, $p)
            )->values();

            $found = $positions->reduce(function ($result, $position) {
                return $result || !empty(@$this::$blocks[$position]);
            }, false);

            return $found;
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return false;
        }
    }

    public function websiteBannerSrc()
    {
        try {
            $fileId = Config::get('appearance.website_banner');

            return $this->files()->url(File::find($fileId));
        } catch (Throwable $th) {
            return null;
        }
    }

    public function renderAfterBlogSectionAction()
    {
        return PluginManager::doAction(
            PluginManager::ACTION_HOME_PAGE_AFTER_BLOG_SECTION
        );
    }
}
