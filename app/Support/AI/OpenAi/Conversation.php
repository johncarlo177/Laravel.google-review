<?php

namespace App\Support\AI\OpenAi;

use App\Support\AI\OpenAi\Models\Input;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Collection;
use App\Support\AI\OpenAi\Models\Response;

class Conversation
{
    use WriteLogs;

    /**
     * @var Collection
     */
    protected $inputs = null;

    /**
     * @var Response
     */
    protected $response = null;

    protected $onInputChanged = null;

    public static function withInputs($inputs)
    {
        $instance = new static;

        $instance->inputs = $inputs;

        return $instance;
    }

    public static function start($systemPrompt)
    {
        // 
        $instance = new static;

        $instance->inputs = collect(
            [Input::system($systemPrompt)]
        );

        return $instance;
    }

    public function withInputChangeListener($callback)
    {
        $this->onInputChanged = $callback;

        return $this;
    }

    public function record($input)
    {
        $this->push(Input::user($input));

        return $this;
    }

    public function send()
    {
        $input = $this->inputs->all();

        $api = Api::withInput($input);

        $this->response = $api->send();

        $output = $this->response->output[0];

        $this->push(Input::assistant($output->id, $output->content));

        return $this;
    }

    protected function push(Input $input)
    {
        $this->inputs->push($input);

        if (is_callable($this->onInputChanged)) {
            call_user_func($this->onInputChanged, $this->inputs);
        }
    }

    public function get()
    {
        return $this->response;
    }
}
