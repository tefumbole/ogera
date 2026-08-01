<?php

namespace App\Support;

/**
 * Tells a person's name apart from a phone number that was stored in its place.
 *
 * Account creation used to seed `name` from the phone, and that number then
 * mirrored itself across the POS user, the customer record and the portal user,
 * so it showed up as the customer on booking lists, as the assignee in Task
 * Manager, and as the greeting in WhatsApp messages.
 */
class PersonName
{
    /** A name needs at least one letter; without one, seven digits is a number. */
    const MIN_PHONE_DIGITS = 7;

    /**
     * True for values such as "+237675321739", "237 675 321 739" or "(0675) 321-739".
     */
    public static function looksLikePhone($value)
    {
        $raw = trim((string) $value);
        if ($raw === '' || preg_match('/\pL/u', $raw)) {
            return false;
        }

        return strlen(self::digits($raw)) >= self::MIN_PHONE_DIGITS;
    }

    /**
     * The first candidate that reads as a real name, or '' when every candidate
     * is blank or is really a phone number. Accepts nested arrays.
     */
    public static function pick(...$candidates)
    {
        foreach (self::flatten($candidates) as $candidate) {
            $name = trim((string) $candidate);
            if ($name !== '' && ! self::looksLikePhone($name)) {
                return $name;
            }
        }

        return '';
    }

    private static function digits($value)
    {
        return preg_replace('/\D/', '', (string) $value);
    }

    private static function flatten(array $candidates)
    {
        $flat = [];
        foreach ($candidates as $candidate) {
            if (is_array($candidate)) {
                $flat = array_merge($flat, self::flatten($candidate));
            } else {
                $flat[] = $candidate;
            }
        }

        return $flat;
    }
}
