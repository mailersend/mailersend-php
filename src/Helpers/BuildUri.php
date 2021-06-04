<?php

namespace MailerSend\Helpers;

class BuildUri
{
    protected array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function execute(string $path, array $params = []): string
    {
        $paramsArray = [];

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $paramsArray[] = $key.'='.$value;
        }

        $paramsString = implode('&', $paramsArray);

        return $this->options['protocol'].'://'.
            $this->options['host'].
            '/'.$this->options['api_path'].
            '/'.$path.
            ($paramsString ? '?'.$paramsString : '');
    }
}
