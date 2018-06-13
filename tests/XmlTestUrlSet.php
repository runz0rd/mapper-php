<?php

use Traits\ConvertibleTrait;
use Traits\MappableTrait;

/**
 *
 * User: somov.nn@gmail.com
 * Date: 15.12.2017
 *
 * @xmlRoot urlset
 * @xmlNamespaces "http://www.sitemaps.org/schemas/sitemap/0.9" xhtml="http://www.w3.org/1999/xhtml"
 * @xmlEncoding windows-1251
 * @xmlVersion 1.0
 *
 */
class XmlTestUrlSet
{
    use MappableTrait;
    use ConvertibleTrait;

    /**
     * @var XmlTestUrl[]
     */
    public $url;


    public function __construct()
    {
        $url = new XmlTestUrl();
        $url->loc = 'loc_value_1';

        $this->url[] = $url;

        $url = new XmlTestUrl();
        $url->loc = 'loc_value_2';

        $this->url[] = $url;


    }

}