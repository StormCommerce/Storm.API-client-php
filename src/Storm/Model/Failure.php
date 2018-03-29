<?php


namespace Storm\Model;


use Carbon\Carbon;
use Storm\Model\Support\Arrayable;
use Storm\Model\Support\StormModel;
use Storm\StormClient;
use Storm\Util\Str;

class Failure extends StormModel
{
    public function __construct(array $attributes)
    {
        $attributes['body'] = Str::searchReplace("\r\n",'',$attributes['body']);
        parent::__construct($attributes);
        $this->saveToLog();
    }

    public function saveToLog()
    {
        $log = $this->getLog();
        $data = [];

        foreach ($this->params as $param) {
            if($param instanceof Arrayable) {
                $data = $param;
            }
        }
        if(isset($this->params['data']) && $this->params['data'] instanceof Arrayable) {
            $data = $this->params['data']->toJson();
        }
        $oldBody = $this->body;
        $this->body = json_decode($this->body);
        $prepend = [
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'error' => $this->toJson(),
            'data' => $data,
        ];
        array_unshift($log,$prepend);
        $log = array_slice($log,0,50);
        StormClient::self()->cache()->forever('errors',$log);
        $this->body = $oldBody;
    }

    public function getLog() {
        return StormClient::self()->cache()->get('errors',[]);
    }

    public function message()
    {
        $json = json_decode($this->body, true);
        if (isset($json['Message'])) {
            return $json['Message'];
        }
        return '';
    }
}