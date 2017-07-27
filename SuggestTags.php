<?php

class SuggestTags
{
    public $query; // One or many words
    private $words; // Array of news body words
    public $j; // Equal to number of results or could indicate expected results when equal to 1

    private static $punctuation_marks = array('"', ',', ';', '،', '؛', '?', '؟', '.', ')', '(');

    public static $excluded_words = array("،", "آض", "آمينَ", "آه", "آهاً", "آي", "أ", "أب", "أجل", "أجمع", "أخ", "أخذ",
        "أصبح", "أضحى", "أقبل", "أقل", "أكثر", "ألا", "أم", "أما", "أمامك", "أمامكَ", "أمسى", "أمّا", "أن", "أنا", "أنت",
        "أنتم", "أنتما", "أنتن", "أنتِ", "أنشأ", "أنّى", "أو", "أوشك", "أولئك", "أولئكم", "أولاء", "أولالك", "أوّهْ", "أي",
        "أيا", "أين", "أينما", "أيّ", "أَنَّ", "أََيُّ", "أُفٍّ", "إذ", "إذا", "إذاً", "إذما", "إذن", "إلى", "إليكم", "إليكما",
        "إليكنّ", "إليكَ", "إلَيْكَ", "إلّا", "إمّا", "إن", "إنّ", "إنّما", "إي", "إياك", "إياكم", "إياكما", "إياكن", "إيانا",
        "إياه", "إياها", "إياهم", "إياهما", "إياهن", "إياي", "إيهٍ", "إِنَّ", "ا", "ابتدأ", "اثر", "اجل", "احد", "اخرى",
        "اخلولق", "اذا", "اربعة", "ارتدّ", "استحال", "اطار", "اعادة", "اعلنت", "اف", "اكثر", "اكد", "الألاء", "الألى", "الا",
        "الاخيرة", "الان", "الاول", "الاولى", "التى", "التي", "الثاني", "الثانية", "الذاتي", "الذى", "الذي", "الذين",
        "السابق", "الف", "اللائي", "اللاتي", "اللتان", "اللتيا", "اللتين", "اللذان", "اللذين", "اللواتي", "الماضي",
        "المقبل", "الوقت", "الى", "اليوم", "اما", "امام", "امس", "ان", "انبرى", "انقلب", "انه", "انها", "او", "اول",
        "اي", "ايار", "ايام", "ايضا", "ب", "بات", "باسم", "بان", "بخٍ", "برس", "بسبب", "بسّ", "بشكل", "بضع", "بطآن",
        "بعد", "بعض", "بك", "بكم", "بكما", "بكن", "بل", "بلى", "بما", "بماذا", "بمن", "بن", "بنا", "به", "بها", "بي",
        "بيد", "بين", "بَسْ", "بَلْهَ", "بِئْسَ", "تانِ", "تانِك", "تبدّل", "تجاه", "تحوّل", "تلقاء", "تلك", "تلكم", "تلكما", "تم",
        "تينك", "تَيْنِ", "تِه", "تِي", "ثلاثة", "ثم", "ثمّ", "ثمّة", "ثُمَّ", "جعل", "جلل", "جميع", "جير", "حار", "حاشا", "حاليا",
        "حاي", "حتى", "حرى", "حسب", "حم", "حوالى", "حول", "حيث", "حيثما", "حين", "حيَّ", "حَبَّذَا", "حَتَّى", "حَذارِ", "خلا",
        "خلال", "دون", "دونك", "ذا", "ذات", "ذاك", "ذانك", "ذانِ", "ذلك", "ذلكم", "ذلكما", "ذلكن", "ذو", "ذوا", "ذواتا",
        "ذواتي", "ذيت", "ذينك", "ذَيْنِ", "ذِه", "ذِي", "راح", "رجع", "رويدك", "ريث", "رُبَّ", "زيارة", "سبحان", "سرعان", "سنة",
        "سنوات", "سوف", "سوى", "سَاءَ", "سَاءَمَا", "شبه", "شخصا", "شرع", "شَتَّانَ", "صار", "صباح", "صفر", "صهٍ", "صهْ", "ضد",
        "ضمن", "طاق", "طالما", "طفق", "طَق", "ظلّ", "عاد", "عام", "عاما", "عامة", "عدا", "عدة", "عدد", "عدم", "عسى",
        "عشر", "عشرة", "علق", "على", "عليك", "عليه", "عليها", "علًّ", "عن", "عند", "عندما", "عوض", "عين", "عَدَسْ", "عَمَّا",
        "غدا", "غير", "ـ", "ف", "فان", "فلان", "فو", "فى", "في", "فيم", "فيما", "فيه", "فيها", "قال", "قام", "قبل", "قد",
        "قطّ", "قلما", "قوة", "كأنّما", "كأين", "كأيّ", "كأيّن", "كاد", "كان", "كانت", "كذا", "كذلك", "كرب", "كل", "كلا",
        "كلاهما", "كلتا", "كلم", "كليكما", "كليهما", "كلّما", "كلَّا", "كم", "كما", "كي", "كيت", "كيف", "كيفما", "كَأَنَّ", "كِخ",
        "لئن", "لا", "لات", "لاسيما", "لدن", "لدى", "لعمر", "لقاء", "لك", "لكم", "لكما", "لكن", "لكنَّما", "لكي", "لكيلا",
        "للامم", "لم", "لما", "لمّا", "لن", "لنا", "له", "لها", "لو", "لوكالة", "لولا", "لوما", "لي", "لَسْتَ", "لَسْتُ", "لَسْتُم",
        "لَسْتُمَا", "لَسْتُنَّ", "لَسْتِ", "لَسْنَ", "لَعَلَّ", "لَكِنَّ", "لَيْتَ", "لَيْسَ", "لَيْسَا", "لَيْسَتَا", "لَيْسَتْ", "لَيْسُوا", "لَِسْنَا", "ما",
        "ماانفك", "مابرح", "مادام", "ماذا", "مازال", "مافتئ", "مايو", "متى", "مثل", "مذ", "مساء", "مع", "معاذ", "مقابل",
        "مكانكم", "مكانكما", "مكانكنّ", "مكانَك", "مليار", "مليون", "مما", "ممن", "من", "منذ", "منها", "مه", "مهما", "مَنْ",
        "مِن", "نحن", "نحو", "نعم", "نفس", "نفسه", "نهاية", "نَخْ", "نِعِمّا", "نِعْمَ", "ها", "هاؤم", "هاكَ", "هاهنا", "هبّ",
        "هذا", "هذه", "هكذا", "هل", "هلمَّ", "هلّا", "هم", "هما", "هن", "هنا", "هناك", "هنالك", "هو", "هي", "هيا", "هيت",
        "هيّا", "هَؤلاء", "هَاتانِ", "هَاتَيْنِ", "هَاتِه", "هَاتِي", "هَجْ", "هَذا", "هَذانِ", "هَذَيْنِ", "هَذِه", "هَذِي", "هَيْهَاتَ", "و", "و6",
        "وا", "واحد", "واضاف", "واضافت", "واكد", "وان", "واهاً", "واوضح", "وراءَك", "وفي", "وقال", "وقالت", "وقد", "وقف",
        "وكان", "وكانت", "ولا", "ولم", "ومن", "وهو", "وهي", "ويكأنّ", "وَيْ", "وُشْكَانََ", "يكون", "يمكن", "يوم", "ّأيّان");

