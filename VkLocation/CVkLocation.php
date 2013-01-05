<?php
/**
 @todo documentation & comments
 */

Yii::import('zii.widgets.jui.CJuiInputWidget');

class CVkLocation  extends CWidget
{
    public $dataSource = "index.php?r=site/vkLocation.dataSource";

    public $model;
    public $countryIdAttribute;
    public $cityIdAttribute;
    public $cityNameAttribute;

    public $countryIdHtmlPre;
    public $countryIdHtmlPost;
    public $cityNameHtmlPre;
    public $cityNameHtmlPost;

    public $countryIdOptions;
    public $cityNameOptions;
    public $autoCompleteOptions;

    private $cityIdOptions; // private since city_id field is hidden

    public static function getCountryOptions()
    {
        return self::listCountries(true);
    }

    public static function countryText($key)
    {
        $options = self::getCountryOptions();
        return $options[$key];
    }

    static function json_encode_utf($array)
    {
        $json = preg_replace_callback('/\\\u(\w\w\w\w)/',
            function($matches) {
                return '&#'.hexdec($matches[1]).';';
            }
            , json_encode($array));
        return $json;
    }

    static function isUTF8($string) {
        return (utf8_encode(utf8_decode($string)) == $string);
    }

    static function getContent($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $tmp = curl_exec($ch);
        curl_close($ch);
        if ($tmp != false){
            return $tmp;
        }
    }

    static function vkSelectAjax($query)
    {
        $content = self::getContent("http://vk.com/select_ajax.php?{$query}");
        return $content;
    }

    public static function getCityInfo($city_id)
    {
        return "fah";
    }

    public static function suggestCities($country, $query)
    {
        $content = self::vkSelectAjax("act=a_get_cities&country={$country}&str={$query}");
        $json = str_replace("'", "\"", $content);

        $suggestions = json_decode($json);
        if (isset($suggestions->cities))
            $suggestions = $suggestions->cities;

        $result = array();
        if ($suggestions) foreach ($suggestions as $e)
        {
            $result[] = array(
                'id'=>$e[0],
                'label'=>strip_tags($e[1]),
                'region'=>$e[2]
            );
        }
        return $result;
    }

    public static function listLargeCities($country)
    {
        $json = self::vkSelectAjax("act=a_get_country_info&country={$country}&fields=1");
        $json = iconv("windows-1251", "utf-8", $json);

        $large_cities = json_decode($json);
        if (isset($large_cities->cities))
            $large_cities = $large_cities->cities;

        $result = array();
        if ($large_cities) foreach ($large_cities as $e)
        {
            $result[] = array(
                'id'=>$e[0],
                'label'=>strip_tags($e[1]),
            );
        }
        return $result;
    }

    public static function listCountries($justValues = false)
    {
        $json = self::vkSelectAjax("act=a_get_countries");
        $json = iconv("windows-1251", "utf-8", $json);

        $countries = json_decode($json);
        if (isset($countries->countries))
            $countries = $countries->countries;


        usort($countries, function($a, $b){ return $a[0] - $b[0];});

        if ($justValues)
        {
            $result = array();
            if ($countries) foreach ($countries as $e)
            {
                $result[$e[0]] = strip_tags($e[1]);
            }
            return $result;
        }

        $result = array();
        if ($countries) foreach ($countries as $e)
        {
            $result[] = array(
                'id'=>$e[0],
                'label'=>strip_tags($e[1]),
            );
        }
        return $result;
    }

    public function init()
    {
        $cs = Yii::app()->getClientScript();
        $assets = Yii::app()->getAssetManager()->publish(dirname(__FILE__) . '/assets');
        $cs->registerScriptFile($assets . '/jquery.ui.widget.min.js');
        $cs->registerScriptFile($assets . '/jquery.ui.combobox.js');

        parent::init();
    }

    public function run()
    {
        // create country id input
        CHtml::resolveNameID($this->model, $this->countryIdAttribute, $this->countryIdOptions);
        echo $this->countryIdHtmlPre;
        echo CHtml::activeDropDownList($this->model, $this->countryIdAttribute,
            self::listCountries(true), $this->countryIdOptions);
        echo $this->countryIdHtmlPost;

        // create city id input
        CHtml::resolveNameID($this->model, $this->cityIdAttribute, $this->cityIdOptions);
        echo CHtml::activeHiddenField($this->model, $this->cityIdAttribute);

        // create city name input and assign dependencies
        CHtml::resolveNameID($this->model, $this->cityNameAttribute, $this->cityNameOptions);
        $this->cityNameOptions['country_id'] = $this->countryIdOptions['id'];
        $this->cityNameOptions['city_id'] = $this->cityIdOptions['id'];
        $this->cityNameOptions['data_source'] = $this->dataSource;
        echo $this->cityNameHtmlPre;
        echo CHtml::activeTextField($this->model, $this->cityNameAttribute, $this->cityNameOptions);
        echo $this->cityNameHtmlPost;

        // create javascript code to apply widget on the inputs
        $cityNameField = $this->cityNameOptions['id'];
        $autoCompleteOptions = CJavaScript::encode($this->autoCompleteOptions);
        $js = "jQuery('#{$cityNameField}').combobox({$autoCompleteOptions});";

        $cs = Yii::app()->getClientScript();
        $cs->registerScript(__CLASS__ . '#' . $cityNameField, $js);
    }

    static public function actions()
    {
        return array(
            'dataSource'=>array('class'=>'application.components.VkLocation.actions.dataSource'),
        );
    }
}