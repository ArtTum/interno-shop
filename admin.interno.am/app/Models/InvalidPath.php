<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvalidPath extends Model
{
    protected $fillable = [
      'path', 'referrar', 'message', 'qty', 'locale'
    ];

    public static function collectInvalidPaths(string $path, string $locale, $message)
    {
        if (str_contains($path, 'wp-')) return;

        $invalidPath = self::select('id', 'qty')->where('path', $path)->where('locale', $locale)->first();

        if ($invalidPath) {
            $invalidPath->update([
                'qty' => $invalidPath->qty + 1,
            ]);
        } else {
            self::create([
               'path' => $path,
               'referrar' => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'chka',
               'message' => $message,
               'locale' => $locale,
               'qty' => 1,
            ]);
        }
    }
}
