<?php

namespace MollieForms;

class Helpers
{

    /**
     * Get all currencies with number of decimals
     *
     * @param null $currency
     *
     * @return array|mixed
     */
    public function getCurrencies($currency = null)
    {
        $currencies = [
            'EUR' => 2,
            'USD' => 2,
            'GBP' => 2,
            'AUD' => 2,
            'BGN' => 2,
            'CAD' => 2,
            'CHF' => 2,
            'CZK' => 2,
            'DKK' => 2,
            'HKD' => 2,
            'HRK' => 2,
            'HUF' => 2,
            'ILS' => 2,
            'ISK' => 2,
            'JPY' => 0,
            'PLN' => 2,
            'RON' => 2,
            'SEK' => 2,
        ];

        if ($currency && array_key_exists($currency, $currencies)) {
            return $currencies[$currency];
        }

        return $currencies;
    }

    /**
     * Get currency symbol
     *
     * @param string $currency
     *
     * @return string
     */
    public function getCurrencySymbol($currency = 'EUR')
    {
        switch ($currency) {
            case 'EUR':
                $symbol = '&euro;';
                break;
            case 'USD':
                $symbol = 'US$';
                break;
            case 'GBP':
                $symbol = '&pound;';
                break;
            case 'JPY':
                $symbol = '&yen;';
                break;
            default:
                $symbol = strtoupper($currency);
        }

        return $symbol;
    }

    /**
     * Get all Mollie Checkout locales
     *
     * @return array
     */
    public function getLocales()
    {
        return [
            'nl_NL' => __('Dutch', 'mollie-forms'),
            'nl_BE' => __('Dutch (Belgium)', 'mollie-forms'),
            'en_US' => __('English', 'mollie-forms'),
            'de_DE' => __('German', 'mollie-forms'),
            'fr_FR' => __('French', 'mollie-forms'),
            'fr_BE' => __('French (Belgium)', 'mollie-forms'),
            'es_ES' => __('Spanish', 'mollie-forms'),
            'ca_ES' => __('Catalan', 'mollie-forms'),
            'pt_PT' => __('Portuguese', 'mollie-forms'),
            'it_IT' => __('Italian', 'mollie-forms'),
            'sv_SE' => __('Swedish', 'mollie-forms'),
            'fi_FI' => __('Finnish', 'mollie-forms'),
            'da_DK' => __('Danish', 'mollie-forms'),
            'is_IS' => __('Icelandic', 'mollie-forms'),
            'hu_HU' => __('Hungarian', 'mollie-forms'),
            'pl_PL' => __('Polish', 'mollie-forms'),
            'lv_LV' => __('Latvian', 'mollie-forms'),
            'lt_LT' => __('Lithuanian', 'mollie-forms'),
            'nb_NO' => __('Norwegian Bokmål', 'mollie-forms'),
        ];
    }

