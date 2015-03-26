<?php
namespace SEOstats\Services;

/**
 * SEOstats extension for SEMRush data.
 *
 * @package    SEOstats
 * @author     Stephan Schmitz <eyecatchup@gmail.com>
 * @copyright  Copyright (c) 2010 - present Stephan Schmitz
 * @license    http://eyecatchup.mit-license.org/  MIT License
 * @updated    2013/08/14
 */

use SEOstats\Common\SEOstatsException as E;
use SEOstats\SEOstats as SEOstats;
use SEOstats\Config as Config;
use SEOstats\Helper as Helper;

class SemRush extends SEOstats
{
    public static function getDBs()
    {
        return array(
            "au",     # Google.com.au (Australia)
            "br",     # Google.com.br (Brazil)
            "ca",     # Google.ca (Canada)
            "de",     # Google.de (Germany)
            "es",     # Google.es (Spain)
            "fr",     # Google.fr (France)
            "it",     # Google.it (Italy)
            "ru",     # Google.ru (Russia)
            "uk",     # Google.co.uk (United Kingdom)
            'us',     # Google.com (United States)
            "us.bing" # Bing.com
        );
    }

    public static function getParams()
    {
        return array(
          "DomainReports" => array(
            "Ac" => "Estimated expenses the site has for advertising in Ads (per month).",
            "Ad" => "Number of Keywords this site has in the TOP20 Ads results.",
            "At" => "Estimated number of visitors coming from Ads (per month).",
            "Dn" => "The requested site name.",
            "Dt" => "The date when the report data was computed (formatted as YYYYmmdd).",
            "Np" => "The number of keywords for which the site is displayed in search results next to the analyzed site.",
            "Oa" => "Estimated number of potential ad/traffic buyers.",
            "Oc" => "Estimated cost of purchasing the same number of visitors through Ads.",
            "Oo" => "Estimated number of competitors in organic search.",
            "Or" => "Number of Keywords this site has in the TOP20 organic results.",
            "Ot" => "Estimated number of visitors coming from the first 20 search results (per month).",
            "Rk" => "The SEMRush Rank (rating of sites by the number of visitors coming from the first 20 search results)."
          ),
          "OrganicKeywordReports" => array(
            "Co" => "Competition of advertisers for that term (the higher the number - the greater the competition).",
            "Cp" => "Average price of a click on an Ad for this search query (in U.S. dollars).",
            "Nr" => "The number of search results - how many results does the search engine return for this query.",
            "Nq" => "Average number of queries for the keyword per month (for the corresponding local version of search engine).",
            "Ph" => "The search query the site has within the first 20 search results.",
            "Po" => "The site&#39;s position for the search query (at the moment of data collection).",
            "Pp" => "The site&#39;s position for the search query (at the time of prior data collection).",
            "Tc" => "The estimated value of the organic traffic generated by the query as compared to the cost of purchasing the same volume of traffic through Ads.",
            "Tr" => "The ratio comparing the number of visitors coming to the site from this search request to all visitors to the site from search results.",
            "Ur" => "URL of a page on the site displayed in search results for this query (landing page)."
          )
        );
    }

    /**
     * Returns the SEMRush main report data.
     * (Only main report is public available.)
     *
     * @access       public
     * @param   url  string             Domain name only, eg. "ebay.com" (/wo quotes).
     * @param   db   string             Optional: The database to use. Valid values are:
     *                                  au, br, ca, de, es, fr, it, ru, uk, us, us.bing (us is default)
     * @return       array              Returns an array containing the main report data.
     * @link         http://www.semrush.com/api.html
     */
    public static function getDomainRank($url = false, $db = false)
    {
        $data = self::getBackendData($url, $db, 'domain_rank');

        return is_array($data) ? $data['rank']['data'][0] : $data;
    }

    public static function getDomainRankHistory($url = false, $db = false)
    {
        $data = self::getBackendData($url, $db, 'domain_rank_history');

        return is_array($data) ? $data['rank_history'] : $data;
    }

    public static function getOrganicKeywords($url = false, $db = false)
    {
        return static::getWidgetData($url, $db, 'organic', 'organic');
    }

    public static function getCompetitors($url = false, $db = false)
    {
        return static::getWidgetData($url, $db, 'organic_organic', 'organic_organic');
    }

