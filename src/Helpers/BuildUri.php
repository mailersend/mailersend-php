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
            if (is_null($value)) {
                continue;
            } elseif (is_array($value)) {
                $value = implode(',', array_map(fn ($item) => rawurlencode((string) $item), $value));
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            } else {
                $value = rawurlencode((string) $value);
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
