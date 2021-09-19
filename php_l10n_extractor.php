<?php
/**
 * @brief Extract string to translate from a php file
 * 
 * @author Jean-Christian Denis and contributors
 * 
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

// contents exemple
$content = '$count = sprintf(__("a singular", "%s plurals", 2), 2);

class myClass
{
    function myFunction($var)
    {
        $var = trim("no");
        echo __(\'single quote is "the" thing\');
        echo __("double quote is \'not\' bad");
    }
}

__("a normal string");
__("with parenthesis (%s) inside");
__(
    "or with multiple line"
);

echo "and not this one";    
';

echo sprintf('<pre>%s</pre>', print_r(php_l10n_extractor($content), true));

function php_l10n_extractor(string $content, string $func = '__'): array
{
    $duplicate = $final_strings = [];
    // split content by starting of translation function
    $parts = explode($func . "(", $content);
    // remove fisrt element from array
    array_shift($parts);
    // put removed parenthesis
    $parts = array_map(function($v){ return '(' . $v;}, $parts);
    // walk through parts
    foreach($parts as $part) {
        // find pairs of parenthesis
        preg_match_all("/\((?:[^\)\(]+|(?R))*+\)/s", $part, $subparts);
        // find quoted strings (single or double)
        preg_match_all("/\'(?:[^\'']+)\'|\"(?:[^\"]+)\"/s", $subparts[0][0], $strings);
        // strings exist
        if (!empty($strings[0])) {
            // remove quotes
            $strings[0] = array_map(function($v){ return substr($v, 1, -1);}, $strings[0]);
            // filter duplicate strings (only check first string for plurals form)
            if (!in_array($strings[0][0], $duplicate)) {
                // fill final array
                $final_strings[] = $strings[0];
                $duplicate[] = $strings[0][0];
            }
        }
    }
    return $final_strings;
}