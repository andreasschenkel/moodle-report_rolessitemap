<?php

namespace report_rolessitemap;

defined('MOODLE_INTERNAL') || die();

class Misc
{
    /**
     * @param stored_file $storedFile
     * @return float
     */
    public static function convertByteInMegabyte(int $byte)
    {
        if ($byte === 0) {
            return $byte;
        }
        $filesizeInKilobyte = $byte / 1024;
        $filesizeInMegabyte = $filesizeInKilobyte / 1024;
        return number_format($filesizeInMegabyte, 2, ',', '');
    }

    public static function translate($data, $translationfile, $prefix = "")
    {
        foreach ($data as $item) {
            $result[$item] = get_string($prefix . $item, $translationfile);
        }
        return $result;
    }
}
