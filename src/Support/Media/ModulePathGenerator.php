<?php

namespace CMSCore\Support\Media;

use CMSCore\Models\Author;
use CMSCore\Models\Blog;
use CMSCore\Models\Brand;
use CMSCore\Models\Event;
use CMSCore\Models\Product;
use CMSCore\Models\SiteDetails;
use CMSCore\Models\Term;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Groups media into a per-module folder so the bucket is browsable by feature
 * rather than being a flat wall of numeric ids:
 *
 *   tenants/{identifier}/author/94/Syed-Owais-Ahmed-Rizvi-Avatar.webp
 *   tenants/{identifier}/blog/12/cover.webp
 *
 * The `tenants/{identifier}` part is the disk root (see SwitchTenantDiskTask);
 * everything this class returns is relative to it.
 *
 * The media id stays in the path because it is what makes a path unique —
 * two records may share a file_name.
 *
 * Lives in cms-core because both apps must agree on it: cms-admin writes files
 * at these paths, cms-api generates the public URLs that point at them. When
 * the two used different generators the URLs silently lost their module segment
 * and every image 404'd, so there is deliberately only one copy of this map.
 */
class ModulePathGenerator implements PathGenerator
{
    /**
     * Folder name per owning model. Terms back categories and tags alike, and
     * only categories carry a cover image, so they file under `category`.
     *
     * @var array<class-string, string>
     */
    private const MODULES = [
        Author::class => 'author',
        Blog::class => 'blog',
        Brand::class => 'brand',
        Event::class => 'event',
        Product::class => 'product',
        Term::class => 'category',
        SiteDetails::class => 'site',
    ];

    /**
     * Fallback for a model that isn't mapped above, so a newly added media
     * model gets a sane folder instead of landing at the disk root.
     */
    private const FALLBACK = 'other';

    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /**
     * Resolves the module folder for a media record. Public and static so the
     * migration command can compute destination paths without instantiating
     * a generator per row.
     */
    public static function moduleFor(Media $media): string
    {
        $modelClass = Relation::getMorphedModel($media->model_type) ?? $media->model_type;

        foreach (self::MODULES as $class => $module) {
            if (is_a($modelClass, $class, true)) {
                return $module;
            }
        }

        return Str::of(class_basename($media->model_type))->kebab()->value() ?: self::FALLBACK;
    }

    protected function getBasePath(Media $media): string
    {
        $prefix = config('media-library.prefix', '');
        $path = self::moduleFor($media).'/'.$media->getKey();

        return $prefix !== '' ? $prefix.'/'.$path : $path;
    }
}