    /**
     * Get all countries
     *
     * @return array
     */
    public function getCountries()
    {
        return [
            'AF' => __('Afghanistan', 'mollie-forms'),
            'AL' => __('Albania', 'mollie-forms'),
            'DZ' => __('Algeria', 'mollie-forms'),
            'DS' => __('American Samoa', 'mollie-forms'),
            'AD' => __('Andorra', 'mollie-forms'),
            'AO' => __('Angola', 'mollie-forms'),
            'AI' => __('Anguilla', 'mollie-forms'),
            'AQ' => __('Antarctica', 'mollie-forms'),
            'AG' => __('Antigua and Barbuda', 'mollie-forms'),
            'AR' => __('Argentina', 'mollie-forms'),
            'AM' => __('Armenia', 'mollie-forms'),
            'AW' => __('Aruba', 'mollie-forms'),
            'AU' => __('Australia', 'mollie-forms'),
            'AT' => __('Austria', 'mollie-forms'),
            'AZ' => __('Azerbaijan', 'mollie-forms'),
            'BS' => __('Bahamas', 'mollie-forms'),
            'BH' => __('Bahrain', 'mollie-forms'),
            'BD' => __('Bangladesh', 'mollie-forms'),
            'BB' => __('Barbados', 'mollie-forms'),
            'BY' => __('Belarus', 'mollie-forms'),
            'BE' => __('Belgium', 'mollie-forms'),
            'BZ' => __('Belize', 'mollie-forms'),
            'BJ' => __('Benin', 'mollie-forms'),
            'BM' => __('Bermuda', 'mollie-forms'),
            'BT' => __('Bhutan', 'mollie-forms'),
            'BO' => __('Bolivia', 'mollie-forms'),
            'BA' => __('Bosnia and Herzegovina', 'mollie-forms'),
            'BW' => __('Botswana', 'mollie-forms'),
            'BV' => __('Bouvet Island', 'mollie-forms'),
            'BR' => __('Brazil', 'mollie-forms'),
            'IO' => __('British Indian Ocean Territory', 'mollie-forms'),
            'BN' => __('Brunei Darussalam', 'mollie-forms'),
            'BG' => __('Bulgaria', 'mollie-forms'),
            'BF' => __('Burkina Faso', 'mollie-forms'),
            'BI' => __('Burundi', 'mollie-forms'),
            'KH' => __('Cambodia', 'mollie-forms'),
            'CM' => __('Cameroon', 'mollie-forms'),
            'CA' => __('Canada', 'mollie-forms'),
            'CV' => __('Cape Verde', 'mollie-forms'),
            'KY' => __('Cayman Islands', 'mollie-forms'),
            'CF' => __('Central African Republic', 'mollie-forms'),
            'TD' => __('Chad', 'mollie-forms'),
            'CL' => __('Chile', 'mollie-forms'),
            'CN' => __('China', 'mollie-forms'),
            'CX' => __('Christmas Island', 'mollie-forms'),
            'CC' => __('Cocos (Keeling) Islands', 'mollie-forms'),
            'CO' => __('Colombia', 'mollie-forms'),
            'KM' => __('Comoros', 'mollie-forms'),
            'CG' => __('Congo', 'mollie-forms'),
            'CK' => __('Cook Islands', 'mollie-forms'),
            'CR' => __('Costa Rica', 'mollie-forms'),
            'HR' => __('Croatia (Hrvatska)', 'mollie-forms'),
            'CU' => __('Cuba', 'mollie-forms'),
            'CY' => __('Cyprus', 'mollie-forms'),
            'CZ' => __('Czech Republic', 'mollie-forms'),
            'DK' => __('Denmark', 'mollie-forms'),
            'DJ' => __('Djibouti', 'mollie-forms'),
            'DM' => __('Dominica', 'mollie-forms'),
            'DO' => __('Dominican Republic', 'mollie-forms'),
            'TP' => __('East Timor', 'mollie-forms'),
            'EC' => __('Ecuador', 'mollie-forms'),
            'EG' => __('Egypt', 'mollie-forms'),
            'SV' => __('El Salvador', 'mollie-forms'),
            'GQ' => __('Equatorial Guinea', 'mollie-forms'),
            'ER' => __('Eritrea', 'mollie-forms'),
            'EE' => __('Estonia', 'mollie-forms'),
            'ET' => __('Ethiopia', 'mollie-forms'),
            'FK' => __('Falkland Islands (Malvinas)', 'mollie-forms'),
            'FO' => __('Faroe Islands', 'mollie-forms'),
            'FJ' => __('Fiji', 'mollie-forms'),
            'FI' => __('Finland', 'mollie-forms'),
            'FR' => __('France', 'mollie-forms'),
            'FX' => __('France, Metropolitan', 'mollie-forms'),
            'GF' => __('French Guiana', 'mollie-forms'),
            'PF' => __('French Polynesia', 'mollie-forms'),
            'TF' => __('French Southern Territories', 'mollie-forms'),
            'GA' => __('Gabon', 'mollie-forms'),
            'GM' => __('Gambia', 'mollie-forms'),
            'GE' => __('Georgia', 'mollie-forms'),
            'DE' => __('Germany', 'mollie-forms'),
            'GH' => __('Ghana', 'mollie-forms'),
            'GI' => __('Gibraltar', 'mollie-forms'),
            'GK' => __('Guernsey', 'mollie-forms'),
            'GR' => __('Greece', 'mollie-forms'),
            'GL' => __('Greenland', 'mollie-forms'),
            'GD' => __('Grenada', 'mollie-forms'),
            'GP' => __('Guadeloupe', 'mollie-forms'),
            'GU' => __('Guam', 'mollie-forms'),
            'GT' => __('Guatemala', 'mollie-forms'),
            'GN' => __('Guinea', 'mollie-forms'),
            'GW' => __('Guinea-Bissau', 'mollie-forms'),
            'GY' => __('Guyana', 'mollie-forms'),
            'HT' => __('Haiti', 'mollie-forms'),
            'HM' => __('Heard and Mc Donald Islands', 'mollie-forms'),
            'HN' => __('Honduras', 'mollie-forms'),
            'HK' => __('Hong Kong', 'mollie-forms'),
            'HU' => __('Hungary', 'mollie-forms'),
            'IS' => __('Iceland', 'mollie-forms'),
            'IN' => __('India', 'mollie-forms'),
            'IM' => __('Isle of Man', 'mollie-forms'),
            'ID' => __('Indonesia', 'mollie-forms'),
            'IR' => __('Iran (Islamic Republic of)', 'mollie-forms'),
            'IQ' => __('Iraq', 'mollie-forms'),
            'IE' => __('Ireland', 'mollie-forms'),
            'IL' => __('Israel', 'mollie-forms'),
            'IT' => __('Italy', 'mollie-forms'),
            'CI' => __('Ivory Coast', 'mollie-forms'),
            'JE' => __('Jersey', 'mollie-forms'),
            'JM' => __('Jamaica', 'mollie-forms'),
            'JP' => __('Japan', 'mollie-forms'),
            'JO' => __('Jordan', 'mollie-forms'),
            'KZ' => __('Kazakhstan', 'mollie-forms'),
            'KE' => __('Kenya', 'mollie-forms'),
            'KI' => __('Kiribati', 'mollie-forms'),
            'KP' => __('Korea, Democratic People\'s Republic of', 'mollie-forms'),
            'KR' => __('Korea, Republic of', 'mollie-forms'),
            'XK' => __('Kosovo', 'mollie-forms'),
            'KW' => __('Kuwait', 'mollie-forms'),
            'KG' => __('Kyrgyzstan', 'mollie-forms'),
            'LA' => __('Lao People\'s Democratic Republic', 'mollie-forms'),
            'LV' => __('Latvia', 'mollie-forms'),
            'LB' => __('Lebanon', 'mollie-forms'),
            'LS' => __('Lesotho', 'mollie-forms'),
            'LR' => __('Liberia', 'mollie-forms'),
            'LY' => __('Libyan Arab Jamahiriya', 'mollie-forms'),
            'LI' => __('Liechtenstein', 'mollie-forms'),
            'LT' => __('Lithuania', 'mollie-forms'),
            'LU' => __('Luxembourg', 'mollie-forms'),
            'MO' => __('Macau', 'mollie-forms'),
            'MK' => __('Macedonia', 'mollie-forms'),
            'MG' => __('Madagascar', 'mollie-forms'),
            'MW' => __('Malawi', 'mollie-forms'),
            'MY' => __('Malaysia', 'mollie-forms'),
            'MV' => __('Maldives', 'mollie-forms'),
            'ML' => __('Mali', 'mollie-forms'),
            'MT' => __('Malta', 'mollie-forms'),
            'MH' => __('Marshall Islands', 'mollie-forms'),
            'MQ' => __('Martinique', 'mollie-forms'),
            'MR' => __('Mauritania', 'mollie-forms'),
            'MU' => __('Mauritius', 'mollie-forms'),
            'TY' => __('Mayotte', 'mollie-forms'),
            'MX' => __('Mexico', 'mollie-forms'),
            'FM' => __('Micronesia, Federated States of', 'mollie-forms'),
            'MD' => __('Moldova, Republic of', 'mollie-forms'),
            'MC' => __('Monaco', 'mollie-forms'),
            'MN' => __('Mongolia', 'mollie-forms'),
            'ME' => __('Montenegro', 'mollie-forms'),
            'MS' => __('Montserrat', 'mollie-forms'),
            'MA' => __('Morocco', 'mollie-forms'),
            'MZ' => __('Mozambique', 'mollie-forms'),
            'MM' => __('Myanmar', 'mollie-forms'),
            'NA' => __('Namibia', 'mollie-forms'),
            'NR' => __('Nauru', 'mollie-forms'),
            'NP' => __('Nepal', 'mollie-forms'),
            'NL' => __('Netherlands', 'mollie-forms'),
            'AN' => __('Netherlands Antilles', 'mollie-forms'),
            'NC' => __('New Caledonia', 'mollie-forms'),
            'NZ' => __('New Zealand', 'mollie-forms'),
            'NI' => __('Nicaragua', 'mollie-forms'),
            'NE' => __('Niger', 'mollie-forms'),
            'NG' => __('Nigeria', 'mollie-forms'),
            'NU' => __('Niue', 'mollie-forms'),
            'NF' => __('Norfolk Island', 'mollie-forms'),
            'MP' => __('Northern Mariana Islands', 'mollie-forms'),
            'NO' => __('Norway', 'mollie-forms'),
            'OM' => __('Oman', 'mollie-forms'),
            'PK' => __('Pakistan', 'mollie-forms'),
            'PW' => __('Palau', 'mollie-forms'),
            'PS' => __('Palestine', 'mollie-forms'),
            'PA' => __('Panama', 'mollie-forms'),
            'PG' => __('Papua New Guinea', 'mollie-forms'),
            'PY' => __('Paraguay', 'mollie-forms'),
            'PE' => __('Peru', 'mollie-forms'),
            'PH' => __('Philippines', 'mollie-forms'),
            'PN' => __('Pitcairn', 'mollie-forms'),
            'PL' => __('Poland', 'mollie-forms'),
            'PT' => __('Portugal', 'mollie-forms'),
            'PR' => __('Puerto Rico', 'mollie-forms'),
            'QA' => __('Qatar', 'mollie-forms'),
            'RE' => __('Reunion', 'mollie-forms'),
            'RO' => __('Romania', 'mollie-forms'),
            'RU' => __('Russian Federation', 'mollie-forms'),
            'RW' => __('Rwanda', 'mollie-forms'),
            'KN' => __('Saint Kitts and Nevis', 'mollie-forms'),
            'LC' => __('Saint Lucia', 'mollie-forms'),
            'VC' => __('Saint Vincent and the Grenadines', 'mollie-forms'),
            'WS' => __('Samoa', 'mollie-forms'),
            'SM' => __('San Marino', 'mollie-forms'),
            'ST' => __('Sao Tome and Principe', 'mollie-forms'),
            'SA' => __('Saudi Arabia', 'mollie-forms'),
            'SN' => __('Senegal', 'mollie-forms'),
            'RS' => __('Serbia', 'mollie-forms'),
            'SC' => __('Seychelles', 'mollie-forms'),
            'SL' => __('Sierra Leone', 'mollie-forms'),
            'SG' => __('Singapore', 'mollie-forms'),
            'SK' => __('Slovakia', 'mollie-forms'),
            'SI' => __('Slovenia', 'mollie-forms'),
            'SB' => __('Solomon Islands', 'mollie-forms'),
            'SO' => __('Somalia', 'mollie-forms'),
            'ZA' => __('South Africa', 'mollie-forms'),
            'SS' => __('South Sudan', 'mollie-forms'),
            'GS' => __('South Georgia South Sandwich Islands', 'mollie-forms'),
            'ES' => __('Spain', 'mollie-forms'),
            'LK' => __('Sri Lanka', 'mollie-forms'),
            'SH' => __('St. Helena', 'mollie-forms'),
            'PM' => __('St. Pierre and Miquelon', 'mollie-forms'),
            'SD' => __('Sudan', 'mollie-forms'),
            'SR' => __('Suriname', 'mollie-forms'),
            'SJ' => __('Svalbard and Jan Mayen Islands', 'mollie-forms'),
            'SZ' => __('Swaziland', 'mollie-forms'),
            'SE' => __('Sweden', 'mollie-forms'),
            'CH' => __('Switzerland', 'mollie-forms'),
            'SY' => __('Syrian Arab Republic', 'mollie-forms'),
            'TW' => __('Taiwan', 'mollie-forms'),
            'TJ' => __('Tajikistan', 'mollie-forms'),
            'TZ' => __('Tanzania, United Republic of', 'mollie-forms'),
            'TH' => __('Thailand', 'mollie-forms'),
            'TG' => __('Togo', 'mollie-forms'),
            'TK' => __('Tokelau', 'mollie-forms'),
            'TO' => __('Tonga', 'mollie-forms'),
            'TT' => __('Trinidad and Tobago', 'mollie-forms'),
            'TN' => __('Tunisia', 'mollie-forms'),
            'TR' => __('Turkey', 'mollie-forms'),
            'TM' => __('Turkmenistan', 'mollie-forms'),
            'TC' => __('Turks and Caicos Islands', 'mollie-forms'),
            'TV' => __('Tuvalu', 'mollie-forms'),
            'UG' => __('Uganda', 'mollie-forms'),
            'UA' => __('Ukraine', 'mollie-forms'),
            'AE' => __('United Arab Emirates', 'mollie-forms'),
            'GB' => __('United Kingdom', 'mollie-forms'),
            'US' => __('United States', 'mollie-forms'),
            'UM' => __('United States minor outlying islands', 'mollie-forms'),
            'UY' => __('Uruguay', 'mollie-forms'),
            'UZ' => __('Uzbekistan', 'mollie-forms'),
            'VU' => __('Vanuatu', 'mollie-forms'),
            'VA' => __('Vatican City State', 'mollie-forms'),
            'VE' => __('Venezuela', 'mollie-forms'),
            'VN' => __('Vietnam', 'mollie-forms'),
            'VG' => __('Virgin Islands (British)', 'mollie-forms'),
            'VI' => __('Virgin Islands (U.S.)', 'mollie-forms'),
            'WF' => __('Wallis and Futuna Islands', 'mollie-forms'),
            'EH' => __('Western Sahara', 'mollie-forms'),
            'YE' => __('Yemen', 'mollie-forms'),
            'ZR' => __('Zaire', 'mollie-forms'),
            'ZM' => __('Zambia', 'mollie-forms'),
            'ZW' => __('Zimbabwe', 'mollie-forms'),
        ];
    }

    /**
     * Get frequency label
     *
     * @param $frequency
     * @param $withOneOff
     *
     * @return string
     */
    public function getFrequencyLabel($frequency, $withOneOff = false)
    {
        $words        = [
            'days',
            'weeks',
            'months',
        ];
        $translations = [
            __('days', 'mollie-forms'),
            __('weeks', 'mollie-forms'),
            __('months', 'mollie-forms'),
        ];

        $frequency = trim($frequency);
        switch ($frequency) {
            case null:
                $return = '';
                break;
            case 'once':
                $return = $withOneOff ? __('one-off', 'mollie-forms') : '';
                break;
            case '1 months':
            case '1 month':
                $return = __('per month', 'mollie-forms');
                break;
            case '3 months':
                $return = __('each quarter', 'mollie-forms');
                break;
            case '12 months':
                $return = __('per year', 'mollie-forms');
                break;
            case '1 weeks':
            case '1 week':
                $return = __('per week', 'mollie-forms');
                break;
            case '1 days':
            case '1 day':
                $return = __('per day', 'mollie-forms');
                break;
            default:
                $return = __('each', 'mollie-forms') . ' ' . str_replace($words, $translations, $frequency);
        }

        return $return;
    }

}