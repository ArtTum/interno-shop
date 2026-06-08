@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($entries as $s)
        <sitemap>
            <loc>{{ $s['loc'] }}</loc>
            <lastmod>{{ $s['lastmod'] }}</lastmod>
        </sitemap>
    @endforeach
</sitemapindex>
