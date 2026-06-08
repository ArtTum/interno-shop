<?php

namespace App\Services\General;

use App\Constants\PageConstants;
use App\Constants\PermalinkConstants;
use App\Models\PermalinkTranslation;
use App\Repositories\PermalinkTranslation\PermalinkTranslationRepository;
use App\Services\ERP\Catalog\Category\CategoryService;
use Illuminate\Database\Eloquent\Model;

class CustomSlugService
{
    const REPLACEMENTS = [
        // German
        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue',

        // French
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'à' => 'a', 'â' => 'a', 'ç' => 'c', 'ô' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ÿ' => 'y',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'À' => 'A', 'Â' => 'A', 'Ç' => 'C', 'Ô' => 'O', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ÿ' => 'Y',

        // Hungarian
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ö' => 'o', 'ő' => 'o', 'ú' => 'u', 'ü' => 'u', 'ű' => 'u',
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ö' => 'O', 'Ő' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ű' => 'U',

        // Spanish, Portuguese
        'ñ' => 'n', 'Ñ' => 'N', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'Á' => 'A', 'É' => 'E',
        'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',

        // Polish
        'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',
        'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z',

        // Czech, Slovak
        'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ž' => 'z',
        'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ž' => 'Z',

        // Romanian
        'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ț' => 't',
        'Ă' => 'A', 'Â' => 'A', 'Î' => 'I', 'Ș' => 'S', 'Ț' => 'T',

        // Russian (Cyrillic)
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '',
        'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo',
        'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
        'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => '',
        'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',

        // Arabic
        'ا' => 'a', 'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j', 'ح' => 'h', 'خ' => 'kh',
        'د' => 'd', 'ذ' => 'th', 'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sh', 'ص' => 's',
        'ض' => 'd', 'ط' => 't', 'ظ' => 'th', 'ع' => 'aa', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'k',
        'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'ه' => 'h', 'و' => 'w', 'ي' => 'y',
        'ء' => 'aa', 'ة' => 'a', 'ى' => 'a', 'آ' => 'a', 'إ' => 'i', 'أ' => 'a', 'ؤ' => 'u',
        'ئ' => 'y'

    ];

    public static function createCustomSlug(Model $model, string $slug, int $language_id, ?int $pageType = null): string
    {
        $suffix = 0;
        $generatedSlug = self::generateSlug($slug);
        $newSlug = $generatedSlug;

        while ($model::where('slug', $newSlug)
            ->where('language_id', $language_id)
            ->when(!is_null($pageType), function ($query) use ($pageType) {
                return $query->whereHas('page', function ($query) use ($pageType) {
                    $query->where('type', array_flip(PageConstants::PAGE_TYPES)[$pageType]);
                });
            })
            ->exists()) {
            $suffix++;
            $newSlug = "{$generatedSlug}-{$suffix}";
        }

        return $newSlug;
    }

    private static function generateSlug($string): string
    {
        $string = strtr($string, self::REPLACEMENTS); // Replace special characters
        $string = strtolower($string); // Convert to lowercase
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string); // Remove unwanted characters
        $string = preg_replace('/[\s-]+/', '-', $string); // Replace spaces and hyphens with a single hyphen
        return trim($string, '-'); // Trim any leading or trailing hyphens
    }


    public static function setPathBySlugProduct(array $data, string $type): array
    {
        $permalinkTranslationRepository = new PermalinkTranslationRepository(new PermalinkTranslation());
        if (isset($data['slug'])) {
            $permalinkSlug = $permalinkTranslationRepository->getSlugByType(PermalinkConstants::PAGE_TYPES[$type], $data['language_id']);
            $data['path'] = "{$permalinkSlug}/{$data['slug']}";
        }

        return $data;
    }

    public static function setPathBySlugCategory(array $data, string $type): array
    {
        $permalinkTranslationRepository = new PermalinkTranslationRepository(new PermalinkTranslation());

        if (isset($data['slug'])) {
            $permalinkSlug = $permalinkTranslationRepository->getSlugByType(PermalinkConstants::PAGE_TYPES[$type], $data['language_id']);

            $preparedArr = CategoryService::getInstance()->prepareFullPathAndBreadcrumbForCategory(
                $permalinkSlug, $data['parent_id'], $data['name'], $data['slug'],
            );

            $data['breadcrumb'] = $preparedArr['fullBreadcrumb'];
            $data['path'] = $preparedArr['fullPath'];
        }

        return $data;
    }
}
