<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteb021c2a2389b31d590e3f8cccb70ab9
{
    public static $files = array (
        '1fd85688adc86954578e4e80b63953c1' => __DIR__ . '/../..' . '/l10n.php',
    );

    public static $classMap = array (
        'Adobe_Font_Metrics' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Adobe_Font_Metrics.php',
        'Encoding_Map' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Encoding_Map.php',
        'Font' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font.php',
        'Font_Binary_Stream' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Binary_Stream.php',
        'Font_EOT' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_EOT.php',
        'Font_EOT_Header' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_EOT_Header.php',
        'Font_Glyph_Outline' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Glyph_Outline.php',
        'Font_Glyph_Outline_Component' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Glyph_Outline_Component.php',
        'Font_Glyph_Outline_Composite' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Glyph_Outline_Composite.php',
        'Font_Glyph_Outline_Simple' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Glyph_Outline_Simple.php',
        'Font_Header' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Header.php',
        'Font_OpenType' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_OpenType.php',
        'Font_OpenType_Table_Directory_Entry' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_OpenType_Table_Directory_Entry.php',
        'Font_Table' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table.php',
        'Font_Table_Directory_Entry' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_Directory_Entry.php',
        'Font_Table_cmap' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_cmap.php',
        'Font_Table_glyf' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_glyf.php',
        'Font_Table_head' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_head.php',
        'Font_Table_hhea' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_hhea.php',
        'Font_Table_hmtx' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_hmtx.php',
        'Font_Table_kern' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_kern.php',
        'Font_Table_loca' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_loca.php',
        'Font_Table_maxp' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_maxp.php',
        'Font_Table_name' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_name.php',
        'Font_Table_name_Record' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_name_Record.php',
        'Font_Table_os2' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_os2.php',
        'Font_Table_post' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_Table_post.php',
        'Font_TrueType' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_TrueType.php',
        'Font_TrueType_Collection' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_TrueType_Collection.php',
        'Font_TrueType_Header' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_TrueType_Header.php',
        'Font_TrueType_Table_Directory_Entry' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_TrueType_Table_Directory_Entry.php',
        'Font_WOFF' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_WOFF.php',
        'Font_WOFF_Header' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_WOFF_Header.php',
        'Font_WOFF_Table_Directory_Entry' => __DIR__ . '/..' . '/phenx/php-font-lib/classes/Font_WOFF_Table_Directory_Entry.php',
        'UsabilityDynamics\\WPI\\PDF_Invoice_Charge' => __DIR__ . '/../..' . '/lib/classes/class-pdf-template.php',
        'UsabilityDynamics\\WPI\\PDF_Invoice_Item' => __DIR__ . '/../..' . '/lib/classes/class-pdf-template.php',
        'UsabilityDynamics\\WPI\\PDF_Template' => __DIR__ . '/../..' . '/lib/classes/class-pdf-template.php',
        'UsabilityDynamics\\WPI\\WPI_PDF_Bootstrap' => __DIR__ . '/../..' . '/lib/classes/class-WPI_PDF_Bootstrap.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticIniteb021c2a2389b31d590e3f8cccb70ab9::$classMap;

        }, null, ClassLoader::class);
    }
}
