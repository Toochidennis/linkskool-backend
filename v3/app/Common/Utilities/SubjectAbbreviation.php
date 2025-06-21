<?php

namespace V3\App\Common\Utilities;

class SubjectAbbreviation
{
    public static function abbreviate($name)
    {
        // Predefined known abbreviations
        $map = [
            'english language' => 'ENG',
            'mathematics' => 'MATH',
            'further mathematics' => 'FMATH',
            'biology' => 'BIO',
            'chemistry' => 'CHEM',
            'physics' => 'PHY',
            'agricultural science' => 'AGR',
            'agric science' => 'AGR',
            'agric' => 'AGR',
            'economics' => 'ECO',
            'civic education' => 'CIV',
            'christian religious studies' => 'CRS',
            'christian religion' => 'CRS',
            'crs' => 'CRS',
            'government' => 'GOVT',
            'literature in english' => 'LIT',
            'literature' => 'LIT',
            'commerce' => 'COM',
            'basic science' => 'BSC',
            'basic technology' => 'BTECH',
            'business studies' => 'BST',
            'computer science' => 'CSC',
            'ict' => 'ICT',
            'physical education' => 'PE',
            'home economics' => 'HEC',
            'history' => 'HIST',
            'french' => 'FR',
            'yoruba' => 'YOR',
            'hausa' => 'HAU',
            'igbo' => 'IGB',
            'music' => 'MUS',
            'visual arts' => 'ART',
            'creative arts' => 'ART',
            'social studies' => 'SOS',
            'code' => 'CODE',
            'catering craft' => 'CCP',
        ];

        $key = strtolower(trim($name));
        if (isset($map[$key])) {
            return $map[$key];
        }

        // Fallback: take first 3 or 4 uppercase letters
        $cleaned = preg_replace('/[^A-Za-z]/', '', $name);
        return strtoupper(substr($cleaned, 0, 4));
    }
}
