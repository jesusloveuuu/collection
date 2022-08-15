<?php


namespace App\Services;

use Illuminate\Support\Facades\Cache;
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
        'hl' => 'en-US',
        'tz' => 0,
        'geo' => 'US',
        'time' => 'all',
        'category' => 0,
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

    public function getOptionsCacheKey($prefix)
    {

        $options = $this->options;
        ksort($options);
        $options_key = $prefix;

        //日期开关
        if(0){
            $options_key .=":" . date('Y-m-d',time());
        }

        foreach ($options as $key => $value) {
            $options_key .= ":" . $key . ":" . $value;
        }
        return $options_key;
    }

    public function getSuggestionsAutocomplete(string $kWord): array
    {

        $gtrends = new GTrends($this->options);
        $array_gtrend = $gtrends->getSuggestionsAutocomplete($kWord) ?? [];
        Cache::set($this->getOptionsCacheKey('GTrends:'.__FUNCTION__.":".$kWord), json_encode($array_gtrend));

        return $array_gtrend;
    }

    public function getAllOneKeyWord(string $kWord): array
    {
        $gtrends = new GTrends($this->options);
        $array_gtrend = $gtrends->getAllOneKeyWord($kWord) ?? [];
        Cache::set($this->getOptionsCacheKey('GTrends:'.__FUNCTION__.":".$kWord), json_encode($array_gtrend));

        return $array_gtrend;
    }

    public function getRelatedTopics(string $kWord): array
    {
        $gtrends = new GTrends($this->options);
        $array_gtrend = $gtrends->getAllOneKeyWord($kWord) ?? [];
        Cache::set($this->getOptionsCacheKey('GTrends:'.__FUNCTION__.":".$kWord), json_encode($array_gtrend));

        return $array_gtrend;
    }




}
