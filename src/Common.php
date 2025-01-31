<?php

namespace SmartAxiata\Common;

class Common
{
    public static function appVersionIntVal(?string $version): int
    {
        if (is_null($version) || $version == '-') {
            return PHP_INT_MAX;
        }
        $pattern = '/^\d{1,2}\.\d{1,2}\.\d{1,2}$/';
        if ($version == '*' || !preg_match($pattern, $version)) {
            return 0;
        }

        $versionNumList = explode('.', $version);
        if (isset($versionNumList[2]) && intval($versionNumList[2]) < 10) {
            $versionNumList[2] = str_pad(intval($versionNumList[2]), 2, '0', STR_PAD_LEFT);
        }
        $vnumber = implode('', $versionNumList);
        return intval($vnumber);
    }
}
