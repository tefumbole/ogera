<?php

namespace App\Support;

/**
 * Country flags as images rather than emoji.
 *
 * Flag emoji are regional-indicator letter pairs. Windows ships no flag glyphs
 * for them at all, so a browser there draws the two letters instead ("RW" for
 * Rwanda). The only rendering that is the same everywhere is a real image, so
 * every flag is served from public/images/flags/<iso2>.svg.
 */
class CountryFlag
{
    /** Where the SVGs live, relative to the app's public directory. */
    const DIRECTORY = 'images/flags';

    /** Countries offered in the pickers: display name => ISO 3166-1 alpha-2. */
    public static function countries()
    {
        return [
            'Cameroon' => 'cm',
            'Rwanda' => 'rw',
            'Uganda' => 'ug',
            'Kenya' => 'ke',
            'Tanzania' => 'tz',
            'Nigeria' => 'ng',
            'Ghana' => 'gh',
            'South Africa' => 'za',
            'Ethiopia' => 'et',
            'Egypt' => 'eg',
            'Morocco' => 'ma',
            'Algeria' => 'dz',
            'Tunisia' => 'tn',
            'Senegal' => 'sn',
            "Côte d'Ivoire" => 'ci',
            'Democratic Republic of the Congo' => 'cd',
            'Republic of the Congo' => 'cg',
            'Gabon' => 'ga',
            'Chad' => 'td',
            'Central African Republic' => 'cf',
            'Equatorial Guinea' => 'gq',
            'Angola' => 'ao',
            'Zambia' => 'zm',
            'Zimbabwe' => 'zw',
            'Mozambique' => 'mz',
            'Botswana' => 'bw',
            'Namibia' => 'na',
            'Burundi' => 'bi',
            'Malawi' => 'mw',
            'Mali' => 'ml',
            'Burkina Faso' => 'bf',
            'Niger' => 'ne',
            'Guinea' => 'gn',
            'Benin' => 'bj',
            'Togo' => 'tg',
            'Sierra Leone' => 'sl',
            'Liberia' => 'lr',
            'Sudan' => 'sd',
            'South Sudan' => 'ss',
            'Somalia' => 'so',
            'Libya' => 'ly',
            'United States' => 'us',
            'Canada' => 'ca',
            'United Kingdom' => 'gb',
            'Ireland' => 'ie',
            'France' => 'fr',
            'Germany' => 'de',
            'Spain' => 'es',
            'Portugal' => 'pt',
            'Italy' => 'it',
            'Malta' => 'mt',
            'Netherlands' => 'nl',
            'Belgium' => 'be',
            'Switzerland' => 'ch',
            'Sweden' => 'se',
            'Norway' => 'no',
            'Denmark' => 'dk',
            'Finland' => 'fi',
            'Poland' => 'pl',
            'Russia' => 'ru',
            'Turkey' => 'tr',
            'United Arab Emirates' => 'ae',
            'Saudi Arabia' => 'sa',
            'Qatar' => 'qa',
            'India' => 'in',
            'Pakistan' => 'pk',
            'China' => 'cn',
            'Japan' => 'jp',
            'South Korea' => 'kr',
            'Indonesia' => 'id',
            'Malaysia' => 'my',
            'Singapore' => 'sg',
            'Philippines' => 'ph',
            'Australia' => 'au',
            'New Zealand' => 'nz',
            'Brazil' => 'br',
            'Argentina' => 'ar',
            'Mexico' => 'mx',
        ];
    }

    /**
     * Spellings a leader may have been saved under before the picker existed,
     * or that an admin may type by hand.
     */
    protected static function aliases()
    {
        return [
            'usa' => 'us',
            'u.s.a.' => 'us',
            'u.s.' => 'us',
            'america' => 'us',
            'united states of america' => 'us',
            'uk' => 'gb',
            'u.k.' => 'gb',
            'great britain' => 'gb',
            'britain' => 'gb',
            'england' => 'gb',
            'scotland' => 'gb',
            'wales' => 'gb',
            'northern ireland' => 'gb',
            'drc' => 'cd',
            'dr congo' => 'cd',
            'dr. congo' => 'cd',
            'congo-kinshasa' => 'cd',
            'congo (drc)' => 'cd',
            'congo' => 'cg',
            'congo-brazzaville' => 'cg',
            'ivory coast' => 'ci',
            'cote d\'ivoire' => 'ci',
            "cote d'ivoire" => 'ci',
            'cote divoire' => 'ci',
            'uae' => 'ae',
            'emirates' => 'ae',
            'korea' => 'kr',
            'republic of korea' => 'kr',
            'holland' => 'nl',
            'the netherlands' => 'nl',
            'russian federation' => 'ru',
            'türkiye' => 'tr',
            'turkiye' => 'tr',
            'tanzania, united republic of' => 'tz',
            'south-africa' => 'za',
            'burkina' => 'bf',
            'swaziland' => 'sz',
            'eswatini' => 'sz',
        ];
    }

    /** ISO 3166-1 alpha-2 for a stored country name, or '' when unknown. */
    public static function code($country)
    {
        $name = trim((string) $country);
        if ($name === '') {
            return '';
        }

        $map = self::countries();
        if (isset($map[$name])) {
            return $map[$name];
        }

        $needle = mb_strtolower($name);
        foreach ($map as $label => $code) {
            if (mb_strtolower($label) === $needle) {
                return $code;
            }
        }

        $aliases = self::aliases();
        if (isset($aliases[$needle])) {
            return $aliases[$needle];
        }

        // Someone may have stored the code itself.
        if (preg_match('/^[A-Za-z]{2}$/', $name) && self::exists(mb_strtolower($name))) {
            return mb_strtolower($name);
        }

        return '';
    }

    /** True when the SVG for this ISO code is on disk. */
    public static function exists($code)
    {
        $code = preg_replace('/[^a-z]/', '', mb_strtolower((string) $code));

        return $code !== '' && is_file(public_path(self::DIRECTORY.'/'.$code.'.svg'));
    }

    /** Public URL of the flag image for a country name, or '' when unknown. */
    public static function url($country)
    {
        $code = self::code($country);
        if ($code === '' || ! self::exists($code)) {
            return '';
        }

        return asset('public/'.self::DIRECTORY.'/'.$code.'.svg');
    }

    /** name => public flag URL, for the country pickers. */
    public static function urlMap()
    {
        $urls = [];
        foreach (self::countries() as $name => $code) {
            $urls[$name] = self::exists($code) ? asset('public/'.self::DIRECTORY.'/'.$code.'.svg') : '';
        }

        return $urls;
    }

}
