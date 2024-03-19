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
            'nl_NL' => esc_html__('Dutch', 'mollie-forms'),
            'nl_BE' => esc_html__('Dutch (Belgium)', 'mollie-forms'),
            'en_US' => esc_html__('English', 'mollie-forms'),
            'de_DE' => esc_html__('German', 'mollie-forms'),
            'fr_FR' => esc_html__('French', 'mollie-forms'),
            'fr_BE' => esc_html__('French (Belgium)', 'mollie-forms'),
            'es_ES' => esc_html__('Spanish', 'mollie-forms'),
            'ca_ES' => esc_html__('Catalan', 'mollie-forms'),
            'pt_PT' => esc_html__('Portuguese', 'mollie-forms'),
            'it_IT' => esc_html__('Italian', 'mollie-forms'),
            'sv_SE' => esc_html__('Swedish', 'mollie-forms'),
            'fi_FI' => esc_html__('Finnish', 'mollie-forms'),
            'da_DK' => esc_html__('Danish', 'mollie-forms'),
            'is_IS' => esc_html__('Icelandic', 'mollie-forms'),
            'hu_HU' => esc_html__('Hungarian', 'mollie-forms'),
            'pl_PL' => esc_html__('Polish', 'mollie-forms'),
            'lv_LV' => esc_html__('Latvian', 'mollie-forms'),
            'lt_LT' => esc_html__('Lithuanian', 'mollie-forms'),
            'nb_NO' => esc_html__('Norwegian Bokmål', 'mollie-forms'),
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
            'AF' => esc_html__('Afghanistan', 'mollie-forms'),
            'AL' => esc_html__('Albania', 'mollie-forms'),
            'DZ' => esc_html__('Algeria', 'mollie-forms'),
            'DS' => esc_html__('American Samoa', 'mollie-forms'),
            'AD' => esc_html__('Andorra', 'mollie-forms'),
            'AO' => esc_html__('Angola', 'mollie-forms'),
            'AI' => esc_html__('Anguilla', 'mollie-forms'),
            'AQ' => esc_html__('Antarctica', 'mollie-forms'),
            'AG' => esc_html__('Antigua and Barbuda', 'mollie-forms'),
            'AR' => esc_html__('Argentina', 'mollie-forms'),
            'AM' => esc_html__('Armenia', 'mollie-forms'),
            'AW' => esc_html__('Aruba', 'mollie-forms'),
            'AU' => esc_html__('Australia', 'mollie-forms'),
            'AT' => esc_html__('Austria', 'mollie-forms'),
            'AZ' => esc_html__('Azerbaijan', 'mollie-forms'),
            'BS' => esc_html__('Bahamas', 'mollie-forms'),
            'BH' => esc_html__('Bahrain', 'mollie-forms'),
            'BD' => esc_html__('Bangladesh', 'mollie-forms'),
            'BB' => esc_html__('Barbados', 'mollie-forms'),
            'BY' => esc_html__('Belarus', 'mollie-forms'),
            'BE' => esc_html__('Belgium', 'mollie-forms'),
            'BZ' => esc_html__('Belize', 'mollie-forms'),
            'BJ' => esc_html__('Benin', 'mollie-forms'),
            'BM' => esc_html__('Bermuda', 'mollie-forms'),
            'BT' => esc_html__('Bhutan', 'mollie-forms'),
            'BO' => esc_html__('Bolivia', 'mollie-forms'),
            'BA' => esc_html__('Bosnia and Herzegovina', 'mollie-forms'),
            'BW' => esc_html__('Botswana', 'mollie-forms'),
            'BV' => esc_html__('Bouvet Island', 'mollie-forms'),
            'BR' => esc_html__('Brazil', 'mollie-forms'),
            'IO' => esc_html__('British Indian Ocean Territory', 'mollie-forms'),
            'BN' => esc_html__('Brunei Darussalam', 'mollie-forms'),
            'BG' => esc_html__('Bulgaria', 'mollie-forms'),
            'BF' => esc_html__('Burkina Faso', 'mollie-forms'),
            'BI' => esc_html__('Burundi', 'mollie-forms'),
            'KH' => esc_html__('Cambodia', 'mollie-forms'),
            'CM' => esc_html__('Cameroon', 'mollie-forms'),
            'CA' => esc_html__('Canada', 'mollie-forms'),
            'CV' => esc_html__('Cape Verde', 'mollie-forms'),
            'KY' => esc_html__('Cayman Islands', 'mollie-forms'),
            'CF' => esc_html__('Central African Republic', 'mollie-forms'),
            'TD' => esc_html__('Chad', 'mollie-forms'),
            'CL' => esc_html__('Chile', 'mollie-forms'),
            'CN' => esc_html__('China', 'mollie-forms'),
            'CX' => esc_html__('Christmas Island', 'mollie-forms'),
            'CC' => esc_html__('Cocos (Keeling) Islands', 'mollie-forms'),
            'CO' => esc_html__('Colombia', 'mollie-forms'),
            'KM' => esc_html__('Comoros', 'mollie-forms'),
            'CG' => esc_html__('Congo', 'mollie-forms'),
            'CK' => esc_html__('Cook Islands', 'mollie-forms'),
            'CR' => esc_html__('Costa Rica', 'mollie-forms'),
            'HR' => esc_html__('Croatia (Hrvatska)', 'mollie-forms'),
            'CU' => esc_html__('Cuba', 'mollie-forms'),
            'CY' => esc_html__('Cyprus', 'mollie-forms'),
            'CZ' => esc_html__('Czech Republic', 'mollie-forms'),
            'DK' => esc_html__('Denmark', 'mollie-forms'),
            'DJ' => esc_html__('Djibouti', 'mollie-forms'),
            'DM' => esc_html__('Dominica', 'mollie-forms'),
            'DO' => esc_html__('Dominican Republic', 'mollie-forms'),
            'TP' => esc_html__('East Timor', 'mollie-forms'),
            'EC' => esc_html__('Ecuador', 'mollie-forms'),
            'EG' => esc_html__('Egypt', 'mollie-forms'),
            'SV' => esc_html__('El Salvador', 'mollie-forms'),
            'GQ' => esc_html__('Equatorial Guinea', 'mollie-forms'),
            'ER' => esc_html__('Eritrea', 'mollie-forms'),
            'EE' => esc_html__('Estonia', 'mollie-forms'),
            'ET' => esc_html__('Ethiopia', 'mollie-forms'),
            'FK' => esc_html__('Falkland Islands (Malvinas)', 'mollie-forms'),
            'FO' => esc_html__('Faroe Islands', 'mollie-forms'),
            'FJ' => esc_html__('Fiji', 'mollie-forms'),
            'FI' => esc_html__('Finland', 'mollie-forms'),
            'FR' => esc_html__('France', 'mollie-forms'),
            'FX' => esc_html__('France, Metropolitan', 'mollie-forms'),
            'GF' => esc_html__('French Guiana', 'mollie-forms'),
            'PF' => esc_html__('French Polynesia', 'mollie-forms'),
            'TF' => esc_html__('French Southern Territories', 'mollie-forms'),
            'GA' => esc_html__('Gabon', 'mollie-forms'),
            'GM' => esc_html__('Gambia', 'mollie-forms'),
            'GE' => esc_html__('Georgia', 'mollie-forms'),
            'DE' => esc_html__('Germany', 'mollie-forms'),
            'GH' => esc_html__('Ghana', 'mollie-forms'),
            'GI' => esc_html__('Gibraltar', 'mollie-forms'),
            'GK' => esc_html__('Guernsey', 'mollie-forms'),
            'GR' => esc_html__('Greece', 'mollie-forms'),
            'GL' => esc_html__('Greenland', 'mollie-forms'),
            'GD' => esc_html__('Grenada', 'mollie-forms'),
            'GP' => esc_html__('Guadeloupe', 'mollie-forms'),
            'GU' => esc_html__('Guam', 'mollie-forms'),
            'GT' => esc_html__('Guatemala', 'mollie-forms'),
            'GN' => esc_html__('Guinea', 'mollie-forms'),
            'GW' => esc_html__('Guinea-Bissau', 'mollie-forms'),
            'GY' => esc_html__('Guyana', 'mollie-forms'),
            'HT' => esc_html__('Haiti', 'mollie-forms'),
            'HM' => esc_html__('Heard and Mc Donald Islands', 'mollie-forms'),
            'HN' => esc_html__('Honduras', 'mollie-forms'),
            'HK' => esc_html__('Hong Kong', 'mollie-forms'),
            'HU' => esc_html__('Hungary', 'mollie-forms'),
            'IS' => esc_html__('Iceland', 'mollie-forms'),
            'IN' => esc_html__('India', 'mollie-forms'),
            'IM' => esc_html__('Isle of Man', 'mollie-forms'),
            'ID' => esc_html__('Indonesia', 'mollie-forms'),
            'IR' => esc_html__('Iran (Islamic Republic of)', 'mollie-forms'),
            'IQ' => esc_html__('Iraq', 'mollie-forms'),
            'IE' => esc_html__('Ireland', 'mollie-forms'),
            'IL' => esc_html__('Israel', 'mollie-forms'),
            'IT' => esc_html__('Italy', 'mollie-forms'),
            'CI' => esc_html__('Ivory Coast', 'mollie-forms'),
            'JE' => esc_html__('Jersey', 'mollie-forms'),
            'JM' => esc_html__('Jamaica', 'mollie-forms'),
            'JP' => esc_html__('Japan', 'mollie-forms'),
            'JO' => esc_html__('Jordan', 'mollie-forms'),
            'KZ' => esc_html__('Kazakhstan', 'mollie-forms'),
            'KE' => esc_html__('Kenya', 'mollie-forms'),
            'KI' => esc_html__('Kiribati', 'mollie-forms'),
            'KP' => esc_html__('Korea, Democratic People\'s Republic of', 'mollie-forms'),
            'KR' => esc_html__('Korea, Republic of', 'mollie-forms'),
            'XK' => esc_html__('Kosovo', 'mollie-forms'),
            'KW' => esc_html__('Kuwait', 'mollie-forms'),
            'KG' => esc_html__('Kyrgyzstan', 'mollie-forms'),
            'LA' => esc_html__('Lao People\'s Democratic Republic', 'mollie-forms'),
            'LV' => esc_html__('Latvia', 'mollie-forms'),
            'LB' => esc_html__('Lebanon', 'mollie-forms'),
            'LS' => esc_html__('Lesotho', 'mollie-forms'),
            'LR' => esc_html__('Liberia', 'mollie-forms'),
            'LY' => esc_html__('Libyan Arab Jamahiriya', 'mollie-forms'),
            'LI' => esc_html__('Liechtenstein', 'mollie-forms'),
            'LT' => esc_html__('Lithuania', 'mollie-forms'),
            'LU' => esc_html__('Luxembourg', 'mollie-forms'),
            'MO' => esc_html__('Macau', 'mollie-forms'),
            'MK' => esc_html__('Macedonia', 'mollie-forms'),
            'MG' => esc_html__('Madagascar', 'mollie-forms'),
            'MW' => esc_html__('Malawi', 'mollie-forms'),
            'MY' => esc_html__('Malaysia', 'mollie-forms'),
            'MV' => esc_html__('Maldives', 'mollie-forms'),
            'ML' => esc_html__('Mali', 'mollie-forms'),
            'MT' => esc_html__('Malta', 'mollie-forms'),
            'MH' => esc_html__('Marshall Islands', 'mollie-forms'),
            'MQ' => esc_html__('Martinique', 'mollie-forms'),
            'MR' => esc_html__('Mauritania', 'mollie-forms'),
            'MU' => esc_html__('Mauritius', 'mollie-forms'),
            'TY' => esc_html__('Mayotte', 'mollie-forms'),
            'MX' => esc_html__('Mexico', 'mollie-forms'),
            'FM' => esc_html__('Micronesia, Federated States of', 'mollie-forms'),
            'MD' => esc_html__('Moldova, Republic of', 'mollie-forms'),
            'MC' => esc_html__('Monaco', 'mollie-forms'),
            'MN' => esc_html__('Mongolia', 'mollie-forms'),
            'ME' => esc_html__('Montenegro', 'mollie-forms'),
            'MS' => esc_html__('Montserrat', 'mollie-forms'),
            'MA' => esc_html__('Morocco', 'mollie-forms'),
            'MZ' => esc_html__('Mozambique', 'mollie-forms'),
            'MM' => esc_html__('Myanmar', 'mollie-forms'),
            'NA' => esc_html__('Namibia', 'mollie-forms'),
            'NR' => esc_html__('Nauru', 'mollie-forms'),
            'NP' => esc_html__('Nepal', 'mollie-forms'),
            'NL' => esc_html__('Netherlands', 'mollie-forms'),
            'AN' => esc_html__('Netherlands Antilles', 'mollie-forms'),
            'NC' => esc_html__('New Caledonia', 'mollie-forms'),
            'NZ' => esc_html__('New Zealand', 'mollie-forms'),
            'NI' => esc_html__('Nicaragua', 'mollie-forms'),
            'NE' => esc_html__('Niger', 'mollie-forms'),
            'NG' => esc_html__('Nigeria', 'mollie-forms'),
            'NU' => esc_html__('Niue', 'mollie-forms'),
            'NF' => esc_html__('Norfolk Island', 'mollie-forms'),
            'MP' => esc_html__('Northern Mariana Islands', 'mollie-forms'),
            'NO' => esc_html__('Norway', 'mollie-forms'),
            'OM' => esc_html__('Oman', 'mollie-forms'),
            'PK' => esc_html__('Pakistan', 'mollie-forms'),
            'PW' => esc_html__('Palau', 'mollie-forms'),
            'PS' => esc_html__('Palestine', 'mollie-forms'),
            'PA' => esc_html__('Panama', 'mollie-forms'),
            'PG' => esc_html__('Papua New Guinea', 'mollie-forms'),
            'PY' => esc_html__('Paraguay', 'mollie-forms'),
            'PE' => esc_html__('Peru', 'mollie-forms'),
            'PH' => esc_html__('Philippines', 'mollie-forms'),
            'PN' => esc_html__('Pitcairn', 'mollie-forms'),
            'PL' => esc_html__('Poland', 'mollie-forms'),
            'PT' => esc_html__('Portugal', 'mollie-forms'),
            'PR' => esc_html__('Puerto Rico', 'mollie-forms'),
            'QA' => esc_html__('Qatar', 'mollie-forms'),
            'RE' => esc_html__('Reunion', 'mollie-forms'),
            'RO' => esc_html__('Romania', 'mollie-forms'),
            'RU' => esc_html__('Russian Federation', 'mollie-forms'),
            'RW' => esc_html__('Rwanda', 'mollie-forms'),
            'KN' => esc_html__('Saint Kitts and Nevis', 'mollie-forms'),
            'LC' => esc_html__('Saint Lucia', 'mollie-forms'),
            'VC' => esc_html__('Saint Vincent and the Grenadines', 'mollie-forms'),
            'WS' => esc_html__('Samoa', 'mollie-forms'),
            'SM' => esc_html__('San Marino', 'mollie-forms'),
            'ST' => esc_html__('Sao Tome and Principe', 'mollie-forms'),
            'SA' => esc_html__('Saudi Arabia', 'mollie-forms'),
            'SN' => esc_html__('Senegal', 'mollie-forms'),
            'RS' => esc_html__('Serbia', 'mollie-forms'),
            'SC' => esc_html__('Seychelles', 'mollie-forms'),
            'SL' => esc_html__('Sierra Leone', 'mollie-forms'),
            'SG' => esc_html__('Singapore', 'mollie-forms'),
            'SK' => esc_html__('Slovakia', 'mollie-forms'),
            'SI' => esc_html__('Slovenia', 'mollie-forms'),
            'SB' => esc_html__('Solomon Islands', 'mollie-forms'),
            'SO' => esc_html__('Somalia', 'mollie-forms'),
            'ZA' => esc_html__('South Africa', 'mollie-forms'),
            'SS' => esc_html__('South Sudan', 'mollie-forms'),
            'GS' => esc_html__('South Georgia South Sandwich Islands', 'mollie-forms'),
            'ES' => esc_html__('Spain', 'mollie-forms'),
            'LK' => esc_html__('Sri Lanka', 'mollie-forms'),
            'SH' => esc_html__('St. Helena', 'mollie-forms'),
            'PM' => esc_html__('St. Pierre and Miquelon', 'mollie-forms'),
            'SD' => esc_html__('Sudan', 'mollie-forms'),
            'SR' => esc_html__('Suriname', 'mollie-forms'),
            'SJ' => esc_html__('Svalbard and Jan Mayen Islands', 'mollie-forms'),
            'SZ' => esc_html__('Swaziland', 'mollie-forms'),
            'SE' => esc_html__('Sweden', 'mollie-forms'),
            'CH' => esc_html__('Switzerland', 'mollie-forms'),
            'SY' => esc_html__('Syrian Arab Republic', 'mollie-forms'),
            'TW' => esc_html__('Taiwan', 'mollie-forms'),
            'TJ' => esc_html__('Tajikistan', 'mollie-forms'),
            'TZ' => esc_html__('Tanzania, United Republic of', 'mollie-forms'),
            'TH' => esc_html__('Thailand', 'mollie-forms'),
            'TG' => esc_html__('Togo', 'mollie-forms'),
            'TK' => esc_html__('Tokelau', 'mollie-forms'),
            'TO' => esc_html__('Tonga', 'mollie-forms'),
            'TT' => esc_html__('Trinidad and Tobago', 'mollie-forms'),
            'TN' => esc_html__('Tunisia', 'mollie-forms'),
            'TR' => esc_html__('Turkey', 'mollie-forms'),
            'TM' => esc_html__('Turkmenistan', 'mollie-forms'),
            'TC' => esc_html__('Turks and Caicos Islands', 'mollie-forms'),
            'TV' => esc_html__('Tuvalu', 'mollie-forms'),
            'UG' => esc_html__('Uganda', 'mollie-forms'),
            'UA' => esc_html__('Ukraine', 'mollie-forms'),
            'AE' => esc_html__('United Arab Emirates', 'mollie-forms'),
            'GB' => esc_html__('United Kingdom', 'mollie-forms'),
            'US' => esc_html__('United States', 'mollie-forms'),
            'UM' => esc_html__('United States minor outlying islands', 'mollie-forms'),
            'UY' => esc_html__('Uruguay', 'mollie-forms'),
            'UZ' => esc_html__('Uzbekistan', 'mollie-forms'),
            'VU' => esc_html__('Vanuatu', 'mollie-forms'),
            'VA' => esc_html__('Vatican City State', 'mollie-forms'),
            'VE' => esc_html__('Venezuela', 'mollie-forms'),
            'VN' => esc_html__('Vietnam', 'mollie-forms'),
            'VG' => esc_html__('Virgin Islands (British)', 'mollie-forms'),
            'VI' => esc_html__('Virgin Islands (U.S.)', 'mollie-forms'),
            'WF' => esc_html__('Wallis and Futuna Islands', 'mollie-forms'),
            'EH' => esc_html__('Western Sahara', 'mollie-forms'),
            'YE' => esc_html__('Yemen', 'mollie-forms'),
            'ZR' => esc_html__('Zaire', 'mollie-forms'),
            'ZM' => esc_html__('Zambia', 'mollie-forms'),
            'ZW' => esc_html__('Zimbabwe', 'mollie-forms'),
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
            esc_html__('days', 'mollie-forms'),
            esc_html__('weeks', 'mollie-forms'),
            esc_html__('months', 'mollie-forms'),
        ];

        $frequency = trim($frequency);
        switch ($frequency) {
            case null:
                $return = '';
                break;
            case 'once':
                $return = $withOneOff ? esc_html__('one-off', 'mollie-forms') : '';
                break;
            case '1 months':
            case '1 month':
                $return = esc_html__('per month', 'mollie-forms');
                break;
            case '3 months':
                $return = esc_html__('each quarter', 'mollie-forms');
                break;
            case '12 months':
                $return = esc_html__('per year', 'mollie-forms');
                break;
            case '1 weeks':
            case '1 week':
                $return = esc_html__('per week', 'mollie-forms');
                break;
            case '1 days':
            case '1 day':
                $return = esc_html__('per day', 'mollie-forms');
                break;
            default:
                $return = esc_html__('each', 'mollie-forms') . ' ' . str_replace($words, $translations, $frequency);
        }

        return $return;
    }

}