    public static function getDomainGraph($reportType = 1, $url = false, $db = false, $w = 400, $h = 300, $lc = 'e43011', $dc = 'e43011', $lang = 'en', $html = true)
    {
        $domain = static::getDomainFromUrl($url);
        $database = static::getValidDatabase($db);

        static::guardValidArgsForGetDomainGraph($reportType, $w, $h, $lang);

        $imgUrl = sprintf(Config\Services::SEMRUSH_GRAPH_URL,
            $domain, $database, $reportType, $w, $h, $lc, $dc, $lang);

        if (! $html) {
            return $imgUrl;
        } else {
            $imgTag = '<img src="%s" width="%s" height="%s" alt="SEMRush Domain Trend Graph for %s"/>';
            return sprintf($imgTag, $imgUrl, $w, $h, $domain);
        }
    }

    protected static function getApiData($url)
    {
        $json = static::_getPage($url);
        return Helper\Json::decode($json, true);
    }

    protected static function getSemRushDatabase($db)
    {
        return false !== $db
            ? $db
            : Config\DefaultSettings::SEMRUSH_DB;
    }

    protected static function guardDomainIsValid($domain)
    {
        if (false == $domain) {
            self::exc('Invalid domain name.');
        }
    }

    protected static function guardDatabaseIsValid($database)
    {
        if (false === $database) {
            self::exc('db');
        }
    }

    protected static function guardValidArgsForGetDomainGraph($reportType, $width, $height, $lang)
    {
        if ($reportType > 5 || $reportType < 1) {
            self::exc('Report type must be between 1 (one) and 5 (five).');
        }

        if ($width > 400 || $width < 200) {
            self::exc('Image width must be between 200 and 400 px.');
        }

        if ($height > 300 || $height < 150) {
            self::exc('Image height must be between 150 and 300 px.');
        }

        if (strlen($lang) != 2) {
            self::exc('You must specify a valid language code.');
        }
    }

    protected static function getBackendData($url, $db, $reportType)
    {
        $db      = false !== $db ? $db : Config\DefaultSettings::SEMRUSH_DB;
        $dataUrl = self::getBackendUrl($url, $db, $reportType);
        $data    = self::getApiData($dataUrl);

        if (!is_array($data)) {
            $data = self::getApiData(str_replace('.backend.', '.api.', $dataUrl));
            if (!is_array($data)) {
                return parent::noDataDefaultValue();
            }
        }

        return $data;
    }

    protected static function getBackendUrl($url, $db, $reportType)
    {
        $domain = static::getDomainFromUrl($url);
        $database = static::getValidDatabase($db);

        $backendUrl = Config\Services::SEMRUSH_BE_URL;
        return sprintf($backendUrl, $database, $reportType, $domain);
    }

    protected static function getWidgetUrl($url, $db, $reportType)
    {
        $domain = static::getDomainFromUrl($url);
        $database = static::getValidDatabase($db);

        $widgetUrl = Config\Services::SEMRUSH_WIDGET_URL;
        return sprintf($widgetUrl, $reportType, $database, $domain);
    }

    protected static function getWidgetData($url, $db, $reportType, $valueKey)
    {
        $db      = false !== $db ? $db : Config\DefaultSettings::SEMRUSH_DB;
        $dataUrl = self::getWidgetUrl($url, $db, $reportType);
        $data    = self::getApiData($dataUrl);

        return !is_array($data) ? parent::noDataDefaultValue() : $data[ $valueKey ];
    }

    protected static function checkDatabase($db)
    {
        return !in_array($db, self::getDBs()) ? false : $db;
    }

    /**
     *
     * @throws E
     */
    protected static function exc($err)
    {
        $e = ($err == 'db') ? "Invalid database. Choose one of: " .
            substr( implode(", ", self::getDBs()), 0, -2) : $err;
        throw new E($e);
        exit(0);
    }

    protected static function getDomainFromUrl($url)
    {
        $url      = parent::getUrl($url);
        $domain   = Helper\Url::parseHost($url);
        static::guardDomainIsValid($domain);

        return $domain;
    }

    protected static function getValidDatabase($db)
    {
        $db = self::getSemRushDatabase($db);
        $database = self::checkDatabase($db);
        static::guardDatabaseIsValid($database);

        return $database;
    }
}
