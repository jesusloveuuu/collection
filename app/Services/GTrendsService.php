<?php


namespace App\Services;

use XFran\GTrends\GTrends;

use function array_key_exists;
use function count;
use function in_array;
use function stripos;
use function substr;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function unlink;


class GTrendsService
{
    private array $options = [
        'hl'        => 'en-US',
        'tz'        => 0,
        'geo'       => 'US',
        'time'      => 'all',
        'category'  => 0,
    ];

    public function __construct(array $options = [])
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): GTrendsService
    {
        $this->options = $options;
        return $this;
    }

    //记录日志表


}
