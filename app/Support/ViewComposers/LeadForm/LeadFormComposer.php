<?php

namespace App\Support\ViewComposers\LeadForm;

use App\Models\File;
use App\Models\LeadForm;
use App\Repositories\FileManager;
use App\Support\ArrayHelper;
use App\Support\Color;
use App\Support\QRCodeTypes\ViewComposers\Traits\CombinesStylesMethods;
use App\Support\ViewComposers\BaseComposer;
use Illuminate\View\View;

class LeadFormComposer extends BaseComposer
{
    use CombinesStylesMethods;

    private ?LeadForm $model = null;

    private $questions = [];

    public function __construct() {}

    private function shouldIgnoreDisabledConfig()
    {
        return @$this->view->getData()['ignoreDisabledConfig'];
    }

    public function shouldRender()
    {
        return $this->model != null && $this->isEnabled();
    }

    private function isEnabled()
    {
        if (
            $this->mode('full-page') ||
            $this->shouldIgnoreDisabledConfig()
        ) return true;

        return $this->config('enabled', 'disabled') === 'enabled';
    }

    private function mode($mode)
    {
        $m = @$this->view->getData()['mode'];

        return $m == $mode;
    }

    public static function path(): string
    {
        return 'qrcode.components.lead-form.lead-form|qrcode.components.lead-form.trigger';
    }

    public function isMultipleSubmissionAllowed()
    {
        return $this->model->isMultipleSubmissionAllowed();
    }

    public function afterSubmitUrl()
    {
        $url = @$this->model->configs['after_submit_url'];

        $url = empty($url) ? '' : $url;

        return $url;
    }

    public function config($name, $default = null)
    {
        return @$this->model->configs[$name] ?? $default;
    }

    public function compose(View $view)
    {
        parent::compose($view);

        $id = empty(@$view->getData()['id']) ? null : @$view->getData()['id'];

        if (!$id) return;

        $this->model = LeadForm::find($id);
    }

    protected function backgroundImageUrl()
    {
        $id = $this->config('background_image');

        $file = File::find($id);

        if (!$file) return;

        $url = (new FileManager)->url($file);

        return $url;
    }

    public function backgroundImageStyles()
    {
        if (!$this->backgroundImageUrl()) return;

        return sprintf(
            '%s { background-image: url(%s); }',
            $this->selector(''),
            $this->backgroundImageUrl()
        );
    }

    public function logoUrl()
    {
        $id = $this->config('logo_image');

        $file = File::find($id);

        if (!$file) return;

        return (new FileManager)->url($file);
    }

    public function pageBackgroundColorStyles()
    {
        $bg = $this->config('background_color');

        if ($this->backgroundImageUrl()) {
            $bg = 'transparent';
        }

        if (!$bg) return;


        return sprintf(
            '%s, %s, %s { background-color: %s; }',
            $this->selector('.question-page'),
            $this->selector('.questions'),
            $this->selector('.success-page'),
            $bg
        );
    }

    public function pageTextColorStyles()
    {
        $c = $this->config('text_color');

        if (!$c) return;

        $selectors = [
            '.answer.text',
            '.answer.date',
            '.answer.email',
            '.answer.text input',
            '.answer.date input',
            '.answer.email input',
            '.answer.textarea textarea',
        ];

        $selectors = array_map(fn($s) => '.question-container ' . $s, $selectors);

        $selectors[] = '.question-container';

        $selectors[] = '.success-page';

        $selectors[] = '.lead-form-header';

        $selectors = array_map(fn($s) => $this->selector($s), $selectors);

        $selectors = implode(', ', $selectors);

        return sprintf(
            '%s { color: %s; }',
            $selectors,
            $c
        );
    }

    public function buttonsBackgroundColorStyles()
    {
        $bg = $this->config('button_background_color');

        if (!$bg) return;

        return sprintf(
            '%s { background-color: %s; border-color: %s; }',
            $this->buttonsSelector(),
            $bg,
            Color::adjustBrightness($bg, -0.05)
        );
    }

    private function buttonsSelector()
    {
        return sprintf(
            '%s, %s',
            $this->selector('.ok-button .button'),
            $this->selector('.navigation .button')
        );
    }

    public function buttonsTextColorStyles()
    {
        $c = $this->config('button_text_color');

        if (!$c) return;

        return sprintf(
            '%s { color: %s; }',
            $this->buttonsSelector(),
            $c
        );
    }

    public function triggerButtonBgStyles()
    {
        $bg = $this->config('trigger_background_color');

        if (!$bg) return;

        return sprintf(
            '%s, %1$s:hover { background-color: %s; border-color: %s; }',
            'html .lead-form-trigger',
            $bg,
            $bg
        );
    }

    public function triggerButtonTextStyles()
    {
        $c = $this->config('trigger_text_color');

        if (!$c) return;

        return sprintf(
            '%s, %1$s:hover { color: %s; }',
            'html .lead-form-trigger',
            $c
        );
    }

    public function outputColorVarsStyles()
    {
        $placeholder = $this->inputPlaceholderColor() ?? '#eee';
        $textColor = $this->config('text_color') ?? 'white';

        return sprintf(
            '%s { --placeholder-color: %s; --text-color: %s; }',
            $this->selector(''),
            $placeholder,
            $textColor
        );
    }

    public function placeholderColorStyles()
    {
        if (!$this->inputPlaceholderColor()) return;

        return sprintf(
            '%s, %s { color: %s; }',
            $this->selector('.answer.text input::placeholder'),
            $this->selector('.answer.textarea textarea::placeholder'),
            $this->inputPlaceholderColor()
        );
    }

    private function inputPlaceholderColor()
    {
        $c = $this->config('placeholder_color');

        if (!$c) return;

        return $c;
    }

    public function inputBorderColorStyles()
    {
        if (!$this->inputPlaceholderColor()) return;

        $selectors = [
            '.answer input',
            '.answer textarea',
        ];

        $selectors = array_map(fn($s) => $this->selector($s), $selectors);

        $selectors = implode(', ', $selectors);

        return sprintf(
            '%s, %s { border-color: %s; }',
            $this->selector('.answer.text input'),
            $this->selector('.answer.textarea textarea'),
            $this->inputPlaceholderColor()
        );
    }

    private function selector($selector)
    {
        return sprintf('.lead-form.lead-form-%s %s', $this->id(), $selector);
    }

    public function id()
    {
        return $this->model->id;
    }

    public function questions()
    {
        if (!empty($this->questions)) {
            return $this->questions;
        }

        $questions = @$this->model->fields ?? [];

        ArrayHelper::sort($questions);

        $this->questions = $questions;

        return $questions;
    }

    public function hasQuestions()
    {
        return !empty($this->questions());
    }

    public function questionNumber($question)
    {
        return array_search($question, $this->questions()) + 1;
    }

    public function questionText($question)
    {
        return @$question['text'];
    }

    public function shouldRenderQuestionDescription($question)
    {
        return !empty(@$question['description']);
    }

    public function questionDescription($question)
    {
        return @$question['description'];
    }

    public function isQuestionRequired($question)
    {
        $value = @$question['required'];

        return $value === 'required';
    }

    public function isLastQuestion($question)
    {
        return array_search(
            $question,
            $this->questions()
        ) == count(
            $this->questions()
        ) - 1;
    }


    public function submitButtonText()
    {
        return $this->config('submit_button_text', t('Submit'));
    }

    public function okButtonText()
    {
        return $this->config('ok_button_text', t('OK'));
    }
}
