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
        if (0) {
            $options_key .= ":" . date('Y-m-d', time());
        }

        foreach ($options as $key => $value) {
            $options_key .= ":" . $key . ":" . $value;
        }

        //var_dump($options_key);
        return $options_key;
    }

    public function getSuggestionsAutocomplete(string $kWord): array
    {
        $cache_key = $this->getOptionsCacheKey('GTrends:' . __FUNCTION__ . ":" . $kWord);
        $cache_value = Cache::get($cache_key);

        if (empty($cache_value)) {
            $gtrends = new GTrends($this->options);
            $array_gtrend = $gtrends->getSuggestionsAutocomplete($kWord) ?? [];
            Cache::put($cache_key, json_encode($array_gtrend));
        } else {
            $array_gtrend = json_decode($cache_value, true);
        }

        return $array_gtrend;
    }

    public function getAllOneKeyWord(string $kWord): array
    {

        $t1 = time();

        $cache_key = $this->getOptionsCacheKey('GTrends:' . __FUNCTION__ . ":" . $kWord);

        $cache_value_array = $this->getArrayFromCacheJson($cache_key);

        if (empty($cache_value_array)) {
            $gtrends = new GTrends($this->options);
            $cache_value_array = $gtrends->getAllOneKeyWord($kWord);

            $this->setArrayToCacheJson($cache_key, $cache_value_array);
        }

        $t2 = time();

        echo "time: " . ($t2 - $t1) . ' s';
        $this->sleep_delay($t2 - $t1);

        return $cache_value_array ?? [];
    }

    public function getRelatedTopics(string $kWord): array
    {
        $cache_key = $this->getOptionsCacheKey('GTrends:' . __FUNCTION__ . ":" . $kWord);
        $cache_value = Cache::get($cache_key);

        if (empty($cache_value)) {
            $gtrends = new GTrends($this->options);
            $array_gtrend = $gtrends->getRelatedTopics($kWord) ?? [];
            Cache::put($cache_key, json_encode($array_gtrend));
        } else {
            $array_gtrend = json_decode($cache_value, true);
        }


        return $array_gtrend;
    }

    private function getArrayFromCacheJson($cache_key)
    {

        $cache_value = Cache::get($cache_key);
        $array = [];
        if (!empty($cache_value)) {
            $array = json_decode($cache_value, true) ?? [];
        }

        return $array;

    }

    private function setArrayToCacheJson($cache_key, $array)
    {
        $forever = false;
        if (!empty($array)) {
            $json_gtrend = json_encode($array, true);
            //var_dump(strlen($json_gtrend));
            //var_dump($array_gtrend);
            $forever = Cache::forever($cache_key, $json_gtrend);
            //var_dump($forever);
            if (!$forever) {
                var_dump("set cache failed!");
            }
        }

        return $forever;

    }

    private function sleep_delay($time = 10)
    {
        if ($time < 5) {
            $time = 1;
        } else if ($time > 60) {
            $time = 60;
        }

        for ($i = $time; $i > 0; $i--) {

            $random = random_int(0, $time);
            echo "($i,$random).";
            if ($random !== 0) {
                $i++;
            }
            sleep(1);//暂停，反反爬虫
        }

        echo "\n";

        return true;

    }


}
