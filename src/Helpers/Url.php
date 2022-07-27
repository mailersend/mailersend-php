<?php

namespace MailerSend\Helpers;

class Url
{
    protected array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function execute(string $path, array $params = []): string
    {
        $paramsString = http_build_query($params);

        return $this->options['protocol'].'://'.
            $this->options['host'].
            '/'.$this->options['api_path'].
            '/'.$path.
            ($paramsString ? '?'.$paramsString : '');
    }
}