    public static $proper_nouns = array('لبنان', 'ليبيا', 'لندن', 'لاهاي', 'ليتوانيا', 'ليبريا', 'لاتفيا', 'لابورا',
        'لوزان', 'لافروف', 'ليزيو', 'لاريجاني');

    public static $prefixes = array('و', 'ك', 'ب', 'ف', 'ل');

    /**
     * @param $string
     * @return array
     */
    private static function refineString($string)
    {
        // Remove HTML tags
        $string = html_entity_decode(strip_tags($string));

        // Remove punctuation marks
        $string = str_replace(self::$punctuation_marks, '', $string);

        // Convert unicode whitespaces to ASCII
        $string = mb_ereg_replace('/[\pZ\pC]/u', ' ', $string);

        // Remove excess whitespaces
        $string = mb_ereg_replace('/\s+/', ' ', $string);

        // Split string in array
        $words = preg_split('/\s+/', $string);

        // Trim words
        $words = array_map('trim', $words);

        // Remove the empty elements, then reset array's indexes
        $words = array_values(array_filter($words));
        print_r($words);

        return $words;
    }

    /**
     * @param $string
     * @return int
     */
    public function setWords($string)
    {
        $this->words = self::refineString($string);
        return count($this->words);
    }

    /**
     * @param $array
     * @param bool $exactMatch
     * @return array|false|int
     */
    public function findQuery($array, $exactMatch = true)
    {
        if (is_array($array)) {
            if ($exactMatch)
                return array_search($this->query, $array);
            else {
                $search = preg_quote($this->query);
                //$search = preg_quote($this->query, '~');
                //$results = preg_grep('~' . $this->query . ' ~', $array);
                // Search by word
                $matches = preg_grep('/^' . $search . '(\s.*)?/', $array);
                return is_array($matches) ? $matches : array();
            }
        } else
            return false;
    }

    /**
     * @param $customQuery
     * @param $array
     * @param bool $exactMatch
     * @return array|false|int
     */
    public static function findCustom($customQuery, $array, $exactMatch = true)
    {
        if (is_array($array)) {
            if ($exactMatch)
                return array_search($customQuery, $array);
            else {
                $search = preg_quote($customQuery);
                // Search by word
                $matches = preg_grep('/^' . $search . '(\s.*)?/', $array);
                return is_array($matches) ? $matches : array();
            }
        } else
            return false;
    }

    /**
     * @param $i
     * @return false|string
     */
    public function getWordByIndex($i)
    {
        return (isset($this->words[$i]) ? $this->words[$i] : false);
    }

    /**
     * Strip last word from a string
     * @return string
     */
    public function removeLastWord()
    {
        $words = explode(' ', $this->query);
        array_splice($words, -1);
        return implode(" ", $words);
    }

    /**
     * @return int
     */
    public function getNumberOfAdditionalWords()
    {
        return count(explode(' ', $this->query)) - 1;
    }

    /**
     * @return int
     */
    public function hasPrefix()
    {
        return in_array(mb_substr($this->query, 0, 1), self::$prefixes);
    }
}