<?php

namespace IMDC\TerpTubeBundle\Consumer\Options;

class StatusConsumerOptions extends AbstractConsumerOptions
{
    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $who;

    /**
     * @var string
     */
    public $what;

    /**
     * @var string
     */
    public $identifier;

    public function pack()
    {
        return json_encode(array(
            'status' => $this->status,
            'who' => $this->who,
            'what' => $this->what,
            'identifier' => $this->identifier
        ));
    }

    public static function unpack($json)
    {
        $optsArray = json_decode($json, true);

        $getKey = function ($key, $default) use ($optsArray) {
            return array_key_exists($key, $optsArray) ? $optsArray[$key] : $default;
        };

        $opts = new self();
        $opts->status = $getKey('status', '');
        $opts->who = $getKey('who', '');
        $opts->what = $getKey('what', '');
        $opts->identifier = $getKey('identifier', '');

        return $opts;
    }
}
